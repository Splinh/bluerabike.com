<?php
/**
 * Asset manager for SPLAT plugin.
 *
 * Resolves CSS/JS from Vite manifest. Zero footprint — only loads on admin page.
 *
 * @package SPLAT
 */

namespace SPLAT;

defined( 'ABSPATH' ) || exit;

use SPLAT\Traits\Vite;

final class Asset {

	use Vite;

	// ----------------------------------------

	/**
	 * Get resolved handle for a manifest entry.
	 */
	public static function handle( ?string $entry = null, string $handlePrefix = 'splat-' ): ?string {
		if ( empty( $entry ) ) {
			return null;
		}

		$resolve = self::manifestResolve( $entry, $handlePrefix );

		return $resolve['handle'] ?? null;
	}

	// ----------------------------------------

	/**
	 * Enqueue CSS from Vite manifest.
	 */
	public static function enqueueCSS( ?string $entry = null, array $deps = [], string|bool|null $ver = null, string $media = 'all' ): void {
		$resolve = self::manifestResolve( $entry );
		if ( empty( $resolve ) ) {
			return;
		}

		$resolve['deps']  = $deps;
		$resolve['ver']   = $ver;
		$resolve['media'] = $media;

		self::enqueueStyle( $resolve );
	}

	// ----------------------------------------

	/**
	 * Enqueue JS from Vite manifest (auto-enqueues associated CSS).
	 */
	public static function enqueueJS( ?string $entry = null, array $deps = [], string|bool|null $ver = null, bool $inFooter = true ): void {
		$resolve = self::manifestResolve( $entry );
		if ( empty( $resolve ) ) {
			return;
		}

		$resolve['deps']      = $deps;
		$resolve['ver']       = $ver;
		$resolve['in_footer'] = $inFooter;

		self::enqueueScript( $resolve );

		// Auto-enqueue CSS dependencies from JS manifest.
		if ( empty( $resolve['css'] ) || ! is_array( $resolve['css'] ) ) {
			return;
		}

		foreach ( $resolve['css'] as $key => $cssFile ) {
			if ( str_contains( $cssFile, 'vendor.' ) ) {
				continue;
			}

			$suffix = 0 === $key ? '-css' : '-' . $key . '-css';
			$handle = preg_replace( '/-js$/', $suffix, $resolve['handle'] );

			if ( wp_style_is( $handle ) || wp_style_is( $handle, 'registered' ) ) {
				continue;
			}

			self::enqueueStyle(
				[
					'handle' => $handle,
					'src'    => self::assetUrl( $cssFile ),
					'deps'   => [],
					'ver'    => $ver,
					'media'  => 'all',
				]
			);
		}
	}

	// ----------------------------------------

	/**
	 * Enqueue a stylesheet.
	 */
	public static function enqueueStyle( string|array $handle, string|bool|null $src = null, array $deps = [], string|bool|null $ver = null, string $media = 'all' ): void {
		$args = is_array( $handle )
			? wp_parse_args(
				$handle,
				[
					'handle' => '',
					'src'    => null,
					'deps'   => [],
					'ver'    => null,
					'media'  => 'all',
				]
			)
			: [
				'handle' => $handle,
				'src'    => $src,
				'deps'   => $deps,
				'ver'    => $ver,
				'media'  => $media,
			];

		if ( empty( $args['handle'] ) || empty( $args['src'] ) ) {
			return;
		}

		if ( ! wp_style_is( $args['handle'], 'registered' ) ) {
			wp_register_style( $args['handle'], $args['src'], $args['deps'], $args['ver'], $args['media'] );
		}

		wp_enqueue_style( $args['handle'] );
	}

	// ----------------------------------------

	/**
	 * Enqueue a script.
	 */
	public static function enqueueScript( string|array $handle, string|bool|null $src = null, array $deps = [], string|bool|null $ver = null, bool $inFooter = true ): void {
		if ( is_array( $handle ) ) {
			$args        = wp_parse_args(
				$handle,
				[
					'handle'    => '',
					'src'       => null,
					'url'       => null,
					'deps'      => [],
					'ver'       => null,
					'in_footer' => true,
				]
			);
			$args['src'] = $args['src'] ?: $args['url'];
		} else {
			$args = [
				'handle'    => $handle,
				'src'       => $src,
				'deps'      => $deps,
				'ver'       => $ver,
				'in_footer' => $inFooter,
			];
		}

		if ( empty( $args['handle'] ) || empty( $args['src'] ) ) {
			return;
		}

		if ( ! wp_script_is( $args['handle'], 'registered' ) ) {
			wp_register_script( $args['handle'], $args['src'], $args['deps'], $args['ver'], (bool) $args['in_footer'] );
		}

		wp_enqueue_script( $args['handle'] );
	}

	// ----------------------------------------

	/**
	 * Localize a script with data.
	 */
	public static function localize( string $handle, string $objectName, array|bool|null $l10n ): void {
		if ( empty( $objectName ) || empty( $l10n ) ) {
			return;
		}

		if ( wp_script_is( $handle, 'registered' ) || wp_script_is( $handle ) ) {
			wp_localize_script( $handle, $objectName, $l10n );
		}
	}
	// ----------------------------------------

	/**
	 * Asset version based on manifest file hash.
	 * Changes only when Vite rebuilds — enables proper browser caching.
	 *
	 * @return string|false
	 */
	public static function version(): string|false {
		static $hash;

		if ( isset( $hash ) ) {
			return $hash ?: false;
		}

		$path = rtrim( SPLAT_PATH, '/\\' ) . '/assets/.vite/manifest.json';
		$hash = is_file( $path ) ? substr( (string) md5_file( $path ), 0, 8 ) : '';

		return $hash ?: false;
	}
}

