<?php
/**
 * Repository for SPLAT consumer tokens.
 *
 * @package SPLAT\Auth
 */

namespace SPLAT\Auth;

defined( 'ABSPATH' ) || exit;

use SPLAT\DB;

final class ConsumerTokenRepository {

	public const TABLE                = 'splat_consumer_tokens';
	private const TOKEN_PREFIX_LENGTH = 40;

	/**
	 * @param array $data
	 *
	 * @return array{id: int, token: string, prefix: string}|\WP_Error
	 */
	public static function create( array $data ): array|\WP_Error {
		$token  = self::generateToken();
		$prefix = self::prefix( $token );

		$id = DB::insertOneRow(
			self::TABLE,
			[
				'name'                      => sanitize_text_field( $data['name'] ?? 'Consumer token' ),
				'token_hash'                => self::hashToken( $token ),
				'token_prefix'              => $prefix,
				'allowed_routes_json'       => self::encodeList( $data['allowed_routes'] ?? ( $data['allowed_routes_json'] ?? null ) ),
				'allowed_capabilities_json' => self::encodeList( $data['allowed_capabilities'] ?? ( $data['allowed_capabilities_json'] ?? null ) ),
				'allowed_providers_json'    => self::encodeList( $data['allowed_providers'] ?? ( $data['allowed_providers_json'] ?? null ) ),
				'allowed_models_json'       => self::encodeList( $data['allowed_models'] ?? ( $data['allowed_models_json'] ?? null ) ),
				'internal_only'             => absint( $data['internal_only'] ?? 0 ),
				'daily_token_limit'         => self::nullableInt( $data['daily_token_limit'] ?? null ),
				'monthly_token_limit'       => self::nullableInt( $data['monthly_token_limit'] ?? null ),
				'expires_at'                => self::nullableDateTime( $data['expires_at'] ?? null ),
			]
		);

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return [
			'id'     => $id,
			'token'  => $token,
			'prefix' => $prefix,
		];
	}

	/**
	 * @param int $id
	 *
	 * @return array|null
	 */
	public static function getById( int $id ): ?array {
		return DB::getOne( self::TABLE, 'id = %d', [ $id ] );
	}

	/**
	 * @param string $prefix
	 *
	 * @return array|null
	 */
	public static function getByPrefix( string $prefix ): ?array {
		return DB::getOne( self::TABLE, 'token_prefix = %s', [ sanitize_text_field( $prefix ) ] );
	}

	/**
	 * @return array<int, array>
	 */
	public static function getAllMasked(): array {
		$rows = DB::getRows( self::TABLE, [], 1, 200, 'id', 'DESC' );
		if ( is_wp_error( $rows ) ) {
			return [];
		}

		foreach ( $rows as &$row ) {
			unset( $row['token_hash'] );
		}

		return $rows;
	}

	/**
	 * @param int $id
	 *
	 * @return int|\WP_Error
	 */
	public static function revoke( int $id ): int|\WP_Error {
		return DB::updateOneRow( self::TABLE, $id, [ 'revoked_at' => current_time( 'mysql', true ) ] );
	}

	/**
	 * @param int $id
	 *
	 * @return int|\WP_Error
	 */
	public static function touchLastUsed( int $id ): int|\WP_Error {
		return DB::updateOneRow( self::TABLE, $id, [ 'last_used_at' => current_time( 'mysql', true ) ] );
	}

	/**
	 * @param int $id
	 *
	 * @return int|\WP_Error
	 */
	public static function delete( int $id ): int|\WP_Error {
		return DB::deleteOneRow( self::TABLE, $id );
	}

	/**
	 * @param string $token
	 *
	 * @return string
	 */
	public static function hashToken( string $token ): string {
		return hash_hmac( 'sha256', $token, wp_salt( 'auth' ) );
	}

	/**
	 * @param string $token
	 *
	 * @return string
	 */
	public static function prefix( string $token ): string {
		return substr( $token, 0, self::TOKEN_PREFIX_LENGTH );
	}

	/**
	 * @return string
	 */
	private static function generateToken(): string {
		return 'splat_' . bin2hex( random_bytes( 32 ) );
	}

	/**
	 * @param mixed $value
	 *
	 * @return string|null
	 */
	private static function encodeList( mixed $value ): ?string {
		if ( $value === null || '' === $value ) {
			return null;
		}

		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true );
			if ( ! is_array( $decoded ) ) {
				$value = array_filter( array_map( 'trim', explode( ',', $value ) ) );
			} else {
				$value = $decoded;
			}
		}

		if ( ! is_array( $value ) ) {
			return null;
		}

		$value = array_values(
			array_filter(
				array_map( static fn( mixed $item ): string => sanitize_text_field( (string) $item ), $value )
			)
		);

		return $value ? wp_json_encode( $value ) : null;
	}

	/**
	 * @param mixed $value
	 *
	 * @return int|null
	 */
	private static function nullableInt( mixed $value ): ?int {
		if ( $value === null || $value === '' ) {
			return null;
		}

		return max( 0, absint( $value ) );
	}

	/**
	 * @param mixed $value
	 *
	 * @return string|null
	 */
	private static function nullableDateTime( mixed $value ): ?string {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return null;
		}

		$timestamp = strtotime( $value );
		if ( false === $timestamp ) {
			return null;
		}

		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}
}

