<?php
/**
 * Abstract base driver — DRYs constructor, HTTP transport, and error handling.
 *
 * @package SPLAT\Driver
 */

namespace SPLAT\Driver;

defined( 'ABSPATH' ) || exit;


abstract class AbstractDriver implements DriverInterface {

	use ErrorClassifier;

	public function __construct(
		protected readonly string $apiKey,
		protected readonly string $baseUrl,
		protected readonly string $model,
		protected readonly int $timeout = 30,
		protected readonly array $customHeaders = [],
	) {}

	/**
	 * Send a JSON POST request and return parsed result.
	 *
	 * @param string $url     Full endpoint URL.
	 * @param array  $headers Extra headers (Content-Type is auto-added).
	 * @param array  $body    Request body (will be JSON-encoded).
	 *
	 * @return array{status: int, data: array}|\WP_Error
	 */
	protected function sendRequest( string $url, array $headers, array $body ): array|\WP_Error {
		// Merge order: custom (lowest) → driver auth → Content-Type (highest).
		$mergedHeaders = array_merge(
			$this->customHeaders,
			$headers,
			[ 'Content-Type' => 'application/json' ]
		);

		$response = wp_remote_post(
			$url,
			[
				'timeout' => $this->timeout,
				'headers' => $mergedHeaders,
				'body'    => wp_json_encode( $body ),
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return [
			'status' => wp_remote_retrieve_response_code( $response ),
			'data'   => json_decode( wp_remote_retrieve_body( $response ), true ) ?: [],
		];
	}
}

