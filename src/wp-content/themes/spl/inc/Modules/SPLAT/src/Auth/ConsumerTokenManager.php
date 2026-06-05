<?php
/**
 * Consumer token authentication.
 *
 * @package SPLAT\Auth
 */

namespace SPLAT\Auth;

defined( 'ABSPATH' ) || exit;

final class ConsumerTokenManager {

	/**
	 * @param array $data
	 *
	 * @return array{id: int, token: string, prefix: string}|\WP_Error
	 */
	public function create( array $data ): array|\WP_Error {
		return ConsumerTokenRepository::create( $data );
	}

	/**
	 * @param string $authorizationHeader
	 *
	 * @return array|null|\WP_Error
	 */
	public function authenticateBearer( string $authorizationHeader ): array|null|\WP_Error {
		if ( ! preg_match( '/^Bearer\s+(.+)$/i', trim( $authorizationHeader ), $matches ) ) {
			return new \WP_Error( 'invalid_bearer_token', 'Missing bearer token.' );
		}

		return $this->authenticateToken( $matches[1] );
	}

	/**
	 * @param string $token
	 *
	 * @return array|null|\WP_Error
	 */
	public function authenticateToken( string $token ): array|null|\WP_Error {
		$row = ConsumerTokenRepository::getByPrefix( ConsumerTokenRepository::prefix( $token ) );
		if ( ! $row ) {
			return null;
		}

		if ( ! hash_equals( (string) $row['token_hash'], ConsumerTokenRepository::hashToken( $token ) ) ) {
			return null;
		}

		if ( ! empty( $row['revoked_at'] ) ) {
			return new \WP_Error( 'consumer_token_revoked', 'Consumer token has been revoked.' );
		}

		if ( $this->isExpired( $row ) ) {
			return new \WP_Error( 'consumer_token_expired', 'Consumer token has expired.' );
		}

		ConsumerTokenRepository::touchLastUsed( (int) $row['id'] );

		return $row;
	}

	/**
	 * @param array $row
	 *
	 * @return bool
	 */
	private function isExpired( array $row ): bool {
		if ( empty( $row['expires_at'] ) ) {
			return false;
		}

		$expiresAt = strtotime( (string) $row['expires_at'] );

		return $expiresAt !== false && $expiresAt <= ( strtotime( current_time( 'mysql', true ) ) ?: time() );
	}
}

