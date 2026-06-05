<?php
/**
 * Deterministic request guardrails.
 *
 * @package SPLAT\Gateway
 */

namespace SPLAT\Gateway;

defined( 'ABSPATH' ) || exit;

use SPLAT\Gateway\DTO\GatewayRequest;

final class RequestOptimizer {

	/**
	 * Clamp request limits for a selected credential without mutating prompts.
	 *
	 * @param GatewayRequest $request
	 * @param array          $credential
	 *
	 * @return GatewayRequest
	 */
	public static function optimizeForCredential( GatewayRequest $request, array $credential ): GatewayRequest {
		$maxTokens = $request->maxTokens;
		$limit     = isset( $credential['default_max_tokens'] ) ? (int) $credential['default_max_tokens'] : 0;

		if ( $limit > 0 ) {
			$maxTokens = $maxTokens === null ? $limit : min( $maxTokens, $limit );
		}

		$responseFormat = self::fallbackResponseFormat( $request, CapabilityResolver::resolve( $credential ) );

		if ( $maxTokens === $request->maxTokens && $responseFormat === $request->responseFormat ) {
			return $request;
		}

		return new GatewayRequest(
			messages:       $request->messages,
			model:          $request->model,
			temperature:    $request->temperature,
			maxTokens:      $maxTokens,
			responseFormat: $responseFormat,
			tools:          $request->tools,
			toolChoice:     $request->toolChoice,
			metadata:       $request->metadata,
			consumerId:     $request->consumerId,
			budgetPolicy:   $request->budgetPolicy,
			routingPolicy:  $request->routingPolicy,
		);
	}

	/**
	 * Build a deterministic cache key for a cacheable request.
	 *
	 * @param GatewayRequest $request
	 * @param string         $provider Effective provider after credential resolution.
	 * @param string         $model    Effective model after credential resolution.
	 *
	 * @return string|null SHA-256 hex string (64 chars) or null if not cacheable.
	 */
	public static function cacheKey( GatewayRequest $request, string $provider = '', string $model = '' ): ?string {
		if ( empty( $request->metadata['cacheable'] ) ) {
			return null;
		}

		return hash(
			'sha256',
			wp_json_encode(
				[
					'provider'        => $provider,
					'model'           => $model ?: $request->model,
					'messages'        => $request->messagesToArray(),
					'response_format' => $request->responseFormat,
					'tools'           => $request->tools,
					'tool_choice'     => $request->toolChoice,
				]
			) ?: ''
		);
	}

	/**
	 * Estimate prompt tokens using a byte-based heuristic (÷4).
	 *
	 * Intentionally conservative: ~20% overestimate for English, ~40% for CJK.
	 *
	 * @param GatewayRequest $request
	 *
	 * @return int Estimated token count.
	 */
	public static function estimatePromptTokens( GatewayRequest $request ): int {
		$json = wp_json_encode( $request->messagesToArray() ) ?: '';

		return (int) ceil( mb_strlen( $json, 'UTF-8' ) / 4 );
	}

	/**
	 * @param GatewayRequest $request
	 * @param array          $capabilities
	 *
	 * @return array|null
	 */
	private static function fallbackResponseFormat( GatewayRequest $request, array $capabilities ): ?array {
		$type = is_array( $request->responseFormat )
			? (string) ( $request->responseFormat['type'] ?? '' )
			: '';

		if ( '' === $type || empty( $request->routingPolicy['allow_structured_output_fallback'] ) ) {
			return $request->responseFormat;
		}

		if ( 'json_schema' === $type && empty( $capabilities['json_schema'] ) && ! empty( $capabilities['json_object'] ) ) {
			return [ 'type' => 'json_object' ];
		}

		if ( in_array( $type, [ 'json_schema', 'json_object' ], true ) && empty( $capabilities[ $type ] ) ) {
			return null;
		}

		return $request->responseFormat;
	}
}

