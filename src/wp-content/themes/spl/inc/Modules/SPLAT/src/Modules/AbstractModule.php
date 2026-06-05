<?php
/**
 * Base class for all SPLAT modules.
 *
 * @package SPLAT\Modules
 */

namespace SPLAT\Modules;

defined( 'ABSPATH' ) || exit;

use SPLAT\Contracts\ModuleInterface;

abstract class AbstractModule implements ModuleInterface {

	/** @var array<class-string, array> In-memory option cache. */
	private static array $optionCache = [];

	// ── Defaults ────────────────────────────────────

	public static function optionKey(): string {
		return 'splat_' . static::slug();
	}

	public static function defaults(): array {
		return [];
	}

	public static function alwaysActive(): bool {
		return false;
	}

	// ── Options ─────────────────────────────────────

	/**
	 * Get merged options: defaults + saved values.
	 *
	 * @return array<string, mixed>
	 */
	public static function getOptions(): array {
		return array_merge( static::defaults(), (array) get_option( static::optionKey(), [] ) );
	}

	/**
	 * Cached options (lazy-loaded, no defaults merge).
	 *
	 * @return array<string, mixed>
	 */
	public static function getCachedOptions(): array {
		$class = static::class;

		if ( ! isset( self::$optionCache[ $class ] ) ) {
			self::$optionCache[ $class ] = (array) get_option( static::optionKey(), [] );
		}

		return self::$optionCache[ $class ];
	}

	/**
	 * Reset cached options.
	 */
	public static function resetCache(): void {
		unset( self::$optionCache[ static::class ] );
	}
}

