<?php
/**
 * AI gateway facade.
 *
 * @package SPLAT\Gateway
 */

namespace SPLAT\Gateway;

defined( 'ABSPATH' ) || exit;

use SPLAT\Auth\ConsumerTokenRepository;
use SPLAT\Driver\DriverFactory;
use SPLAT\Gateway\DTO\GatewayRequest;
use SPLAT\Gateway\DTO\GatewayResponse;
use SPLAT\KeyPoolManager;
use SPLAT\KeyRepository;
use SPLAT\Providers;
use SPLAT\Settings;

final class AiGateway {

	/**
	 * Send a chat request through the managed key pool.
	 *
	 * @param GatewayRequest $request
	 * @param string|null    $provider
	 * @param string         $complexity
	 *
	 * @return GatewayResponse|\WP_Error
	 */
	public function chat( GatewayRequest $request, ?string $provider = null, string $complexity = 'low' ): GatewayResponse|\WP_Error {
		if ( ! $request->messages ) {
			return new \WP_Error( 'missing_messages', 'At least one message is required.' );
		}

		if ( $request->consumerId ) {
			$consumerToken = ConsumerTokenRepository::getById( $request->consumerId );
			if ( $consumerToken && UsageLedger::consumerBudgetExceeded( $consumerToken ) ) {
				return new \WP_Error(
					'quota_exceeded',
					'Consumer token budget exceeded.',
					[
						'status'   => 429,
						'category' => 'quota',
					]
				);
			}
		}

		$preferred      = $provider ?? KeyPoolManager::getPreferredProvider();
		$tierPreference = KeyPoolManager::resolveTierPreference( $complexity );
		$timeout        = (int) Settings::get( 'request_timeout' );
		$maxRetries     = (int) Settings::get( 'max_retries' );
		$cacheTtl       = (int) Settings::get( 'cache_ttl' );
		$retryPolicy    = is_array( $request->routingPolicy['retry_policy'] ?? null ) ? $request->routingPolicy['retry_policy'] : [];

		if ( isset( $retryPolicy['max_attempts'] ) ) {
			$maxRetries = max( 1, min( $maxRetries > 0 ? $maxRetries : 50, absint( $retryPolicy['max_attempts'] ) ) );
		}

		$pool      = ( new KeySelector() )->candidates( $request, $preferred, $tierPreference );
		$poolCount = count( $pool );
		$poolIter  = 0;
		$triedIds  = [];
		$lastError = null;
		$attempt   = 0;

		while ( true ) {
			if ( $maxRetries > 0 && $attempt >= $maxRetries ) {
				break;
			}

			$keyRow = null;
			while ( $poolIter < $poolCount ) {
				$candidate = $pool[ $poolIter ];
				++$poolIter;

				if ( ! in_array( (int) $candidate['id'], $triedIds, true ) ) {
					$keyRow = $candidate;
					break;
				}
			}

			if ( null === $keyRow ) {
				break;
			}

			++$attempt;
			$keyId      = (int) $keyRow['id'];
			$triedIds[] = $keyId;

			$optimizedRequest = RequestOptimizer::optimizeForCredential( $request, $keyRow );
			$config           = $this->resolveCredentialConfig( $keyRow, $request );

			if ( '' === $config['model'] ) {
				continue;
			}

			// ── Prompt guardrail ────────────────────────
			$maxPromptTokens = (int) ( $keyRow['max_prompt_tokens'] ?? 0 );
			if ( $maxPromptTokens > 0 ) {
				$estimated = RequestOptimizer::estimatePromptTokens( $optimizedRequest );
				if ( $estimated > $maxPromptTokens ) {
					$lastError = new \WP_Error(
						'payload_too_large',
						sprintf( 'Prompt exceeds max_prompt_tokens limit (%d estimated > %d allowed).', $estimated, $maxPromptTokens ),
						[
							'status'   => 413,
							'category' => 'invalid_request',
						]
					);
					continue;
				}
			}

			// ── Cache read ──────────────────────────────
			$cacheKey = RequestOptimizer::cacheKey( $optimizedRequest, (string) ( $keyRow['provider'] ?? '' ), $config['model'] );
			if ( $cacheKey ) {
				$cached = ResponseCache::get( $cacheKey );
				if ( $cached ) {
					KeyPoolManager::handleSuccess( $keyId );
					UsageLedger::recordCacheHit( $cached, $request->consumerId );

					return $cached;
				}
			}

			if ( UsageLedger::providerBudgetExceeded( $keyRow ) ) {
				$lastError = new \WP_Error(
					'quota_exceeded',
					'Provider credential budget exceeded.',
					[
						'status'   => 429,
						'category' => 'quota',
					]
				);
				continue;
			}

			$apiKey = KeyRepository::decryptKey( $keyRow );
			if ( '' === $apiKey ) {
				continue;
			}

			try {
				$driver = DriverFactory::make(
					$config['api_format'],
					$apiKey,
					$config['base_url'],
					$config['model'],
					$timeout,
					KeyRepository::decodeCustomHeaders( $keyRow )
				);
			} catch ( \InvalidArgumentException $e ) {
				$lastError = new \WP_Error( 'driver_error', $e->getMessage() );
				continue;
			}

			// ── Per-credential retry with backoff ───────
			$maxTransientRetries = 3;
			$backoffSeconds      = [ 1, 2, 4 ];

			for ( $transient = 0; $transient <= $maxTransientRetries; ++$transient ) {
				$startedAt = microtime( true );
				$result    = $driver->chat( $optimizedRequest, $keyRow );
				$duration  = (int) round( ( microtime( true ) - $startedAt ) * 1000 );

				if ( ! is_wp_error( $result ) ) {
					KeyPoolManager::handleSuccess( $keyId );
					UsageLedger::recordSuccess( $result, $request->consumerId, $duration );

					// ── Cache write ─────────────────────
					if ( $cacheKey && $cacheTtl > 0 ) {
						ResponseCache::put( $cacheKey, $result, $cacheTtl );
					}

					return $result;
				}

				$httpStatus    = (int) ( $result->get_error_data()['status'] ?? 0 );
				$errorCategory = (string) ( $result->get_error_data()['category'] ?? '' );
				$errorMsg      = $result->get_error_message();

				KeyPoolManager::handleFailure( $keyId, $httpStatus, $errorMsg );
				UsageLedger::recordError( $keyRow, $result, $request->consumerId, $duration );
				$lastError = $result;

				// Only retry transient errors on the same credential.
				$retryable = in_array( $errorCategory, [ 'rate_limit', 'provider_error', 'timeout' ], true )
					|| ( $httpStatus >= 429 && $httpStatus !== 401 && $httpStatus !== 403 );

				if ( ! $retryable || $transient >= $maxTransientRetries - 1 ) {
					break;
				}

				// phpcs:ignore WordPress.WP.AlternativeFunctions.sleep_sleep
				sleep( $backoffSeconds[ $transient ] ?? 4 );
			}

			if ( array_key_exists( 'allow_fallback', $request->routingPolicy ) && ! (bool) $request->routingPolicy['allow_fallback'] ) {
				break;
			}

			$httpStatus = (int) ( $lastError->get_error_data()['status'] ?? 0 );
			if ( $httpStatus > 0 && $httpStatus < 429 && $httpStatus !== 408 ) {
				break;
			}
		}

		return $lastError ?? new \WP_Error( 'pool_exhausted', 'All API keys exhausted. No available keys in pool.' );
	}

