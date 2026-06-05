<?php
/**
 * OpenAPI spec endpoint for SPLAT gateway routes.
 *
 * @package SPLAT\API
 */

namespace SPLAT\API;

defined( 'ABSPATH' ) || exit;

use SPLAT\Auth\ConsumerTokenManager;
use SPLAT\Auth\ConsumerTokenPolicy;

final class OpenApiSpecAPI {

	private const NAMESPACE = 'splat/v1';

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/openapi.json',
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

		$routeCheck = ConsumerTokenPolicy::assertRouteAllowed( $consumer, '/splat/v1/openapi.json' );
		if ( is_wp_error( $routeCheck ) ) {
			return $routeCheck;
		}

		$internalCheck = ConsumerTokenPolicy::assertInternalOnly( $consumer );
		if ( is_wp_error( $internalCheck ) ) {
			return $internalCheck;
		}

		return true;
	}

	/**
	 * @return \WP_REST_Response
	 */
	public function handle(): \WP_REST_Response {
		return new \WP_REST_Response(
			[
				'openapi'    => '3.1.0',
				'info'       => [
					'title'   => 'SPLAT AI Gateway',
					'version' => defined( 'SPLAT_VERSION' ) ? SPLAT_VERSION : '0.0.0',
				],
				'components' => [
					'securitySchemes' => [
						'ConsumerBearer' => [
							'type'         => 'http',
							'scheme'       => 'bearer',
							'bearerFormat' => 'SPLAT consumer token',
						],
					],
				],
				'security'   => [
					[ 'ConsumerBearer' => [] ],
				],
				'paths'      => [
					'/splat/v1/chat/completions' => [
						'post' => [
							'summary'     => 'Create a chat completion through the SPLAT gateway.',
							'operationId' => 'createChatCompletion',
							'requestBody' => [
								'required' => true,
								'content'  => [
									'application/json' => [
										'schema' => [
											'type'       => 'object',
											'required'   => [ 'messages' ],
											'properties' => [
												'model'    => [ 'type' => 'string' ],
												'messages' => [
													'type' => 'array',
													'items' => [ 'type' => 'object' ],
												],
												'temperature' => [ 'type' => 'number' ],
												'max_tokens' => [ 'type' => 'integer' ],
												'response_format' => [ 'type' => 'object' ],
												'tools'    => [ 'type' => 'array' ],
												'tool_choice' => [],
												'metadata' => [ 'type' => 'object' ],
											],
										],
									],
								],
							],
							'responses'   => [
								'200' => [ 'description' => 'OpenAI-compatible chat completion response.' ],
								'400' => [ 'description' => 'Invalid request or unsupported capability.' ],
								'401' => [ 'description' => 'Invalid or missing consumer token.' ],
							],
						],
					],
					'/splat/v1/models'           => [
						'get' => [
							'summary'     => 'List active configured SPLAT models.',
							'operationId' => 'listModels',
							'responses'   => [
								'200' => [ 'description' => 'OpenAI-compatible model list.' ],
								'401' => [ 'description' => 'Invalid or missing consumer token.' ],
							],
						],
					],
				],
			]
		);
	}
}

