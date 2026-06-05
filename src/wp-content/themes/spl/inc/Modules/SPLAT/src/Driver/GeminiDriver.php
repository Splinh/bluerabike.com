<?php
/**
 * Google Gemini driver.
 *
 * Endpoint: /v1beta/models/{model}:generateContent
 *
 * @package SPLAT\Driver
 */

namespace SPLAT\Driver;

defined( 'ABSPATH' ) || exit;

use SPLAT\Gateway\DTO\GatewayRequest;
use SPLAT\Gateway\DTO\GatewayResponse;
use SPLAT\Gateway\DTO\GatewayUsage;

final class GeminiDriver extends AbstractDriver {

	public function chat( GatewayRequest $request, array $credential = [] ): GatewayResponse|\WP_Error {
		$url = rtrim( $this->baseUrl, '/' ) . '/models/' . rawurlencode( $this->model ) . ':generateContent';

		$body = [
			'contents'         => $this->mapMessages( $request ),
			'generationConfig' => [
				'temperature'     => $request->temperature ?? 0.7,
				'maxOutputTokens' => $request->maxTokens ?? 4096,
			],
		];

		// Extract system instruction.
		$system = $this->extractSystem( $request );
		if ( $system ) {
			$body['systemInstruction'] = [ 'parts' => [ [ 'text' => $system ] ] ];
		}

		$result = $this->sendRequest(
			$url,
			[ 'x-goog-api-key' => $this->apiKey ],
			$body
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$status = $result['status'];
		$data   = $result['data'];

		if ( 200 !== $status ) {
			$msg = $data['error']['message'] ?? "HTTP {$status}";
			return $this->buildError( $status, $msg, 'gemini' );
		}

		$candidate = $data['candidates'][0] ?? [];
		$parts     = $candidate['content']['parts'] ?? [];
		$content   = '';

		foreach ( $parts as $part ) {
			if ( isset( $part['text'] ) ) {
				$content .= $part['text'];
			}
		}

		$usage = $data['usageMetadata'] ?? [];

		return new GatewayResponse(
			content:      $content,
			provider:     (string) ( $credential['provider'] ?? 'gemini' ),
			model:        $this->model,
			usage:        GatewayUsage::fromArray(
				[
					'prompt_tokens'     => $usage['promptTokenCount'] ?? null,
					'completion_tokens' => $usage['candidatesTokenCount'] ?? null,
					'total_tokens'      => $usage['totalTokenCount'] ?? null,
				]
			),
			credentialId: isset( $credential['id'] ) ? (int) $credential['id'] : null,
			finishReason: isset( $candidate['finishReason'] ) ? (string) $candidate['finishReason'] : null,
		);
	}

	private function extractSystem( GatewayRequest $request ): string {
		$system = '';
		foreach ( $request->messages as $message ) {
			if ( 'system' === $message->role && is_string( $message->content ) ) {
				$system .= $message->content . "\n";
			}
		}

		return trim( $system );
	}

	private function mapMessages( GatewayRequest $request ): array {
		$contents = [];

		foreach ( $request->messages as $message ) {
			if ( 'system' === $message->role ) {
				continue;
			}

			$role       = 'assistant' === $message->role ? 'model' : 'user';
			$contents[] = [
				'role'  => $role,
				'parts' => [ [ 'text' => is_string( $message->content ) ? $message->content : '' ] ],
			];
		}

		return $contents;
	}
}

