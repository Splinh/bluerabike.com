<?php
/**
 * Centralized settings for HD AI Toolkit.
 *
 * Single Source of Truth for all splat_settings defaults.
 * Consumers MUST use Settings::get() instead of raw get_option().
 *
 * @package SPLAT
 */

namespace SPLAT;

defined( 'ABSPATH' ) || exit;

final class Settings {

	public const DEFAULTS = [
		'preferred_provider' => '',
		'max_retries'        => 3,
		'cooldown_429'       => 60,
		'cooldown_5xx'       => 300,
		'request_timeout'    => 30,
		'prefer_free_keys'   => true,
		'paid_key_strategy'  => 'high_complexity_first',
		'cache_ttl'          => 86400,
		'clean_uninstall'    => false,
	];

	/**
	 * Get all settings merged with defaults, or a single key.
	 *
	 * @param string|null $key     Specific setting key, or null for all.
	 * @param mixed       $fallback Fallback if key doesn't exist (only used when $key is set).
	 *
	 * @return mixed
	 */
	public static function get( ?string $key = null, mixed $fallback = null ): mixed {
		$options = array_merge( self::DEFAULTS, get_option( 'splat_settings', [] ) );

		if ( null !== $key ) {
			return $options[ $key ] ?? $fallback;
		}

		return $options;
	}
}

