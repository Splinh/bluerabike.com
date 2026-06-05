<?php
/**
 * Runtime policy checks for SPLAT consumer tokens.
 *
 * @package SPLAT\Auth
 */

namespace SPLAT\Auth;

defined( 'ABSPATH' ) || exit;

use SPLAT\Gateway\CapabilityResolver;
use SPLAT\Gateway\DTO\GatewayRequest;

final class ConsumerTokenPolicy {

	/**
	 * @param array $consumer
	 *
	 * @return bool|\WP_Error
	 */
	public static function assertInternalOnly( array $consumer ): bool|\WP_Error {
		if ( empty( $consumer['internal_only'] ) ) {
			return true;
		}

		if ( InternalRequestContext::isActive() ) {
			return true;
		}

		return new \WP_Error(
			'consumer_internal_only',
			'This token is restricted to internal use.',
			[
				'status'   => 403,
				'category' => 'auth',
			]
		);
	}

	/**
	 * @param array  $consumer
	 * @param string $route
	 *
	 * @return bool|\WP_Error
	 */
	public static function assertRouteAllowed( array $consumer, string $route ): bool|\WP_Error {
		$allowedRoutes = self::listFromJson( $consumer['allowed_routes_json'] ?? null );
		if ( ! $allowedRoutes ) {
			return true;
		}

		$route = self::normalizeRoute( $route );
		foreach ( $allowedRoutes as $allowedRoute ) {
			if ( $route === self::normalizeRoute( $allowedRoute ) ) {
				return true;
			}
		}

		return new \WP_Error(
			'consumer_route_forbidden',
			'Consumer token is not allowed to access this route.',
			[
				'status'   => 403,
				'category' => 'auth',
			]
		);
	}

	/**
	 * @param array          $consumer
	 * @param GatewayRequest $request
	 *
	 * @return bool|\WP_Error
	 */
	public static function assertRequestAllowed( array $consumer, GatewayRequest $request ): bool|\WP_Error {
		$allowedCapabilities = self::listFromJson( $consumer['allowed_capabilities_json'] ?? null );
		if ( $allowedCapabilities ) {
			foreach ( CapabilityResolver::requiredForRequest( $request ) as $requiredCapability ) {
				if ( ! in_array( $requiredCapability, $allowedCapabilities, true ) ) {
					return new \WP_Error(
						'consumer_capability_forbidden',
						sprintf( 'Consumer token is not allowed to use capability: %s', $requiredCapability ),
						[
							'status'     => 403,
							'category'   => 'auth',
							'capability' => $requiredCapability,
						]
					);
				}
			}
		}

		$allowedModels = self::listFromJson( $consumer['allowed_models_json'] ?? null );
		if ( $allowedModels && $request->model && ! in_array( $request->model, $allowedModels, true ) ) {
			return new \WP_Error(
				'consumer_model_forbidden',
				'Consumer token is not allowed to use the requested model.',
				[
					'status'   => 403,
					'category' => 'auth',
				]
			);
		}

		return true;
	}

	/**
	 * @param array $consumer
	 *
	 * @return array<string, array<int, string>>
	 */
	public static function routingPolicy( array $consumer ): array {
		$policy = [];

		$allowedProviders = self::listFromJson( $consumer['allowed_providers_json'] ?? null );
		if ( $allowedProviders ) {
			$policy['allowed_providers'] = $allowedProviders;
		}

		$allowedModels = self::listFromJson( $consumer['allowed_models_json'] ?? null );
		if ( $allowedModels ) {
			$policy['allowed_models'] = $allowedModels;
		}

		return $policy;
	}

	/**
	 * @param array  $consumer
	 * @param string $provider
	 * @param string $model
	 *
	 * @return bool
	 */
	public static function allowsProviderModel( array $consumer, string $provider, string $model ): bool {
		$allowedProviders = self::listFromJson( $consumer['allowed_providers_json'] ?? null );
		if ( $allowedProviders && ! in_array( $provider, $allowedProviders, true ) ) {
			return false;
		}

		$allowedModels = self::listFromJson( $consumer['allowed_models_json'] ?? null );
		if ( $allowedModels && ! in_array( $model, $allowedModels, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string[]
	 */
	public static function listFromJson( mixed $value ): array {
		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			return [];
		}

		$decoded = json_decode( $value, true );
		if ( ! is_array( $decoded ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map( static fn( mixed $item ): string => sanitize_text_field( (string) $item ), $decoded )
			)
		);
	}

	/**
	 * @param string $route
	 *
	 * @return string
	 */
	private static function normalizeRoute( string $route ): string {
		$route = '/' . ltrim( trim( $route ), '/' );

		return rtrim( $route, '/' );
	}
}

