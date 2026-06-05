<?php
/**
 * Provider credential lifecycle status.
 *
 * @package SPLAT\Gateway
 */

namespace SPLAT\Gateway;

defined( 'ABSPATH' ) || exit;

final class CredentialLifecycle {

	private const EXPIRING_SOON_DAYS = 7;

	/**
	 * @param array $credential
	 *
	 * @return string
	 */
	public static function status( array $credential ): string {
		if ( empty( $credential['is_active'] ) ) {
			return 'disabled';
		}

		if ( self::isExpired( $credential ) ) {
			return 'expired';
		}

		if ( self::isQuotaExceeded( $credential ) ) {
			return 'quota_exceeded';
		}

		if ( ! empty( $credential['fail_count'] ) && self::inCooldown( $credential ) ) {
			return 'failing';
		}

		if ( self::isExpiringSoon( $credential ) ) {
			return 'expiring_soon';
		}

		return 'active';
	}

	/**
	 * @param array $credential
	 *
	 * @return bool
	 */
	public static function isSelectable( array $credential ): bool {
		return in_array( self::status( $credential ), [ 'active', 'expiring_soon' ], true );
	}

	/**
	 * @param array $credential
	 *
	 * @return bool
	 */
	public static function hasRotationWarning( array $credential ): bool {
		$days = isset( $credential['rotate_after_days'] ) ? (int) $credential['rotate_after_days'] : 0;
		if ( $days <= 0 || empty( $credential['created_at'] ) ) {
			return false;
		}

		$createdAt = strtotime( (string) $credential['created_at'] );
		if ( false === $createdAt ) {
			return false;
		}

		return self::now() >= ( $createdAt + ( $days * DAY_IN_SECONDS ) );
	}

	/**
	 * @param array $credential
	 *
	 * @return bool
	 */
	private static function isExpired( array $credential ): bool {
		if ( empty( $credential['expires_at'] ) ) {
			return false;
		}

		$expiresAt = strtotime( (string) $credential['expires_at'] );

		return $expiresAt !== false && $expiresAt <= self::now();
	}

	/**
	 * @param array $credential
	 *
	 * @return bool
	 */
	private static function isExpiringSoon( array $credential ): bool {
		if ( empty( $credential['expires_at'] ) ) {
			return false;
		}

		$expiresAt = strtotime( (string) $credential['expires_at'] );
		if ( false === $expiresAt ) {
			return false;
		}

		return $expiresAt <= ( self::now() + ( self::EXPIRING_SOON_DAYS * DAY_IN_SECONDS ) );
	}

	/**
	 * @param array $credential
	 *
	 * @return bool
	 */
	private static function isQuotaExceeded( array $credential ): bool {
		$usage = self::decodeUsage( $credential['last_usage_json'] ?? null );

		foreach ( [ 'daily_token_limit', 'monthly_token_limit', 'daily_cost_limit', 'monthly_cost_limit' ] as $limitKey ) {
			$limit = isset( $credential[ $limitKey ] ) ? (float) $credential[ $limitKey ] : 0;
			if ( $limit <= 0 ) {
				continue;
			}

			$usageKey = str_replace( '_limit', '_used', $limitKey );
			if ( (float) ( $usage[ $usageKey ] ?? 0 ) >= $limit ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param mixed $value
	 *
	 * @return array
	 */
	private static function decodeUsage( mixed $value ): array {
		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			return [];
		}

		$decoded = json_decode( $value, true );

		return is_array( $decoded ) ? $decoded : [];
	}

	/**
	 * @param array $credential
	 *
	 * @return bool
	 */
	private static function inCooldown( array $credential ): bool {
		if ( empty( $credential['cooldown_until'] ) ) {
			return false;
		}

		$cooldownUntil = strtotime( (string) $credential['cooldown_until'] );

		return $cooldownUntil !== false && $cooldownUntil > self::now();
	}

	/**
	 * @return int
	 */
	private static function now(): int {
		return strtotime( current_time( 'mysql', true ) ) ?: time();
	}
}

