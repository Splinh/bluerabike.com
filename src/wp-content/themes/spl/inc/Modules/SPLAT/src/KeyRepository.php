<?php
/**
 * Key Repository — CRUD for splat_ai_keys table.
 *
 * @package SPLAT\Repository
 */

namespace SPLAT;

defined( 'ABSPATH' ) || exit;

final class KeyRepository {

	public const TABLE = 'splat_ai_keys';

	// ── Queries ─────────────────────────────────────

	/**
	 * Get active keys ready for use (not in cooldown).
	 *
	 * @param string|null $preferredProvider Provider to prioritize.
	 * @param string|null $tierPreference    Tier to prioritize ('free' or 'paid').
	 *
	 * @return array<int, array>
	 */
	public static function getActiveKeys( ?string $preferredProvider = null, ?string $tierPreference = null ): array {
		$table       = DB::backtickedTable( self::TABLE );
		$now         = current_time( 'mysql', true );
		$hasExpiry   = DB::tableHasColumn( self::TABLE, 'expires_at' );
		$expiryWhere = $hasExpiry ? 'AND (expires_at IS NULL OR expires_at > %s)' : '';
		$params      = [ $now ];

		if ( $hasExpiry ) {
			$params[] = $now;
		}

		$params[] = $preferredProvider ?? '';
		$params[] = $tierPreference ?? '';

		$sql = DB::db()->prepare(
			"SELECT * FROM {$table}
			WHERE is_active = 1
			AND (cooldown_until IS NULL OR cooldown_until < %s)
			{$expiryWhere}
			ORDER BY
				CASE WHEN provider = %s THEN 0 ELSE 1 END,
				CASE WHEN tier = %s THEN 0 ELSE 1 END,
				priority ASC,
				fail_count ASC,
				last_used_at ASC",
			$params
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return DB::db()->get_results( $sql, ARRAY_A ) ?: [];
	}

	/**
	 * Get active keys that support a capability.
	 *
	 * @param string|null $provider Provider to prioritize/filter.
	 * @param string|null $tierPreference Tier to prioritize.
	 *
	 * @return array<int, array>
	 */
	public static function getActiveKeysByCapability( string $capability, ?string $provider = null, ?string $tierPreference = null ): array {
		$capability = sanitize_key( $capability );
		if ( '' === $capability ) {
			return [];
		}

		return array_values(
			array_filter(
				self::getActiveKeys( $provider, $tierPreference ),
				static function ( array $row ) use ( $capability ): bool {
					$capabilities = \SPLAT\Providers\ProviderRegistry::capabilitiesFor( (string) ( $row['provider'] ?? '' ) );
					$overrides    = self::decodeJsonObject( $row['capabilities_json'] ?? null );

					foreach ( $overrides as $name => $enabled ) {
						$capabilities[ sanitize_key( (string) $name ) ] = (bool) $enabled;
					}

					return ! empty( $capabilities[ $capability ] );
				}
			)
		);
	}

	/**
	 * Get all keys for admin listing (masked).
	 *
	 * @return array<int, array>
	 */
	public static function getAll(): array {
		$table = DB::backtickedTable( self::TABLE );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$rows = DB::db()->get_results( "SELECT * FROM {$table} ORDER BY priority ASC, id ASC", ARRAY_A ) ?: [];

		// Mask keys for display.
		foreach ( $rows as &$row ) {
			unset( $row['api_key_enc'] );
		}

		return $rows;
	}

	/**
	 * Get a single key by ID.
	 */
	public static function getById( int $id ): ?array {
		return DB::getOne( self::TABLE, 'id = %d', [ $id ] );
	}

	// ── Mutations ───────────────────────────────────

	/**
	 * Create a new key.
	 *
	 * @return int|\WP_Error Insert ID or error.
	 */
	public static function create( array $data ): int|\WP_Error {
		$plainKey = $data['api_key'] ?? '';
		if ( '' === $plainKey ) {
			return new \WP_Error( 'missing_key', 'API key is required.' );
		}

		$tier = sanitize_text_field( $data['tier'] ?? 'free' );
		$tier = in_array( $tier, [ 'free', 'paid' ], true ) ? $tier : 'free';

		$customHeaders = self::encodeCustomHeaders( $data['custom_headers'] ?? '' );
		if ( is_wp_error( $customHeaders ) ) {
			return $customHeaders;
		}

		return DB::insertOneRow(
			self::TABLE,
			[
				'credential_type'     => 'provider',
				'provider'            => sanitize_text_field( $data['provider'] ?? '' ),
				'api_format'          => self::normalizeApiFormat( $data['api_format'] ?? 'openai_compatible' ),
				'auth_strategy'       => self::normalizeAuthStrategy( $data['auth_strategy'] ?? '', $data['api_format'] ?? 'openai_compatible' ),
				'label'               => sanitize_text_field( $data['label'] ?? '' ),
				'api_key_hash'        => KeyEncryptor::mask( $plainKey ),
				'api_key_enc'         => KeyEncryptor::encrypt( $plainKey ),
				'base_url'            => esc_url_raw( $data['base_url'] ?? '' ),
				'default_model'       => sanitize_text_field( $data['default_model'] ?? '' ),
				'default_temperature' => self::nullableDecimal( $data['default_temperature'] ?? null, 0, 2 ),
				'default_max_tokens'  => self::nullableInt( $data['default_max_tokens'] ?? null ),
				'max_prompt_tokens'   => self::nullableInt( $data['max_prompt_tokens'] ?? null ),
				'tier'                => $tier,
				'priority'            => absint( $data['priority'] ?? 10 ),
				'is_active'           => 1,
				'capabilities_json'   => self::encodeJsonObject( $data['capabilities_json'] ?? ( $data['capabilities'] ?? null ) ),
				'provider_meta_json'  => self::encodeJsonObject( $data['provider_meta_json'] ?? ( $data['provider_meta'] ?? null ) ),
				'expires_at'          => self::nullableDateTime( $data['expires_at'] ?? null ),
				'rotate_after_days'   => self::nullableInt( $data['rotate_after_days'] ?? null ),
				'daily_token_limit'   => self::nullableInt( $data['daily_token_limit'] ?? null ),
				'monthly_token_limit' => self::nullableInt( $data['monthly_token_limit'] ?? null ),
				'daily_cost_limit'    => self::nullableDecimal( $data['daily_cost_limit'] ?? null ),
				'monthly_cost_limit'  => self::nullableDecimal( $data['monthly_cost_limit'] ?? null ),
				'quota_reset_at'      => self::nullableDateTime( $data['quota_reset_at'] ?? null ),
				'custom_headers'      => $customHeaders,
			]
		);
	}

	/**
	 * Update a key. Re-encrypts if api_key is provided.
	 *
	 * @return int|\WP_Error Rows affected or error.
	 */
	public static function update( int $id, array $data ): int|\WP_Error {
		$update = [];

		if ( isset( $data['provider'] ) ) {
			$update['provider'] = sanitize_text_field( $data['provider'] );
		}
		if ( isset( $data['api_format'] ) ) {
			$update['api_format'] = self::normalizeApiFormat( $data['api_format'] );
		}
		if ( isset( $data['auth_strategy'] ) || isset( $data['api_format'] ) ) {
			$update['auth_strategy'] = self::normalizeAuthStrategy( $data['auth_strategy'] ?? '', $update['api_format'] ?? ( $data['api_format'] ?? 'openai_compatible' ) );
		}
		if ( isset( $data['label'] ) ) {
			$update['label'] = sanitize_text_field( $data['label'] );
		}
		if ( isset( $data['base_url'] ) ) {
			$update['base_url'] = esc_url_raw( $data['base_url'] );
		}
		if ( isset( $data['default_model'] ) ) {
			$update['default_model'] = sanitize_text_field( $data['default_model'] );
		}
		if ( array_key_exists( 'default_temperature', $data ) ) {
			$update['default_temperature'] = self::nullableDecimal( $data['default_temperature'], 0, 2 );
		}
		if ( array_key_exists( 'default_max_tokens', $data ) ) {
			$update['default_max_tokens'] = self::nullableInt( $data['default_max_tokens'] );
		}
		if ( array_key_exists( 'max_prompt_tokens', $data ) ) {
			$update['max_prompt_tokens'] = self::nullableInt( $data['max_prompt_tokens'] );
		}
		if ( isset( $data['priority'] ) ) {
			$update['priority'] = absint( $data['priority'] );
		}
		if ( isset( $data['tier'] ) ) {
			$tier           = sanitize_text_field( $data['tier'] );
			$update['tier'] = in_array( $tier, [ 'free', 'paid' ], true ) ? $tier : 'free';
		}
		if ( isset( $data['is_active'] ) ) {
			$update['is_active'] = (int) (bool) $data['is_active'];
		}
		if ( array_key_exists( 'capabilities_json', $data ) || array_key_exists( 'capabilities', $data ) ) {
			$update['capabilities_json'] = self::encodeJsonObject( $data['capabilities_json'] ?? ( $data['capabilities'] ?? null ) );
		}
		if ( array_key_exists( 'provider_meta_json', $data ) || array_key_exists( 'provider_meta', $data ) ) {
			$update['provider_meta_json'] = self::encodeJsonObject( $data['provider_meta_json'] ?? ( $data['provider_meta'] ?? null ) );
		}
		if ( array_key_exists( 'expires_at', $data ) ) {
			$update['expires_at'] = self::nullableDateTime( $data['expires_at'] );
		}
		if ( array_key_exists( 'rotate_after_days', $data ) ) {
			$update['rotate_after_days'] = self::nullableInt( $data['rotate_after_days'] );
		}
		if ( array_key_exists( 'daily_token_limit', $data ) ) {
			$update['daily_token_limit'] = self::nullableInt( $data['daily_token_limit'] );
		}
		if ( array_key_exists( 'monthly_token_limit', $data ) ) {
			$update['monthly_token_limit'] = self::nullableInt( $data['monthly_token_limit'] );
		}
		if ( array_key_exists( 'daily_cost_limit', $data ) ) {
			$update['daily_cost_limit'] = self::nullableDecimal( $data['daily_cost_limit'] );
		}
		if ( array_key_exists( 'monthly_cost_limit', $data ) ) {
			$update['monthly_cost_limit'] = self::nullableDecimal( $data['monthly_cost_limit'] );
		}
		if ( array_key_exists( 'quota_reset_at', $data ) ) {
			$update['quota_reset_at'] = self::nullableDateTime( $data['quota_reset_at'] );
		}

		// Re-encrypt if new key provided.
		if ( ! empty( $data['api_key'] ) ) {
			$plainKey               = $data['api_key'];
			$update['api_key_hash'] = KeyEncryptor::mask( $plainKey );
			$update['api_key_enc']  = KeyEncryptor::encrypt( $plainKey );
		}

		// Handle custom headers.
		if ( array_key_exists( 'custom_headers', $data ) ) {
			$customHeaders = self::encodeCustomHeaders( $data['custom_headers'] );
			if ( is_wp_error( $customHeaders ) ) {
				return $customHeaders;
			}
			$update['custom_headers'] = $customHeaders;
		}

		if ( ! $update ) {
			return 0;
		}

		return DB::updateOneRow( self::TABLE, $id, $update );
	}

	/**
	 * Delete a key.
	 */
	public static function delete( int $id ): int|\WP_Error {
		return DB::deleteOneRow( self::TABLE, $id );
	}

	// ── Pool Status ─────────────────────────────────

	/**
	 * Mark a key as failed (increment counter + set cooldown).
	 */
	public static function markFailed( int $id, string $error, int $cooldownSeconds = 60 ): void {
		$table = DB::backtickedTable( self::TABLE );

		DB::db()->query(
			DB::db()->prepare(
				"UPDATE {$table} SET
					fail_count     = fail_count + 1,
					last_error     = %s,
					cooldown_until = DATE_ADD(UTC_TIMESTAMP(), INTERVAL %d SECOND)
				WHERE id = %d",
				sanitize_text_field( substr( $error, 0, 500 ) ),
				$cooldownSeconds,
				$id
			)
		);
	}

	/**
	 * Mark a key as successfully used.
	 */
	public static function markSuccess( int $id ): void {
		$table = DB::backtickedTable( self::TABLE );

		DB::db()->query(
			DB::db()->prepare(
				"UPDATE {$table} SET
					fail_count     = 0,
					last_error     = '',
					cooldown_until = NULL,
					last_used_at   = UTC_TIMESTAMP()
				WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Disable a key permanently (invalid auth).
	 */
	public static function disable( int $id, string $reason ): void {
		DB::updateOneRow(
			self::TABLE,
			$id,
			[
				'is_active'  => 0,
				'last_error' => sanitize_text_field( substr( $reason, 0, 500 ) ),
			]
		);
	}

	/**
	 * Reset all cooldowns (admin action).
	 */
	public static function resetAllCooldowns(): void {
		$table = DB::backtickedTable( self::TABLE );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		DB::db()->query( "UPDATE {$table} SET cooldown_until = NULL, fail_count = 0, last_error = ''" );
	}

	/**
	 * Decrypt a key row's api_key_enc for use.
	 */
	public static function decryptKey( array $row ): string {
		return KeyEncryptor::decrypt( $row['api_key_enc'] ?? '' );
	}

	// ── Custom Headers ──────────────────────────────

	/** Headers that must never be overridden by custom input. */
	private const BLOCKED_HEADERS = [
		'authorization',
		'content-type',
		'x-goog-api-key',
		'x-api-key',
		'anthropic-version',
	];

	/** Max stored JSON size in bytes. */
	private const MAX_HEADERS_SIZE = 8000;

	/**
	 * Sanitize and encode custom headers for storage.
	 *
	 * Accepts an associative array or a JSON object string.
	 * Returns a JSON string or null (empty), or WP_Error on validation failure.
	 *
	 * @param array|string $input Raw input from admin/API.
	 *
	 * @return string|null|\WP_Error
	 */
	public static function encodeCustomHeaders( array|string $input ): string|null|\WP_Error {
		// Normalize input.
		if ( is_string( $input ) ) {
			$input = trim( $input );
			if ( '' === $input ) {
				return null;
			}
			$decoded = json_decode( $input, true );
			if ( ! is_array( $decoded ) ) {
				return new \WP_Error( 'invalid_json', 'Custom headers must be a valid JSON object.' );
			}
			$input = $decoded;
		}

		if ( empty( $input ) ) {
			return null;
		}

		$sanitized = [];
		foreach ( $input as $name => $value ) {
			$name = trim( (string) $name );

			// Validate header name: conservative token pattern.
			if ( ! preg_match( '/^[A-Za-z0-9-]+$/', $name ) ) {
				return new \WP_Error( 'invalid_header_name', sprintf( 'Invalid header name: %s', $name ) );
			}

			// Block system/auth headers.
			if ( in_array( strtolower( $name ), self::BLOCKED_HEADERS, true ) ) {
				return new \WP_Error( 'blocked_header', sprintf( 'Header "%s" cannot be set as a custom header.', $name ) );
			}

			// Value must be a scalar string, no CRLF injection.
			$value = (string) $value;
			if ( preg_match( '/[\r\n]/', $value ) ) {
				return new \WP_Error( 'header_injection', sprintf( 'Header "%s" value contains invalid characters.', $name ) );
			}

			$sanitized[ $name ] = sanitize_text_field( $value );
		}

		if ( empty( $sanitized ) ) {
			return null;
		}

		$json = wp_json_encode( $sanitized );
		if ( strlen( $json ) > self::MAX_HEADERS_SIZE ) {
			return new \WP_Error( 'headers_too_large', 'Custom headers JSON exceeds maximum allowed size.' );
		}

		return $json;
	}

	/**
	 * Decode stored custom headers JSON into an associative array.
	 *
	 * @param array $row Key row from DB.
	 *
	 * @return array<string, string>
	 */
	public static function decodeCustomHeaders( array $row ): array {
		$raw = $row['custom_headers'] ?? '';
		if ( empty( $raw ) ) {
			return [];
		}

		$decoded = json_decode( $raw, true );

		return is_array( $decoded ) ? $decoded : [];
	}

	/**
	 * @param mixed $apiFormat
	 *
	 * @return string
	 */
	private static function normalizeApiFormat( mixed $apiFormat ): string {
		return match ( sanitize_key( (string) $apiFormat ) ) {
			'openai', 'openai_compatible'       => 'openai_compatible',
			'google', 'google_gemini'           => 'google_gemini',
			'anthropic', 'anthropic_messages'   => 'anthropic_messages',
			default                             => 'openai_compatible',
		};
	}

	/**
	 * @param mixed $authStrategy
	 * @param mixed $apiFormat
	 *
	 * @return string
	 */
	private static function normalizeAuthStrategy( mixed $authStrategy, mixed $apiFormat ): string {
		$authStrategy = sanitize_key( (string) $authStrategy );
		if ( in_array( $authStrategy, [ 'bearer', 'query_api_key', 'x_api_key' ], true ) ) {
			return $authStrategy;
		}

		return match ( self::normalizeApiFormat( $apiFormat ) ) {
			'google_gemini'      => 'query_api_key',
			'anthropic_messages' => 'x_api_key',
			default              => 'bearer',
		};
	}

	/**
	 * @param mixed    $value
	 * @param int|null $min
	 * @param int|null $max
	 *
	 * @return float|null
	 */
	private static function nullableDecimal( mixed $value, ?int $min = null, ?int $max = null ): ?float {
		if ( $value === null || $value === '' ) {
			return null;
		}

		$value = (float) $value;
		if ( $min !== null ) {
			$value = max( $min, $value );
		}
		if ( $max !== null ) {
			$value = min( $max, $value );
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return int|null
	 */
	private static function nullableInt( mixed $value ): ?int {
		if ( $value === null || $value === '' ) {
			return null;
		}

		return max( 0, absint( $value ) );
	}

	/**
	 * @param mixed $value
	 *
	 * @return string|null
	 */
	private static function nullableDateTime( mixed $value ): ?string {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return null;
		}

		$timestamp = strtotime( $value );
		if ( false === $timestamp ) {
			return null;
		}

		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}

	/**
	 * @param mixed $value
	 *
	 * @return string|null
	 */
	private static function encodeJsonObject( mixed $value ): ?string {
		if ( $value === null || '' === $value ) {
			return null;
		}

		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true );
			if ( ! is_array( $decoded ) ) {
				return null;
			}
			$value = $decoded;
		}

		if ( ! is_array( $value ) ) {
			return null;
		}

		return wp_json_encode( $value ) ?: null;
	}

	/**
	 * @param mixed $value
	 *
	 * @return array
	 */
	private static function decodeJsonObject( mixed $value ): array {
		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			return [];
		}

		$decoded = json_decode( $value, true );

		return is_array( $decoded ) ? $decoded : [];
	}
}

