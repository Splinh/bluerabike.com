<?php
/**
 * OpenAI-compatible models API.
 *
 * @package SPLAT\API
 */

namespace SPLAT\API;

defined( 'ABSPATH' ) || exit;

use SPLAT\Auth\ConsumerTokenManager;
use SPLAT\Auth\ConsumerTokenPolicy;
use SPLAT\Gateway\CapabilityResolver;
use SPLAT\KeyRepository;
use SPLAT\Providers\ProviderRegistry;

final class ModelsAPI {

	private const NAMESPACE = 'splat/v1';

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/models',
			[
				'methods'             => 'GET',
				'callback'            => $this->handle( ... ),
				'permission_callback' => $this->permission( ... ),
			]
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function permission( \WP_REST_Request $request ): bool|\WP_Error {
		$authorization = (string) $request->get_header( 'authorization' );
		if ( '' === $authorization ) {
			return new \WP_Error( 'splat_unauthorized', 'Missing consumer token.', [ 'status' => 401 ] );
		}

		$consumer = ( new ConsumerTokenManager() )->authenticateBearer( $authorization );
		if ( is_wp_error( $consumer ) ) {
			return $consumer;
		}

		if ( ! $consumer ) {
			return new \WP_Error( 'splat_unauthorized', 'Invalid consumer token.', [ 'status' => 401 ] );
		}

		$routeCheck = ConsumerTokenPolicy::assertRouteAllowed( $consumer, '/splat/v1/models' );
		if ( is_wp_error( $routeCheck ) ) {
			return $routeCheck;
		}

		$internalCheck = ConsumerTokenPolicy::assertInternalOnly( $consumer );
		if ( is_wp_error( $internalCheck ) ) {
			return $internalCheck;
		}

		$request->set_param( '_splat_consumer', $consumer );

		return true;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle( \WP_REST_Request $request ): \WP_REST_Response {
		$data     = [];
		$consumer = $request->get_param( '_splat_consumer' );

		foreach ( KeyRepository::getActiveKeys() as $credential ) {
			$model = (string) ( $credential['default_model'] ?? '' );
			if ( '' === $model ) {
				continue;
			}

			$provider = (string) ( $credential['provider'] ?? '' );
			if ( is_array( $consumer ) && ! ConsumerTokenPolicy::allowsProviderModel( $consumer, $provider, $model ) ) {
				continue;
			}

			$definition = ProviderRegistry::get( (string) ( $credential['provider'] ?? '' ) );

			$data[] = [
				'id'           => $model,
				'object'       => 'model',
				'owned_by'     => $provider,
				'provider'     => $provider,
				'credential'   => [
					'id'    => (int) ( $credential['id'] ?? 0 ),
					'label' => (string) ( $credential['label'] ?? '' ),
					'tier'  => (string) ( $credential['tier'] ?? '' ),
					'proxy' => (bool) ( $definition?->proxyRouter ?? false ),
				],
				'capabilities' => CapabilityResolver::resolve( $credential ),
			];
		}

		return new \WP_REST_Response(
			[
				'object' => 'list',
				'data'   => $data,
			]
		);
	}
}

