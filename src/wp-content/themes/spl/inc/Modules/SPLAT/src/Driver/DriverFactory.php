<?php
/**
 * Factory — resolves driver by api_format with injected timeout.
 *
 * @package SPLAT\Driver
 */

namespace SPLAT\Driver;

defined( 'ABSPATH' ) || exit;

final class DriverFactory {

	/**
	 * Create a driver instance for the given API format.
	 *
	 * @param string $apiFormat     openai_compatible|google_gemini|anthropic_messages
	 * @param string $apiKey        Decrypted API key.
	 * @param string $baseUrl       Provider base URL.
	 * @param string $model         Model identifier.
	 * @param int    $timeout       Request timeout in seconds.
	 * @param array  $customHeaders Per-key custom HTTP headers.
	 *
	 * @return DriverInterface
	 * @throws \InvalidArgumentException If format is unsupported.
	 */
	public static function make( string $apiFormat, string $apiKey, string $baseUrl, string $model, int $timeout = 30, array $customHeaders = [] ): DriverInterface {
		return match ( self::normalizeFormat( $apiFormat ) ) {
			'openai_compatible' => new OpenAIDriver( $apiKey, $baseUrl, $model, $timeout, $customHeaders ),
			'google_gemini'     => new GeminiDriver( $apiKey, $baseUrl, $model, $timeout, $customHeaders ),
			'anthropic_messages' => new AnthropicDriver( $apiKey, $baseUrl, $model, $timeout, $customHeaders ),
			default              => throw new \InvalidArgumentException( sprintf( 'Unsupported API format: %s', esc_html( $apiFormat ) ) ),
		};
	}

	/**
	 * @param string $apiFormat
	 *
	 * @return string
	 */
	private static function normalizeFormat( string $apiFormat ): string {
		return match ( $apiFormat ) {
			'openai', 'openai_compatible'       => 'openai_compatible',
			'google', 'google_gemini'           => 'google_gemini',
			'anthropic', 'anthropic_messages'   => 'anthropic_messages',
			default                             => sanitize_key( $apiFormat ),
		};
	}
}

