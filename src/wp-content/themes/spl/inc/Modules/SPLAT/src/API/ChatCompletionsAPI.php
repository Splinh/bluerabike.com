<?php
/**
 * OpenAI-compatible chat-completions API.
 *
 * @package SPLAT\API
 */

namespace SPLAT\API;

defined( 'ABSPATH' ) || exit;

use SPLAT\Auth\ConsumerTokenManager;
use SPLAT\Auth\ConsumerTokenPolicy;
use SPLAT\Gateway\AiGateway;
use SPLAT\Gateway\DTO\GatewayRequest;

final class ChatCompletionsAPI {

	private const NAMESPACE = 'splat/v1';

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/chat/completions',
			[
				'methods'             => 'POST',
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
		$consumer = $this->authenticate( $request );

		if ( is_wp_error( $consumer ) ) {
			return $consumer;
		}

		if ( ! $consumer ) {
			return new \WP_Error( 'splat_unauthorized', 'Invalid consumer token.', [ 'status' => 401 ] );
		}

		$routeCheck = ConsumerTokenPolicy::assertRouteAllowed( $consumer, '/splat/v1/chat/completions' );
		if ( is_wp_error( $routeCheck ) ) {
			return $routeCheck;
		}

		$internalCheck = ConsumerTokenPolicy::assertInternalOnly( $consumer );
		if ( is_wp_error( $internalCheck ) ) {
			return $internalCheck;
		}

		$request->set_param( '_splat_consumer_id', (int) $consumer['id'] );
		$request->set_param( '_splat_consumer', $consumer );

		return true;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			return $this->errorResponse(
				new \WP_Error(
					'invalid_request',
					'Request body must be a JSON object.',
					[
						'status'   => 400,
						'category' => 'invalid_request',
					]
				)
			);
		}

		if ( ! empty( $params['stream'] ) ) {
			return $this->errorResponse(
				new \WP_Error(
					'unsupported_capability',
					'Streaming is not supported in this phase.',
					[
						'status'   => 400,
						'category' => 'unsupported_capability',
					]
				)
			);
		}

		$consumer                 = $request->get_param( '_splat_consumer' );
		$params['consumer_id']    = (int) $request->get_param( '_splat_consumer_id' );
		$params['routing_policy'] = $this->mergeConsumerRoutingPolicy(
			is_array( $params['routing_policy'] ?? null ) ? $params['routing_policy'] : [],
			is_array( $consumer ) ? ConsumerTokenPolicy::routingPolicy( $consumer ) : []
		);

		$gatewayRequest = GatewayRequest::fromArray( $params );
		if ( is_array( $consumer ) ) {
			$policyCheck = ConsumerTokenPolicy::assertRequestAllowed( $consumer, $gatewayRequest );
			if ( is_wp_error( $policyCheck ) ) {
				return $this->errorResponse( $policyCheck );
			}
		}

		$result = ( new AiGateway() )->chat( $gatewayRequest );
		if ( is_wp_error( $result ) ) {
			return $this->errorResponse( $result );
		}

		return new \WP_REST_Response( $result->toOpenAiArray() );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return array|null|\WP_Error
	 */
	private function authenticate( \WP_REST_Request $request ): array|null|\WP_Error {
		$authorization = (string) $request->get_header( 'authorization' );
		if ( '' === $authorization ) {
			return null;
		}

		return ( new ConsumerTokenManager() )->authenticateBearer( $authorization );
	}

	/**
	 * @param \WP_Error $error
	 *
	 * @return \WP_REST_Response
	 */
	private function errorResponse( \WP_Error $error ): \WP_REST_Response {
		$data   = $error->get_error_data();
		$status = isset( $data['status'] ) ? (int) $data['status'] : 500;

		return new \WP_REST_Response(
			[
				'error' => [
					'message' => $error->get_error_message(),
					'type'    => (string) ( $data['category'] ?? 'splat_error' ),
					'code'    => $error->get_error_code(),
				],
			],
			$status
		);
	}

	/**
	 * @param array $requestPolicy
	 * @param array $consumerPolicy
	 *
	 * @return array
	 */
	private function mergeConsumerRoutingPolicy( array $requestPolicy, array $consumerPolicy ): array {
		foreach ( [ 'allowed_providers', 'allowed_models' ] as $key ) {
			if ( empty( $consumerPolicy[ $key ] ) ) {
				continue;
			}

			if ( empty( $requestPolicy[ $key ] ) || ! is_array( $requestPolicy[ $key ] ) ) {
				$requestPolicy[ $key ] = $consumerPolicy[ $key ];
				continue;
			}

			$requestPolicy[ $key ] = array_values( array_intersect( $requestPolicy[ $key ], $consumerPolicy[ $key ] ) );
		}

		return $requestPolicy;
	}
}

