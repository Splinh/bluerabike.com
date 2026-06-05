<?php
/**
 * Module Registry — auto-discovers and boots enabled modules.
 *
 * Zero footprint: disabled modules are never instantiated.
 * Discovery via composer classmap → no filesystem scanning.
 *
 * @package SPLAT
 */

namespace SPLAT;

defined( 'ABSPATH' ) || exit;

use SPLAT\Contracts\ModuleInterface;

final class ModuleRegistry {

	private const CONFIG_KEY = 'splat_modules';

	/** @var array<string, class-string<ModuleInterface>> */
	private static array $discovered = [];

	/**
	 * Discover all modules from composer classmap.
	 *
	 * @return array<string, class-string<ModuleInterface>>
	 */
	public static function discover(): array {
		if ( self::$discovered ) {
			return self::$discovered;
		}

		$classmap = SPLAT_PATH . 'vendor/composer/autoload_classmap.php';
		if ( ! is_file( $classmap ) ) {
			return [];
		}

		$classes = require $classmap;

		foreach ( array_keys( $classes ) as $class ) {
			if ( ! str_starts_with( $class, 'SPLAT\\Modules\\' ) ) {
				continue;
			}

			if ( ! class_exists( $class ) ) {
				continue;
			}

			$ref = new \ReflectionClass( $class );
			if ( $ref->isAbstract() || $ref->isInterface() || ! $ref->implementsInterface( ModuleInterface::class ) ) {
				continue;
			}

			/** @var class-string<ModuleInterface> $class */
			self::$discovered[ $class::slug() ] = $class;
		}

		return self::$discovered;
	}

	/**
	 * Get enabled module slugs.
	 *
	 * @return string[]
	 */
	public static function getEnabled(): array {
		return (array) get_option( self::CONFIG_KEY, [] );
	}

	/**
	 * Save enabled modules.
	 *
	 * @param string[] $slugs
	 */
	public static function setEnabled( array $slugs ): void {
		update_option( self::CONFIG_KEY, array_values( array_unique( $slugs ) ) );
	}

	/**
	 * Boot all enabled modules.
	 */
	public static function boot(): void {
		$modules = self::discover();
		$enabled = self::getEnabled();

		foreach ( $modules as $slug => $class ) {
			if ( $class::alwaysActive() || in_array( $slug, $enabled, true ) ) {
				( new $class() )->boot();
			}
		}
	}

	/**
	 * Get all modules metadata for admin UI.
	 *
	 * @return array<string, array{slug: string, title: string, description: string, active: bool, always_active: bool}>
	 */
	public static function allForAdmin(): array {
		$modules = self::discover();
		$enabled = self::getEnabled();
		$result  = [];

		foreach ( $modules as $slug => $class ) {
			$result[ $slug ] = [
				'slug'          => $slug,
				'title'         => $class::title(),
				'description'   => $class::description(),
				'active'        => $class::alwaysActive() || in_array( $slug, $enabled, true ),
				'always_active' => $class::alwaysActive(),
			];
		}

		return $result;
	}
}

