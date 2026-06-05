<?php
/**
 * Cheap provider credential tester.
 *
 * @package SPLAT\Gateway
 */

namespace SPLAT\Gateway;

defined( 'ABSPATH' ) || exit;

use SPLAT\Driver\DriverFactory;
use SPLAT\Gateway\DTO\GatewayRequest;
use SPLAT\KeyRepository;
use SPLAT\Providers;
use SPLAT\Providers\ProviderRegistry;
use SPLAT\Settings;

final class ProviderTester {

	/**
	 * @param array $credential
	 *
	 * @return array{success: bool, message: string, model?: string, latency_ms?: int}
	 */
	public function test( array $credential ): array {
		$apiKey = KeyRepository::decryptKey( $credential );
		if ( '' === $apiKey ) {
			return $this->finish( $credential, false, 'API key is empty.', 'auth' );
		}

		$startedAt    = microtime( true );
		$capabilities = ProviderRegistry::capabilitiesFor( (string) ( $credential['provider'] ?? '' ) );
		$result       = ! empty( $capabilities['model_list'] )
			? $this->testModelList( $credential, $apiKey )
			: $this->testTinyChat( $credential, $apiKey );

		$latencyMs = (int) round( ( microtime( true ) - $startedAt ) * 1000 );

		return $this->finish(
			$credential,
			$result['success'],
			$result['message'],
			$result['category'] ?? '',
			$latencyMs,
			$result['model'] ?? null
		);
	}

	/**
	 * @param array  $credential
	 * @param string $apiKey
	 *
	 * @return array{success: bool, message: string, category?: string, model?: string}
	 */
	private function testModelList( array $credential, string $apiKey ): array {
		$config = $this->resolveConfig( $credential );
		$url    = rtrim( $config['base_url'], '/' ) . '/models';

		$response = wp_remote_get(
			$url,
			[
				'timeout' => (int) Settings::get( 'request_timeout' ),
				'headers' => [
					'Authorization' => 'Bearer ' . $apiKey,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return [
				'success'  => false,
				'message'  => $response->get_error_message(),
				'category' => 'network',
			];
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status >= 300 ) {
			return [
				'success'  => false,
				'message'  => 'HTTP ' . $status,
				'category' => $status === 401 || $status === 403 ? 'auth' : ( $status === 429 ? 'rate_limit' : 'provider_error' ),
			];
		}

		return [
			'success' => true,
			'message' => 'Connection successful.',
			'model'   => $config['model'],
		];
	}

	/**
	 * @param array  $credential
	 * @param string $apiKey
	 *
	 * @return array{success: bool, message: string, category?: string, model?: string}
	 */
	private function testTinyChat( array $credential, string $apiKey ): array {
		$config  = $this->resolveConfig( $credential );
		$request = GatewayRequest::fromArray(
			[
				'messages'   => [
					[
						'role'    => 'user',
						'content' => 'Reply with exactly: OK',
					],
				],
				'max_tokens' => 5,
			]
		);

		try {
			$driver = DriverFactory::make(
				$config['api_format'],
				$apiKey,
				$config['base_url'],
				$config['model'],
				(int) Settings::get( 'request_timeout' ),
				KeyRepository::decodeCustomHeaders( $credential )
			);
		} catch ( \InvalidArgumentException $e ) {
			return [
				'success'  => false,
				'message'  => $e->getMessage(),
				'category' => 'invalid_request',
			];
		}

		$result = $driver->chat( $request, $credential );
		if ( is_wp_error( $result ) ) {
			$data = $result->get_error_data();

			return [
				'success'  => false,
				'message'  => $result->get_error_message(),
				'category' => (string) ( $data['category'] ?? $result->get_error_code() ),
			];
		}

		return [
			'success' => true,
			'message' => 'Connection successful.',
			'model'   => $result->model,
		];
	}

	/**
	 * @param array       $credential
	 * @param bool        $success
	 * @param string      $message
	 * @param string      $category
	 * @param int         $latencyMs
	 * @param string|null $model
	 *
	 * @return array{success: bool, message: string, model?: string, latency_ms?: int}
	 */
	private function finish( array $credential, bool $success, string $message, string $category = '', int $latencyMs = 0, ?string $model = null ): array {
		$meta = [
			'last_tested_at'      => current_time( 'mysql', true ),
			'last_test_success'   => $success,
			'last_latency_ms'     => $latencyMs,
			'last_error_category' => $success ? '' : sanitize_key( $category ),
		];

		if ( isset( $credential['id'] ) ) {
			KeyRepository::update( (int) $credential['id'], [ 'provider_meta_json' => $meta ] );
		}

		$result = [
			'success'    => $success,
			'message'    => $message,
			'latency_ms' => $latencyMs,
		];

		if ( $model !== null ) {
			$result['model'] = $model;
		}

		return $result;
	}

	/**
	 * @param array $credential
	 *
	 * @return array{api_format: string, base_url: string, model: string}
	 */
	private function resolveConfig( array $credential ): array {
		$apiFormat = (string) ( $credential['api_format'] ?? 'openai_compatible' );
		$baseUrl   = (string) ( $credential['base_url'] ?? '' );
		$model     = (string) ( $credential['default_model'] ?? '' );

		if ( '' === $baseUrl || '' === $model ) {
			$provider = Providers::get( (string) ( $credential['provider'] ?? '' ) );
			if ( $provider ) {
				$baseUrl = $baseUrl ?: (string) ( $provider['base_url'] ?? '' );
				$model   = $model ?: (string) ( $provider['models'][0] ?? '' );
			}
		}

		return [
			'api_format' => $apiFormat,
			'base_url'   => $baseUrl,
			'model'      => $model,
		];
	}
}

