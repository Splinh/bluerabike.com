<?php
/**
 * ModuleInterface — auto-discovered, conditionally loaded.
 *
 * Modules are PLUGIN INTEGRATIONS or PROJECT-SPECIFIC features:
 * - Plugin integrations: isActive() checks plugin availability.
 * - Project features: isActive() returns true (always-on).
 * - Theme works correctly without any Module loaded.
 *
 * @package HD\Contracts
 */

namespace HD\Contracts;

defined( 'ABSPATH' ) || exit;

interface ModuleInterface extends Bootable {
	/** Unique slug for identification (e.g. 'pll', 'acf'). */
	public static function slug(): string;

	/**
	 * Whether this module should be loaded.
	 *
	 * Plugin integrations: check class_exists() / function_exists().
	 * Always-on modules: return true.
	 */
	public static function isActive(): bool;

	/**
	 * REST API controllers owned by this module.
	 *
	 * @return array<class-string<\WP_REST_Controller>>
	 */
	public static function apiClasses(): array;
}
