<?php
/**
 * Key Pool Manager — selects and rotates API keys.
 *
 * @package SPLAT\Pool
 */

namespace SPLAT;

defined( 'ABSPATH' ) || exit;


final class KeyPoolManager {

	/**
	 * Get the next available key from the pool.
	 *
	 * Priority order:
	 * 1. Preferred provider keys first.
	 * 2. Preferred tier (free/paid) first.
	 * 3. Lower priority number = tried first.
	 * 4. Fewer failures = tried first.
	 * 5. Least recently used = tried first.
	 *
	 * @param string|null    $preferredProvider Provider slug to prioritize.
	 * @param array<int,int> $excludeIds        Key IDs already tried this request.
	 * @param string|null    $tierPreference    Tier to prioritize ('free' or 'paid').
	 *
	 * @return array|null Key row or null if pool exhausted.
	 */
	public static function getNextKey( ?string $preferredProvider = null, array $excludeIds = [], ?string $tierPreference = null ): ?array {
		$keys = KeyRepository::getActiveKeys( $preferredProvider, $tierPreference );

		foreach ( $keys as $key ) {
			if ( in_array( (int) $key['id'], $excludeIds, true ) ) {
				continue;
			}

			return $key;
		}

		return null;
	}

	/**
	 * Handle a failed API call — apply cooldown or disable key.
	 *
	 * @param int    $keyId      Key ID.
	 * @param int    $httpStatus HTTP status code.
	 * @param string $error      Error message.
	 */
	public static function handleFailure( int $keyId, int $httpStatus, string $error ): void {
		// 401/403 → permanent disable (invalid key).
		if ( 401 === $httpStatus || 403 === $httpStatus ) {
			KeyRepository::disable( $keyId, $error );
			return;
		}

		// 429 or 5xx → temporary cooldown.
		$cooldownSeconds = 429 === $httpStatus
			? (int) Settings::get( 'cooldown_429' )
			: (int) Settings::get( 'cooldown_5xx' );

		KeyRepository::markFailed( $keyId, $error, $cooldownSeconds );
	}

	/**
	 * Handle a successful API call.
	 */
	public static function handleSuccess( int $keyId ): void {
		KeyRepository::markSuccess( $keyId );
	}

	/**
	 * Get the preferred provider from settings.
	 */
	public static function getPreferredProvider(): string {
		return (string) Settings::get( 'preferred_provider' );
	}

	/**
	 * Resolve tier preference based on complexity and settings.
	 *
	 * @param string $complexity 'low', 'medium', or 'high'.
	 *
	 * @return string|null 'free', 'paid', or null (no preference).
	 */
	public static function resolveTierPreference( string $complexity = 'low' ): ?string {
		$settings = Settings::get();

		if ( empty( $settings['prefer_free_keys'] ) ) {
			return null;
		}

		return match ( $settings['paid_key_strategy'] ?? 'high_complexity_first' ) {
			'always'                => null,
			'fallback_only'         => 'free',
			'high_complexity_first' => 'high' === $complexity ? 'paid' : 'free',
			default                 => 'free',
		};
	}
}

