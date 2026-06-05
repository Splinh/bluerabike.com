<?php
/**
 * GitHub Updater.
 *
 * Uses YahnisElsts/plugin-update-checker for reliable updates.
 * Token is stored encrypted in wp_options under its own key.
 * Configure via the plugin's Settings tab, or define SPLAT_GITHUB_TOKEN
 * in wp-config.php as a fallback.
 *
 * @package SPLAT\Updater
 */

namespace SPLAT\Updater;

use YahnisElsts\PluginUpdateChecker\v5p6\PucFactory;
use YahnisElsts\PluginUpdateChecker\v5p6\Vcs\GitHubApi;

\defined( 'ABSPATH' ) || exit;

final class GitHubUpdater {

	/**
	 * Repository URL.
	 */
	private const REPO_URL = 'https://github.com/HD-Agency/hd-ai-toolkit';

	/**
	 * wp_options key for the encrypted token (owned by this plugin).
	 */
	public const TOKEN_OPTION = '_splat_github_token';

	/**
	 * Context string for Sodium key derivation.
	 * Must never change — altering this invalidates all stored tokens.
	 */
	private const CRYPTO_CONTEXT = 'splat_plugin_encryption_v1';

	// --------------------------------------------------

	public function __construct() {
		$this->initUpdateChecker();
	}

	// --------------------------------------------------

	private function initUpdateChecker(): void {
		try {
			$updateChecker = PucFactory::buildUpdateChecker(
				self::REPO_URL,
				SPLAT_PATH . 'splat.php',
				'hd-ai-toolkit'
			);

			$updateChecker->setBranch( 'main' );

			$vcsApi = $updateChecker->getVcsApi();
			if ( $vcsApi instanceof GitHubApi ) {
				$vcsApi->enableReleaseAssets();
			}

			$token = $this->getToken();
			if ( $token ) {
				$updateChecker->setAuthentication( $token );
			}
		} catch ( \Throwable ) {
			return;
		}
	}

	// --------------------------------------------------

	/**
	 * Get access token.
	 *
	 * Priority:
	 * 1. DB option (own encrypted token — managed via Settings tab).
	 * 2. SPLAT_GITHUB_TOKEN constant in wp-config.php.
	 *
	 * @return string|null
	 */
	private function getToken(): ?string {
		$stored = get_option( self::TOKEN_OPTION, '' );
		if ( ! empty( $stored ) ) {
			$decrypted = self::decryptToken( $stored );
			if ( '' !== $decrypted ) {
				return $decrypted;
			}
		}

		if ( defined( 'SPLAT_GITHUB_TOKEN' ) && \SPLAT_GITHUB_TOKEN ) {
			return \SPLAT_GITHUB_TOKEN;
		}

		return null;
	}

	// --------------------------------------------------
	// Crypto helpers (Sodium XChaCha20-Poly1305)
	// --------------------------------------------------

	/**
	 * Encrypt a plaintext token for storage in wp_options.
	 *
	 * @param string $value Plaintext token.
	 *
	 * @return string Encrypted (base64) or empty string on failure.
	 */
	public static function encryptToken( string $value ): string {
		if ( '' === $value ) {
			return '';
		}

		try {
			$key   = self::deriveKey();
			$nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

			$ciphertext = sodium_crypto_secretbox( $value, $nonce, $key );
			sodium_memzero( $key );

			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			return base64_encode( $nonce . $ciphertext );
		} catch ( \Throwable ) {
			return '';
		}
	}

	/**
	 * Decrypt an encrypted token from wp_options.
	 *
	 * @param string $stored Encrypted (base64) value from DB.
	 *
	 * @return string Plaintext or empty string on failure.
	 */
	public static function decryptToken( string $stored ): string {
		if ( '' === $stored ) {
			return '';
		}

		try {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$raw = base64_decode( $stored, true );
			if ( false === $raw ) {
				return '';
			}

			$minLen = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES;
			if ( strlen( $raw ) <= $minLen ) {
				return '';
			}

			$key        = self::deriveKey();
			$nonce      = substr( $raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$ciphertext = substr( $raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

			$plaintext = sodium_crypto_secretbox_open( $ciphertext, $nonce, $key );
			sodium_memzero( $key );

			return false !== $plaintext ? $plaintext : '';
		} catch ( \Throwable ) {
			return '';
		}
	}

	/**
	 * Check if token is configured (DB or constant).
	 */
	public static function hasToken(): bool {
		$stored = get_option( self::TOKEN_OPTION, '' );
		if ( ! empty( $stored ) && '' !== self::decryptToken( $stored ) ) {
			return true;
		}

		return defined( 'SPLAT_GITHUB_TOKEN' ) && \SPLAT_GITHUB_TOKEN;
	}

	// --------------------------------------------------

	/**
	 * Derive 32-byte key from WP security salts via BLAKE2b.
	 *
	 * @return string 32-byte raw key.
	 * @throws \SodiumException
	 */
	private static function deriveKey(): string {
		$salt = ( defined( 'SECURE_AUTH_KEY' ) ? \SECURE_AUTH_KEY : '' )
			. ( defined( 'AUTH_SALT' ) ? \AUTH_SALT : '' );

		return sodium_crypto_generichash(
			$salt,
			self::CRYPTO_CONTEXT,
			SODIUM_CRYPTO_SECRETBOX_KEYBYTES
		);
	}
}

