<?php
/**
 * Plugin orchestrator — boots all components.
 *
 * @package SPLAT
 */

namespace SPLAT;

defined( 'ABSPATH' ) || exit;


final class Plugin {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Boot the plugin statically.
	 */
	public static function bootInstance(): void {
		if ( ! class_exists( self::class ) ) {
			return;
		}

		self::instance()->boot();
	}

	/**
	 * Boot the plugin — called on plugins_loaded.
	 */
	public function boot(): void {
		// Initialize migration hook.
		Migration::init();
		$this->registerSchedules();

		// REST API (available on both admin and front for AJAX).
		add_action( 'rest_api_init', [ new Admin\RestController(), 'register_routes' ] );
		add_action( 'rest_api_init', [ new API\ChatCompletionsAPI(), 'register_routes' ] );
		add_action( 'rest_api_init', [ new API\ModelsAPI(), 'register_routes' ] );
		add_action( 'rest_api_init', [ new API\OpenApiSpecAPI(), 'register_routes' ] );

		// Public API hook for cross-module token validation.
		add_filter(
			'splat_validate_consumer_token',
			static function ( mixed $result, string $token ): array|null|\WP_Error {
				if ( null !== $result ) {
					return $result;
				}

				return ( new Auth\ConsumerTokenManager() )->authenticateToken( $token );
			},
			10,
			2
		);

		// Admin-only: settings page + auto-update.
		if ( is_admin() && ! wp_doing_cron() ) {
			( new Admin\SettingsPage() )->init();
			new Updater\GitHubUpdater();
		}

		// Boot enabled modules (zero footprint — disabled = never loaded).
		ModuleRegistry::boot();
	}

	public static function activate(): void {
		Migration::createTables();
		update_option( 'splat_db_version', SPLAT_VERSION );

		if ( ! wp_next_scheduled( 'splat_usage_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'splat_usage_cleanup' );
		}

		if ( ! wp_next_scheduled( 'splat_response_cache_gc' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'splat_response_cache_gc' );
		}
	}

	private function __construct() {}

	/**
	 * @return void
	 */
	private function registerSchedules(): void {
		add_action( 'splat_usage_cleanup', [ Gateway\UsageLedger::class, 'cleanupDetailedRows' ] );
		add_action( 'splat_response_cache_gc', [ Gateway\ResponseCache::class, 'gc' ] );
	}
}

