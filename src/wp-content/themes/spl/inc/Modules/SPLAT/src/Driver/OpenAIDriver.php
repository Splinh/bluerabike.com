<?php
/**
 * OpenAI-compatible chat-completions driver.
 *
 * Handles OpenAI, OpenRouter, 9router, and any custom OpenAI-compatible provider.
 *
 * @package SPLAT\Driver
 */

namespace SPLAT\Driver;

defined( 'ABSPATH' ) || exit;

use SPLAT\Gateway\DTO\GatewayRequest;
use SPLAT\Gateway\DTO\GatewayResponse;
use SPLAT\Gateway\DTO\GatewayUsage;

final class OpenAIDriver extends AbstractDriver {

	public function chat( GatewayRequest $request, array $credential = [] ): GatewayResponse|\WP_Error {
		$url  = rtrim( $this->baseUrl, '/' ) . '/chat/completions';
		$body = [
			'model'      => $this->model,
			'messages'   => $request->messagesToArray(),
			'max_tokens' => $request->maxTokens ?? 4096,
		];

		if ( $request->temperature !== null ) {
			$body['temperature'] = $request->temperature;
		}

		if ( $request->responseFormat !== null ) {
			$body['response_format'] = $request->responseFormat;
		}

		if ( $request->tools ) {
			$body['tools'] = $request->tools;
		}

		if ( $request->toolChoice !== null ) {
			$body['tool_choice'] = $request->toolChoice;
		}

		if ( $request->metadata ) {
			$body['metadata'] = $request->metadata;
		}

		$headers = [ 'Authorization' => 'Bearer ' . $this->apiKey ];

		if ( self::isOpenRouterHost( $this->baseUrl ) ) {
			$headers['HTTP-Referer']       = home_url();
			$headers['X-OpenRouter-Title'] = get_bloginfo( 'name' );
		}

		$result = $this->sendRequest( $url, $headers, $body );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$status = $result['status'];
		$data   = $result['data'];

		if ( 200 !== $status ) {
			$msg = $data['error']['message'] ?? "HTTP {$status}";
			return $this->buildError( $status, $msg, (string) ( $credential['provider'] ?? 'openai' ) );
		}

		$choice  = $data['choices'][0] ?? [];
		$content = $choice['message']['content'] ?? '';

		return new GatewayResponse(
			content:      is_string( $content ) ? $content : '',
			provider:     (string) ( $credential['provider'] ?? 'openai' ),
			model:        (string) ( $data['model'] ?? $this->model ),
			usage:        GatewayUsage::fromArray( $data['usage'] ?? [] ),
			credentialId: isset( $credential['id'] ) ? (int) $credential['id'] : null,
			finishReason: isset( $choice['finish_reason'] ) ? (string) $choice['finish_reason'] : null,
			rawId:        isset( $data['id'] ) ? (string) $data['id'] : null,
			created:      isset( $data['created'] ) ? (int) $data['created'] : null,
			toolCalls:    is_array( $choice['message']['tool_calls'] ?? null ) ? $choice['message']['tool_calls'] : [],
		);
	}

	/**
	 * Check if a URL points to OpenRouter by parsing the host.
	 */
	private static function isOpenRouterHost( string $url ): bool {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host ) {
			return false;
		}

		return 'openrouter.ai' === $host || str_ends_with( $host, '.openrouter.ai' );
	}
}

