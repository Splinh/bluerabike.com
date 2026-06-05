<?php
/**
 * AES-256-CBC encryption for API keys.
 *
 * @package SPLAT\Crypto
 */

namespace SPLAT;

defined( 'ABSPATH' ) || exit;

final class KeyEncryptor {

	private const CIPHER = 'aes-256-cbc';

	/**
	 * Encrypt a plaintext API key.
	 */
	public static function encrypt( string $value ): string {
		if ( '' === $value ) {
			return '';
		}

		$key       = hash( 'sha256', wp_salt( 'auth' ), true );
		$ivLength  = openssl_cipher_iv_length( self::CIPHER );
		$iv        = openssl_random_pseudo_bytes( $ivLength );
		$encrypted = openssl_encrypt( $value, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $encrypted ) {
			return '';
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $iv . $encrypted );
	}

	/**
	 * Decrypt an encrypted API key.
	 */
	public static function decrypt( string $value ): string {
		if ( '' === $value ) {
			return '';
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$decoded = base64_decode( $value, true );
		if ( false === $decoded ) {
			return $value;
		}

		$key      = hash( 'sha256', wp_salt( 'auth' ), true );
		$ivLength = openssl_cipher_iv_length( self::CIPHER );

		if ( strlen( $decoded ) < $ivLength ) {
			return $value;
		}

		$iv         = substr( $decoded, 0, $ivLength );
		$ciphertext = substr( $decoded, $ivLength );
		$decrypted  = openssl_decrypt( $ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $decrypted ) {
			return $value;
		}

		return $decrypted;
	}

	/**
	 * Mask an API key for display: ****abcd
	 */
	public static function mask( string $plainKey ): string {
		if ( strlen( $plainKey ) <= 4 ) {
			return '****';
		}

		return '****' . substr( $plainKey, -4 );
	}
}

