<?php
/**
 * REST Controller for AI Key Pool management.
 *
 * @package SPLAT\Admin
 */

namespace SPLAT\Admin;

defined( 'ABSPATH' ) || exit;

use SPLAT\Gateway\AiGateway;
use SPLAT\Auth\ConsumerTokenRepository;
use SPLAT\DB;
use SPLAT\Gateway\CredentialLifecycle;
use SPLAT\Gateway\DTO\GatewayRequest;
use SPLAT\Gateway\UsageLedger;
use SPLAT\KeyRepository;
use SPLAT\Providers;
use SPLAT\Settings;
use SPLAT\Updater\GitHubUpdater;

final class RestController {

	private const NAMESPACE = 'splat/v1';

	public function register_routes(): void {

		// List all keys.
		register_rest_route(
			self::NAMESPACE,
			'/ai-keys',
			[
				'methods'             => 'GET',
				'callback'            => $this->list_keys( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Create key.
		register_rest_route(
			self::NAMESPACE,
			'/ai-keys',
			[
				'methods'             => 'POST',
				'callback'            => $this->create_key( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Update key.
		register_rest_route(
			self::NAMESPACE,
			'/ai-keys/(?P<id>\d+)',
			[
				'methods'             => 'PUT',
				'callback'            => $this->update_key( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Delete key.
		register_rest_route(
			self::NAMESPACE,
			'/ai-keys/(?P<id>\d+)',
			[
				'methods'             => 'DELETE',
				'callback'            => $this->delete_key( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Test key.
		register_rest_route(
			self::NAMESPACE,
			'/ai-keys/(?P<id>\d+)/test',
			[
				'methods'             => 'POST',
				'callback'            => $this->test_key( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Reset cooldowns.
		register_rest_route(
			self::NAMESPACE,
			'/ai-keys/reset-cooldowns',
			[
				'methods'             => 'POST',
				'callback'            => $this->reset_cooldowns( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Save settings.
		register_rest_route(
			self::NAMESPACE,
			'/settings',
			[
				'methods'             => 'POST',
				'callback'            => $this->save_settings( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Get settings.
		register_rest_route(
			self::NAMESPACE,
			'/settings',
			[
				'methods'             => 'GET',
				'callback'            => $this->get_settings( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Providers list.
		register_rest_route(
			self::NAMESPACE,
			'/providers',
			[
				'methods'             => 'GET',
				'callback'            => $this->get_providers( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Test chat (for debugging).
		register_rest_route(
			self::NAMESPACE,
			'/test-chat',
			[
				'methods'             => 'POST',
				'callback'            => $this->test_chat( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Get update token status.
		register_rest_route(
			self::NAMESPACE,
			'/token',
			[
				'methods'             => 'GET',
				'callback'            => $this->get_token( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		// Save update token.
		register_rest_route(
			self::NAMESPACE,
			'/token',
			[
				'methods'             => 'POST',
				'callback'            => $this->save_token( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/consumer-tokens',
			[
				[
					'methods'             => 'GET',
					'callback'            => $this->list_consumer_tokens( ... ),
					'permission_callback' => $this->check_admin( ... ),
				],
				[
					'methods'             => 'POST',
					'callback'            => $this->create_consumer_token( ... ),
					'permission_callback' => $this->check_admin( ... ),
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/consumer-tokens/(?P<id>\d+)',
			[
				'methods'             => 'DELETE',
				'callback'            => $this->revoke_consumer_token( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/usage',
			[
				'methods'             => 'GET',
				'callback'            => $this->usage_summary( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/ai-keys/(?P<id>\d+)/health',
			[
				'methods'             => 'GET',
				'callback'            => $this->credential_health( ... ),
				'permission_callback' => $this->check_admin( ... ),
			]
		);
	}

	// ── Permission ──────────────────────────────────

	public function check_admin(): bool {
		return current_user_can( 'manage_options' );
	}

	// ── Key CRUD ────────────────────────────────────

	public function list_keys(): \WP_REST_Response {
		return new \WP_REST_Response( KeyRepository::getAll() );
	}

	public function create_key( \WP_REST_Request $request ): \WP_REST_Response {
		$data   = $request->get_json_params();
		$result = KeyRepository::create( $data );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [ 'error' => $result->get_error_message() ], 400 );
		}

		return new \WP_REST_Response(
			[
				'id'      => $result,
				'message' => 'Key created.',
			],
			201
		);
	}

	public function update_key( \WP_REST_Request $request ): \WP_REST_Response {
		$id     = (int) $request->get_param( 'id' );
		$data   = $request->get_json_params();
		$result = KeyRepository::update( $id, $data );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [ 'error' => $result->get_error_message() ], 400 );
		}

		return new \WP_REST_Response(
			[
				'updated' => $result,
				'message' => 'Key updated.',
			]
		);
	}

	public function delete_key( \WP_REST_Request $request ): \WP_REST_Response {
		$id     = (int) $request->get_param( 'id' );
		$result = KeyRepository::delete( $id );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [ 'error' => $result->get_error_message() ], 400 );
		}

		return new \WP_REST_Response(
			[
				'deleted' => $result,
				'message' => 'Key deleted.',
			]
		);
	}

	// ── Actions ─────────────────────────────────────

	public function test_key( \WP_REST_Request $request ): \WP_REST_Response {
		$id  = (int) $request->get_param( 'id' );
		$row = KeyRepository::getById( $id );

		if ( ! $row ) {
			return new \WP_REST_Response( [ 'error' => 'Key not found.' ], 404 );
		}

		$gateway = new AiGateway();
		$result  = $gateway->testCredential( $row );

		return new \WP_REST_Response( $result );
	}

	public function reset_cooldowns(): \WP_REST_Response {
		KeyRepository::resetAllCooldowns();

		return new \WP_REST_Response( [ 'message' => 'All cooldowns reset.' ] );
	}

	public function test_chat( \WP_REST_Request $request ): \WP_REST_Response {
		$message = sanitize_text_field( $request->get_param( 'message' ) ?? 'Say hello in 10 words.' );

		$gateway = new AiGateway();
		$result  = $gateway->chat(
			GatewayRequest::fromArray(
				[
					'messages' => [
						[
							'role'    => 'user',
							'content' => $message,
						],
					],
				]
			)
		);

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [ 'error' => $result->get_error_message() ], 500 );
		}

		return new \WP_REST_Response(
			[
				'content'  => $result->content,
				'provider' => $result->provider,
				'model'    => $result->model,
			]
		);
	}

	// ── Settings ────────────────────────────────────

	public function get_settings(): \WP_REST_Response {
		return new \WP_REST_Response( Settings::get() );
	}

	public function save_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$data = $request->get_json_params();

		$paidStrategy    = sanitize_text_field( $data['paid_key_strategy'] ?? 'high_complexity_first' );
		$validStrategies = [ 'always', 'high_complexity_first', 'fallback_only' ];

		$sanitized = [
			'preferred_provider' => sanitize_text_field( $data['preferred_provider'] ?? '' ),
			'max_retries'        => min( 50, absint( $data['max_retries'] ?? 0 ) ),
			'cooldown_429'       => max( 10, absint( $data['cooldown_429'] ?? 60 ) ),
			'cooldown_5xx'       => max( 30, absint( $data['cooldown_5xx'] ?? 300 ) ),
			'request_timeout'    => max( 5, min( 120, absint( $data['request_timeout'] ?? 30 ) ) ),
			'prefer_free_keys'   => ! empty( $data['prefer_free_keys'] ),
			'paid_key_strategy'  => in_array( $paidStrategy, $validStrategies, true ) ? $paidStrategy : 'high_complexity_first',
			'cache_ttl'          => max( 0, min( 604800, absint( $data['cache_ttl'] ?? 86400 ) ) ),
			'clean_uninstall'    => ! empty( $data['clean_uninstall'] ),
		];

		update_option( 'splat_settings', $sanitized );

		return new \WP_REST_Response(
			[
				'message'  => 'Settings saved.',
				'settings' => $sanitized,
			]
		);
	}

	public function get_providers(): \WP_REST_Response {
		return new \WP_REST_Response( Providers::all() );
	}

	// ── Token ────────────────────────────────────────

	/**
	 * Return whether a token is currently configured (never expose the value).
	 */
	public function get_token(): \WP_REST_Response {
		return new \WP_REST_Response(
			[ 'has_token' => GitHubUpdater::hasToken() ]
		);
	}

	/**
	 * Save (encrypt) or clear the update access token.
	 *
	 * Send {"token": ""} to clear, {"token": "xxx"} to set.
	 */
	public function save_token( \WP_REST_Request $request ): \WP_REST_Response {
		$raw = trim( (string) ( $request->get_json_params()['token'] ?? '' ) );

		if ( '' === $raw ) {
			delete_option( GitHubUpdater::TOKEN_OPTION );

			return new \WP_REST_Response(
				[
					'has_token' => false,
					'message'   => 'Token cleared.',
				]
			);
		}

		// Ignore placeholder sent by the UI (token unchanged).
		if ( str_starts_with( $raw, '***' ) ) {
			return new \WP_REST_Response(
				[
					'has_token' => true,
					'message'   => 'Token unchanged.',
				]
			);
		}

		$encrypted = GitHubUpdater::encryptToken( $raw );
		if ( '' === $encrypted ) {
			return new \WP_REST_Response( [ 'error' => 'Encryption failed.' ], 500 );
		}

		update_option( GitHubUpdater::TOKEN_OPTION, $encrypted, false );

		return new \WP_REST_Response(
			[
				'has_token' => true,
				'message'   => 'Token saved.',
			]
		);
	}

	// Consumer Tokens ---------------------------------------------------------

	public function list_consumer_tokens(): \WP_REST_Response {
		$tokens = ConsumerTokenRepository::getAllMasked();
		foreach ( $tokens as &$token ) {
			$token['usage'] = UsageLedger::consumerTokenUsage( (int) ( $token['id'] ?? 0 ) );
		}

		return new \WP_REST_Response( $tokens );
	}

	public function create_consumer_token( \WP_REST_Request $request ): \WP_REST_Response {
		$result = ConsumerTokenRepository::create( $request->get_json_params() ?: [] );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [ 'error' => $result->get_error_message() ], 400 );
		}

		return new \WP_REST_Response( $result, 201 );
	}

	public function revoke_consumer_token( \WP_REST_Request $request ): \WP_REST_Response {
		$id    = (int) $request->get_param( 'id' );
		$force = ! empty( $request->get_param( 'force' ) );

		if ( $force ) {
			$result  = ConsumerTokenRepository::delete( $id );
			$message = 'Consumer token deleted.';
		} else {
			$result  = ConsumerTokenRepository::revoke( $id );
			$message = 'Consumer token revoked.';
		}

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [ 'error' => $result->get_error_message() ], 400 );
		}

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => $message,
			]
		);
	}

	// Usage / Health ----------------------------------------------------------

	public function usage_summary( \WP_REST_Request $request ): \WP_REST_Response {
		$table  = DB::backtickedTable( UsageLedger::TABLE );
		$where  = [];
		$params = [];

		foreach ( [ 'status', 'provider', 'model' ] as $field ) {
			$value = sanitize_text_field( (string) $request->get_param( $field ) );
			if ( '' !== $value ) {
				$where[]  = DB::backtickedColumn( $field ) . ' = %s';
				$params[] = $value;
			}
		}

		$dateFrom = sanitize_text_field( (string) $request->get_param( 'date_from' ) );
		if ( '' !== $dateFrom ) {
			$where[]  = 'created_at >= %s';
			$params[] = gmdate( 'Y-m-d 00:00:00', strtotime( $dateFrom ) ?: time() );
		}

		$dateTo = sanitize_text_field( (string) $request->get_param( 'date_to' ) );
		if ( '' !== $dateTo ) {
			$where[]  = 'created_at <= %s';
			$params[] = gmdate( 'Y-m-d 23:59:59', strtotime( $dateTo ) ?: time() );
		}

		$whereSql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';
		$sql      = "SELECT status, provider, model, COUNT(*) AS requests, COALESCE(SUM(total_tokens), 0) AS total_tokens
			FROM {$table}
			{$whereSql}
			GROUP BY status, provider, model
			ORDER BY requests DESC
			LIMIT 100";

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$summary = DB::db()->get_results( $params ? DB::db()->prepare( $sql, $params ) : $sql, ARRAY_A ) ?: [];

		return new \WP_REST_Response( [ 'summary' => $summary ] );
	}

	public function credential_health( \WP_REST_Request $request ): \WP_REST_Response {
		$row = KeyRepository::getById( (int) $request->get_param( 'id' ) );
		if ( ! $row ) {
			return new \WP_REST_Response( [ 'error' => 'Key not found.' ], 404 );
		}

		unset( $row['api_key_enc'] );

		return new \WP_REST_Response(
			[
				'credential'       => $row,
				'status'           => CredentialLifecycle::status( $row ),
				'rotation_warning' => CredentialLifecycle::hasRotationWarning( $row ),
			]
		);
	}
}

