<?php
/**
 * Anthropic (Claude) driver.
 *
 * Endpoint: /v1/messages
 *
 * @package SPLAT\Driver
 */

namespace SPLAT\Driver;

defined( 'ABSPATH' ) || exit;

use SPLAT\Gateway\DTO\GatewayRequest;
use SPLAT\Gateway\DTO\GatewayResponse;
use SPLAT\Gateway\DTO\GatewayUsage;

final class AnthropicDriver extends AbstractDriver {

	public function chat( GatewayRequest $request, array $credential = [] ): GatewayResponse|\WP_Error {
		$url = rtrim( $this->baseUrl, '/' ) . '/v1/messages';

		// Separate system from conversation.
		$system               = '';
		$conversationMessages = [];

		foreach ( $request->messages as $message ) {
			if ( 'system' === $message->role && is_string( $message->content ) ) {
				$system .= $message->content . "\n";
			} else {
				$conversationMessages[] = [
					'role'    => $message->role,
					'content' => is_string( $message->content ) ? $message->content : '',
				];
			}
		}

		$body = [
			'model'      => $this->model,
			'max_tokens' => $request->maxTokens ?? 4096,
			'messages'   => $conversationMessages,
		];

		if ( '' !== trim( $system ) ) {
			$body['system'] = trim( $system );
		}

		if ( $request->temperature !== null ) {
			$body['temperature'] = $request->temperature;
		}

		$result = $this->sendRequest(
			$url,
			[
				'x-api-key'         => $this->apiKey,
				'anthropic-version' => '2023-06-01',
			],
			$body
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$status = $result['status'];
		$data   = $result['data'];

		if ( 200 !== $status ) {
			$msg = $data['error']['message'] ?? "HTTP {$status}";
			return $this->buildError( $status, $msg, 'anthropic' );
		}

		$content = '';
		foreach ( $data['content'] ?? [] as $block ) {
			if ( 'text' === ( $block['type'] ?? '' ) ) {
				$content .= $block['text'];
			}
		}

		$usage = $data['usage'] ?? [];

		return new GatewayResponse(
			content:      $content,
			provider:     (string) ( $credential['provider'] ?? 'anthropic' ),
			model:        (string) ( $data['model'] ?? $this->model ),
			usage:        GatewayUsage::fromArray(
				[
					'prompt_tokens'     => $usage['input_tokens'] ?? null,
					'completion_tokens' => $usage['output_tokens'] ?? null,
				]
			),
			credentialId: isset( $credential['id'] ) ? (int) $credential['id'] : null,
			finishReason: isset( $data['stop_reason'] ) ? (string) $data['stop_reason'] : null,
			rawId:        isset( $data['id'] ) ? (string) $data['id'] : null,
		);
	}
}

