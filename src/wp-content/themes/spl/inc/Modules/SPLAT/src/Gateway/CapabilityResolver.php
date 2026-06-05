<?php
/**
 * Resolve provider and credential capability support.
 *
 * @package SPLAT\Gateway
 */

namespace SPLAT\Gateway;

defined( 'ABSPATH' ) || exit;

use SPLAT\Gateway\DTO\GatewayRequest;
use SPLAT\Providers\ProviderRegistry;

final class CapabilityResolver {

	/**
	 * @param array $credential
	 *
	 * @return array<string, bool>
	 */
	public static function resolve( array $credential ): array {
		$capabilities = ProviderRegistry::capabilitiesFor( (string) ( $credential['provider'] ?? '' ) );
		$overrides    = self::credentialOverrides( $credential );

		foreach ( $overrides as $capability => $enabled ) {
			$capabilities[ $capability ] = (bool) $enabled;
		}

		return $capabilities;
	}

	/**
	 * @param GatewayRequest $request
	 * @param array          $credential
	 *
	 * @return bool|\WP_Error
	 */
	public static function validate( GatewayRequest $request, array $credential ): bool|\WP_Error {
		$capabilities = self::resolve( $credential );

		foreach ( self::requiredForRequest( $request ) as $required ) {
			if ( empty( $capabilities[ $required ] ) ) {
				return new \WP_Error(
					'unsupported_capability',
					sprintf( 'Credential does not support required capability: %s', $required ),
					[ 'capability' => $required ]
				);
			}
		}

		return true;
	}

	/**
	 * @param GatewayRequest $request
	 *
	 * @return string[]
	 */
	public static function requiredForRequest( GatewayRequest $request ): array {
		$required = [ 'chat_completions' ];

		$type = is_array( $request->responseFormat )
			? (string) ( $request->responseFormat['type'] ?? '' )
			: '';

		if ( 'json_schema' === $type ) {
			$required[] = 'json_schema';
		}

		if ( 'json_object' === $type ) {
			$required[] = 'json_object';
		}

		if ( $request->tools ) {
			$required[] = 'tools';
		}

		return array_values( array_unique( $required ) );
	}

	/**
	 * @param array $credential
	 *
	 * @return array<string, bool>
	 */
	private static function credentialOverrides( array $credential ): array {
		$raw = $credential['capabilities_json'] ?? '';
		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			return [];
		}

		$decoded = json_decode( $raw, true );
		if ( ! is_array( $decoded ) ) {
			return [];
		}

		$capabilities = [];
		foreach ( $decoded as $capability => $enabled ) {
			$capability = sanitize_key( (string) $capability );
			if ( '' !== $capability ) {
				$capabilities[ $capability ] = (bool) $enabled;
			}
		}

		return $capabilities;
	}
}

