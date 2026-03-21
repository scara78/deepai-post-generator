<?php
/**
 * Abstract base class for API providers.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class DeepAI_API_Base {

	/**
	 * API key.
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * API base URL.
	 *
	 * @var string
	 */
	protected $base_url;

	/**
	 * Default request timeout in seconds.
	 *
	 * @var int
	 */
	protected $timeout = 120;

	/**
	 * Constructor.
	 *
	 * @param string $api_key Decrypted API key.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Generate text content.
	 *
	 * @param string $prompt  The prompt to send.
	 * @param string $model   The model to use.
	 * @param array  $options Additional options.
	 * @return array|WP_Error Response array with 'content' and 'tokens_used' keys.
	 */
	abstract public function generate_text( $prompt, $model, $options = array() );

	/**
	 * Generate an image.
	 *
	 * @param string $prompt  Image description prompt.
	 * @param array  $options Image options (size, quality, etc.).
	 * @return array|WP_Error Response array with 'url' key.
	 */
	abstract public function generate_image( $prompt, $options = array() );

	/**
	 * List available models.
	 *
	 * @return array|WP_Error Array of model objects.
	 */
	abstract public function list_models();

	/**
	 * Test the API connection.
	 *
	 * @return bool|WP_Error True on success.
	 */
	abstract public function test_connection();

	/**
	 * Get the provider name.
	 *
	 * @return string
	 */
	abstract public function get_provider_name();

	/**
	 * Make an HTTP request to the API.
	 *
	 * @param string $endpoint API endpoint (relative to base_url).
	 * @param array  $body     Request body.
	 * @param string $method   HTTP method.
	 * @return array|WP_Error  Decoded response body.
	 */
	protected function request( $endpoint, $body = array(), $method = 'POST' ) {
		$url = trailingslashit( $this->base_url ) . ltrim( $endpoint, '/' );

		$args = array(
			'method'  => $method,
			'timeout' => $this->timeout,
			'headers' => $this->get_headers(),
		);

		if ( ! empty( $body ) && 'GET' !== $method ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( $code < 200 || $code >= 300 ) {
			$error_message = isset( $data['error']['message'] )
				? $data['error']['message']
				: sprintf(
					/* translators: %d: HTTP status code */
					__( 'API request failed with status code %d.', 'deepai-post-generator' ),
					$code
				);

			return new WP_Error(
				'deepai_api_error',
				$error_message,
				array( 'status' => $code, 'response' => $data )
			);
		}

		return $data;
	}

	/**
	 * Get request headers.
	 *
	 * @return array
	 */
	protected function get_headers() {
		return array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $this->api_key,
		);
	}

	/**
	 * Download an image from a URL and add it to the Media Library.
	 *
	 * @param string $image_url  Remote image URL.
	 * @param string $alt_text   Alt text for the image.
	 * @param int    $post_id    Optional post to attach to.
	 * @return int|WP_Error      Attachment ID on success.
	 */
	protected function upload_image_to_media( $image_url, $alt_text = '', $post_id = 0 ) {
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		// Download and sideload the image.
		$attachment_id = media_sideload_image( $image_url, $post_id, $alt_text, 'id' );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Set alt text.
		if ( ! empty( $alt_text ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt_text ) );
		}

		return $attachment_id;
	}
}
