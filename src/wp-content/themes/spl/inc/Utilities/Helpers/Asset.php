<?php

namespace HD\Utilities\Helpers;

\defined( 'ABSPATH' ) || die;

/**
 * Collect & enqueue CSS/JS.
 *
 * @author Gaudev
 */
final class Asset {
	// ----------------------------------------

	/**
	 * @param string|array $handle
	 * @param string|bool|null $src
	 * @param array $deps
	 * @param string|bool|null $ver
	 * @param string $media
	 *
	 * @return void
	 */
	public static function enqueueStyle(
		string|array $handle,
		string|bool|null $src = null,
		array $deps = [],
		string|bool|null $ver = null,
		string $media = 'all'
	): void {
		if ( is_array( $handle ) ) {
			$args = wp_parse_args( $handle, [
				'handle' => '',
				'src'    => null,
				'url'    => null,
				'deps'   => [],
				'ver'    => null,
				'media'  => 'all',
			] );

			// url
			if ( empty( $args['src'] ) && ! empty( $args['url'] ) ) {
				$args['src'] = $args['url'];
			}
		} else {
			$args = [
				'handle' => $handle,
				'src'    => $src,
				'deps'   => $deps,
				'ver'    => $ver,
				'media'  => $media,
			];
		}

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
	 * @param string|array $handle
	 * @param string|bool|null $src
	 * @param array $deps
	 * @param string|bool|null $ver
	 * @param bool $in_footer
	 * @param array $extra - Ex. [ 'module', 'defer' ]
	 *
	 * @return void
	 */
	public static function enqueueScript(
		string|array $handle,
		string|bool|null $src = null,
		array $deps = [],
		string|bool|null $ver = null,
		bool $in_footer = true,
		array $extra = []
	): void {
		if ( is_array( $handle ) ) {
			$args = wp_parse_args( $handle, [
				'handle'    => '',
				'src'       => null,
				'url'       => null,
				'deps'      => [],
				'ver'       => null,
				'in_footer' => true,
				'extra'     => [],
				'attrs'     => [],
			] );

			// url
			if ( empty( $args['src'] ) && ! empty( $args['url'] ) ) {
				$args['src'] = $args['url'];
			}

			// attrs
			if ( ! empty( $args['attrs'] ) ) {
				if ( ! is_array( $args['extra'] ) ) {
					$args['extra'] = [];
				}

				if ( empty( $args['extra']['attrs'] ) ) {
					$args['extra']['attrs'] = $args['attrs'];
				}
			}
		} else {
			$args = [
				'handle'    => $handle,
				'src'       => $src,
				'deps'      => $deps,
				'ver'       => $ver,
				'in_footer' => $in_footer,
				'extra'     => $extra,
			];
		}

		// Validate
		if ( empty( $args['handle'] ) || empty( $args['src'] ) ) {
			return;
		}

		if ( ! wp_script_is( $args['handle'], 'registered' ) ) {
			wp_register_script( $args['handle'], $args['src'], $args['deps'], $args['ver'], (bool) $args['in_footer'] );
		}

		wp_enqueue_script( $args['handle'] );

		if ( ! empty( $args['extra'] ) ) {
			wp_script_add_data( $args['handle'], 'extra', $args['extra'] );
		}
	}

	// ----------------------------------------

	/**
	 * @param string $handle
	 * @param string $object_name
	 * @param array|null|bool $l10n
	 *
	 * @return void
	 */
	public static function localize(
		string $handle,
		string $object_name,
		array|bool|null $l10n
	): void {
		if ( empty( $object_name ) || empty( $l10n ) ) {
			return;
		}

		if ( wp_script_is( $handle, 'registered' ) || wp_script_is( $handle, 'enqueued' ) ) {
			wp_localize_script( $handle, $object_name, $l10n );
		}
	}

	// ----------------------------------------

	/**
	 * @param string $handle
	 * @param string|bool|null $css
	 *
	 * @return void
	 */
	public static function inlineStyle( string $handle, string|null|bool $css ): void {
		if ( empty( $css ) ) {
			return;
		}

		if ( wp_style_is( $handle, 'registered' ) || wp_style_is( $handle, 'enqueued' ) ) {
			wp_add_inline_style( $handle, $css );
		}
	}

	// ----------------------------------------

	/**
	 * @param string $handle
	 * @param string|bool|null $code
	 * @param string $position
	 *
	 * @return void
	 */
	public static function inlineScript( string $handle, string|null|bool $code, string $position = 'after' ): void {
		if ( empty( $code ) ) {
			return;
		}

		if ( wp_script_is( $handle, 'registered' ) || wp_script_is( $handle, 'enqueued' ) ) {
			wp_add_inline_script( $handle, $code, $position );
		}
	}
}