	/**
	 * Test one provider credential with a tiny chat request.
	 *
	 * @param array $keyRow
	 *
	 * @return array{success: bool, message: string, model?: string}
	 */
	public function testCredential( array $keyRow ): array {
		return ( new ProviderTester() )->test( $keyRow );
	}

	/**
	 * @param array          $keyRow
	 * @param GatewayRequest $request
	 *
	 * @return array{api_format: string, base_url: string, model: string}
	 */
	private function resolveCredentialConfig( array $keyRow, GatewayRequest $request ): array {
		$apiFormat = (string) ( $keyRow['api_format'] ?? 'openai' );
		$baseUrl   = (string) ( $keyRow['base_url'] ?? '' );
		$model     = $request->model ?? (string) ( $keyRow['default_model'] ?? '' );

		if ( '' === $baseUrl || '' === $model ) {
			$providerConfig = Providers::get( (string) ( $keyRow['provider'] ?? '' ) );
			if ( $providerConfig ) {
				if ( '' === $baseUrl ) {
					$baseUrl = (string) ( $providerConfig['base_url'] ?? '' );
				}
				if ( '' === $model ) {
					$model = (string) ( $providerConfig['models'][0] ?? '' );
				}
				if ( '' === $apiFormat ) {
					$apiFormat = (string) ( $providerConfig['api_format'] ?? 'openai' );
				}
			}
		}

		return [
			'api_format' => $apiFormat,
			'base_url'   => $baseUrl,
			'model'      => $model,
		];
	}
}

