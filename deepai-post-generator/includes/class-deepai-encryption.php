<?php
/**
 * Encryption helper for API keys.
 *
 * Uses WordPress AUTH_KEY and AUTH_SALT for symmetric encryption.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_Encryption {

	/**
	 * Get the encryption key derived from WordPress salts.
	 *
	 * @return string
	 */
	private static function get_key() {
		$salt = defined( 'AUTH_KEY' ) && AUTH_KEY ? AUTH_KEY : 'deepai-pg-fallback-key';
		return hash( 'sha256', $salt, true );
	}

	/**
	 * Get the IV derived from WordPress salts.
	 *
	 * @return string
	 */
	private static function get_iv() {
		$salt   = defined( 'AUTH_SALT' ) && AUTH_SALT ? AUTH_SALT : 'deepai-pg-fallback-iv';
		$iv_len = openssl_cipher_iv_length( 'aes-256-cbc' );
		return substr( hash( 'sha256', $salt, true ), 0, $iv_len );
	}

	/**
	 * Encrypt a value.
	 *
	 * @param string $value Plain text value.
	 * @return string Base64-encoded encrypted value.
	 */
	public static function encrypt( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return base64_encode( $value );
		}

		$encrypted = openssl_encrypt(
			$value,
			'aes-256-cbc',
			self::get_key(),
			0,
			self::get_iv()
		);

		return $encrypted ? $encrypted : '';
	}

	/**
	 * Decrypt a value.
	 *
	 * @param string $value Base64-encoded encrypted value.
	 * @return string Decrypted plain text value.
	 */
	public static function decrypt( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return base64_decode( $value );
		}

		$decrypted = openssl_decrypt(
			$value,
			'aes-256-cbc',
			self::get_key(),
			0,
			self::get_iv()
		);

		return $decrypted ? $decrypted : '';
	}
}
