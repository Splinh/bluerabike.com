<?php
/**
 * Plugin DB Migration.
 *
 * @package SPLAT
 */

namespace SPLAT;

defined( 'ABSPATH' ) || exit;

final class Migration {
	private const DB_VERSION_OPTION = 'splat_db_version';

	public static function init(): void {
		add_action( 'admin_init', [ self::class, 'run' ] );
	}

	public static function run(): void {
		$installedVersion = get_option( self::DB_VERSION_OPTION, '0.0.0' );

		// Early return.
		if ( $installedVersion === SPLAT_VERSION ) {
			return;
		}

		if ( version_compare( $installedVersion, SPLAT_VERSION, '<' ) ) {
			self::createTables();
			update_option( self::DB_VERSION_OPTION, SPLAT_VERSION );

			if ( ! wp_next_scheduled( 'splat_usage_cleanup' ) ) {
				wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'splat_usage_cleanup' );
			}

			if ( ! wp_next_scheduled( 'splat_response_cache_gc' ) ) {
				wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'splat_response_cache_gc' );
			}
		}
	}

	public static function createTables(): void {
		DB::createTable(
			KeyRepository::TABLE,
			<<<'SQL'
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			provider varchar(50) NOT NULL,
			credential_type varchar(20) NOT NULL DEFAULT 'provider',
			api_format varchar(32) NOT NULL DEFAULT 'openai_compatible',
			auth_strategy varchar(32) NOT NULL DEFAULT 'bearer',
			label varchar(100) NOT NULL DEFAULT '',
			api_key_hash varchar(8) NOT NULL DEFAULT '',
			api_key_enc text NOT NULL,
			base_url varchar(255) NOT NULL DEFAULT '',
			default_model varchar(191) NOT NULL DEFAULT '',
			default_temperature decimal(4,2) NULL,
			default_max_tokens int unsigned NULL,
			max_prompt_tokens int unsigned NULL,
			tier varchar(10) NOT NULL DEFAULT 'free',
			priority smallint unsigned NOT NULL DEFAULT 10,
			is_active tinyint(1) NOT NULL DEFAULT 1,
			capabilities_json longtext NULL,
			provider_meta_json longtext NULL,
			expires_at datetime NULL,
			rotate_after_days smallint unsigned NULL,
			daily_token_limit bigint unsigned NULL,
			monthly_token_limit bigint unsigned NULL,
			daily_cost_limit decimal(12,6) NULL,
			monthly_cost_limit decimal(12,6) NULL,
			last_usage_json longtext NULL,
			quota_reset_at datetime NULL,
			fail_count int unsigned NOT NULL DEFAULT 0,
			last_used_at datetime NULL,
			last_error varchar(500) NOT NULL DEFAULT '',
			cooldown_until datetime NULL,
			custom_headers text NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY idx_pool_lookup (is_active, priority, provider),
			KEY idx_provider (provider)
			SQL
		);

		self::normalizeExistingKeyRows();

		DB::createTable(
			Auth\ConsumerTokenRepository::TABLE,
			<<<'SQL'
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			name varchar(120) NOT NULL DEFAULT '',
			token_hash char(64) NOT NULL,
			token_prefix varchar(64) NOT NULL DEFAULT '',
			allowed_routes_json longtext NULL,
			allowed_capabilities_json longtext NULL,
			allowed_providers_json longtext NULL,
			allowed_models_json longtext NULL,
			internal_only tinyint(1) NOT NULL DEFAULT 0,
			daily_token_limit bigint unsigned NULL,
			monthly_token_limit bigint unsigned NULL,
			expires_at datetime NULL,
			revoked_at datetime NULL,
			last_used_at datetime NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY idx_token_hash (token_hash),
			KEY idx_token_prefix (token_prefix),
			KEY idx_token_status (revoked_at, expires_at)
			SQL
		);

		DB::createTable(
			Gateway\UsageLedger::TABLE,
			<<<'SQL'
			id bigint unsigned NOT NULL AUTO_INCREMENT,
			consumer_token_id bigint unsigned NULL,
			credential_id bigint unsigned NULL,
			provider varchar(50) NOT NULL DEFAULT '',
			model varchar(191) NOT NULL DEFAULT '',
			request_class varchar(50) NOT NULL DEFAULT 'chat_completions',
			status varchar(20) NOT NULL DEFAULT '',
			error_category varchar(50) NOT NULL DEFAULT '',
			prompt_tokens int unsigned NOT NULL DEFAULT 0,
			completion_tokens int unsigned NOT NULL DEFAULT 0,
			total_tokens int unsigned NOT NULL DEFAULT 0,
			estimated_cost decimal(12,6) NOT NULL DEFAULT 0,
			duration_ms int unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY idx_consumer_date (consumer_token_id, created_at),
			KEY idx_credential_date (credential_id, created_at),
			KEY idx_provider_model_date (provider, model, created_at),
			KEY idx_status_date (status, created_at)
			SQL
		);

		DB::createTable(
			Gateway\ResponseCache::TABLE,
			<<<'SQL'
			hash_key char(64) NOT NULL,
			provider varchar(50) NOT NULL DEFAULT '',
			model varchar(191) NOT NULL DEFAULT '',
			response_json longtext NOT NULL,
			tokens_used int unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			expires_at datetime NOT NULL,
			PRIMARY KEY  (hash_key),
			KEY idx_expires_at (expires_at)
			SQL
		);

		DB::clearSchemaCache();

		// Widen token_prefix for existing installs (dbDelta does not alter column length).
		$tokenTable = DB::backtickedTable( Auth\ConsumerTokenRepository::TABLE );
		if ( DB::tableExists( Auth\ConsumerTokenRepository::TABLE ) && DB::tableHasColumn( Auth\ConsumerTokenRepository::TABLE, 'token_prefix' ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			DB::db()->query( "ALTER TABLE {$tokenTable} MODIFY COLUMN token_prefix varchar(64) NOT NULL DEFAULT ''" );
		}

		foreach ( [ 'daily_cost_limit', 'monthly_cost_limit' ] as $costColumn ) {
			if ( DB::tableExists( Auth\ConsumerTokenRepository::TABLE ) && DB::tableHasColumn( Auth\ConsumerTokenRepository::TABLE, $costColumn ) ) {
				$column = DB::backtickedColumn( $costColumn );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				DB::db()->query( "ALTER TABLE {$tokenTable} DROP COLUMN {$column}" );
				DB::clearSchemaCache( Auth\ConsumerTokenRepository::TABLE );
			}
		}
	}

	/**
	 * Normalize existing rows after dbDelta adds the gateway columns.
	 *
	 * @return void
	 */
	private static function normalizeExistingKeyRows(): void {
		if ( ! DB::tableExists( KeyRepository::TABLE ) ) {
			return;
		}

		$table = DB::backtickedTable( KeyRepository::TABLE );

		if ( DB::tableHasColumn( KeyRepository::TABLE, 'credential_type' ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			DB::db()->query( "UPDATE {$table} SET credential_type = 'provider' WHERE credential_type = '' OR credential_type IS NULL" );
		}

		if ( DB::tableHasColumn( KeyRepository::TABLE, 'api_format' ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			DB::db()->query(
				"UPDATE {$table}
				SET api_format = CASE api_format
					WHEN 'openai' THEN 'openai_compatible'
					WHEN 'google' THEN 'google_gemini'
					WHEN 'anthropic' THEN 'anthropic_messages'
					ELSE api_format
				END"
			);
		}

		if ( DB::tableHasColumn( KeyRepository::TABLE, 'auth_strategy' ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			DB::db()->query(
				"UPDATE {$table}
				SET auth_strategy = CASE api_format
					WHEN 'google_gemini' THEN 'query_api_key'
					WHEN 'anthropic_messages' THEN 'x_api_key'
					ELSE 'bearer'
				END
				WHERE auth_strategy = '' OR auth_strategy IS NULL"
			);
		}
	}
}

