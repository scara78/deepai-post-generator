<?php
/**
 * OpenRouter API integration.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_OpenRouter extends DeepAI_API_Base {

	/**
	 * Constructor.
	 *
	 * @param string $api_key Decrypted API key.
	 */
	public function __construct( $api_key ) {
		parent::__construct( $api_key );
		$this->base_url = 'https://openrouter.ai/api/v1';
	}

	/**
	 * Get provider name.
	 *
	 * @return string
	 */
	public function get_provider_name() {
		return 'openrouter';
	}

	/**
	 * Get request headers with OpenRouter specific additions.
	 *
	 * @return array
	 */
	protected function get_headers() {
		$headers = parent::get_headers();
		$headers['HTTP-Referer'] = home_url();
		$headers['X-Title']      = get_bloginfo( 'name' );
		return $headers;
	}

	/**
	 * Generate text content.
	 *
	 * @param string $prompt  The prompt.
	 * @param string $model   Model identifier.
	 * @param array  $options Additional options.
	 * @return array|WP_Error
	 */
	public function generate_text( $prompt, $model = 'openai/gpt-4o-mini', $options = array() ) {
		$defaults = array(
			'temperature'    => 0.7,
			'max_tokens'     => 4096,
			'system_message' => 'You are a professional content writer. Write well-structured, engaging, and SEO-friendly content. Always output valid HTML markup for formatting.',
		);
		$options = wp_parse_args( $options, $defaults );

		$body = array(
			'model'       => $model,
			'messages'    => array(
				array(
					'role'    => 'system',
					'content' => $options['system_message'],
				),
				array(
					'role'    => 'user',
					'content' => $prompt,
				),
			),
			'temperature' => (float) $options['temperature'],
			'max_tokens'  => (int) $options['max_tokens'],
		);

		$response = $this->request( 'chat/completions', $body );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['choices'][0]['message']['content'] ) ) {
			return new WP_Error(
				'deepai_invalid_response',
				__( 'Invalid response from OpenRouter API.', 'deepai-post-generator' )
			);
		}

		return array(
			'content'     => $response['choices'][0]['message']['content'],
			'tokens_used' => isset( $response['usage']['total_tokens'] ) ? (int) $response['usage']['total_tokens'] : 0,
			'model'       => $model,
			'provider'    => 'openrouter',
		);
	}

	/**
	 * Generate an image via OpenRouter.
	 *
	 * @param string $prompt  Image description.
	 * @param array  $options Image options.
	 * @return array|WP_Error
	 */
	public function generate_image( $prompt, $options = array() ) {
		$defaults = array(
			'model' => 'openai/dall-e-3',
			'size'  => '1024x1024',
		);
		$options = wp_parse_args( $options, $defaults );

		// OpenRouter uses the same images endpoint format.
		$body = array(
			'model'  => $options['model'],
			'prompt' => $prompt,
			'size'   => $options['size'],
			'n'      => 1,
		);

		$response = $this->request( 'images/generations', $body );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['data'][0]['url'] ) ) {
			return new WP_Error(
				'deepai_invalid_image_response',
				__( 'Invalid image response from OpenRouter API.', 'deepai-post-generator' )
			);
		}

		return array(
			'url'      => $response['data'][0]['url'],
			'provider' => 'openrouter',
		);
	}

	/**
	 * List available models.
	 *
	 * @return array|WP_Error
	 */
	public function list_models() {
		$cached = get_transient( 'deepai_pg_openrouter_models' );
		if ( false !== $cached ) {
			return $cached;
		}

		$response = $this->request( 'models', array(), 'GET' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$models = array();
		if ( isset( $response['data'] ) && is_array( $response['data'] ) ) {
			foreach ( $response['data'] as $model ) {
				if ( isset( $model['id'] ) ) {
					$models[] = array(
						'id'   => $model['id'],
						'name' => isset( $model['name'] ) ? $model['name'] : $model['id'],
					);
				}
			}
		}

		usort( $models, function( $a, $b ) {
			return strcmp( $a['name'], $b['name'] );
		});

		set_transient( 'deepai_pg_openrouter_models', $models, HOUR_IN_SECONDS );

		return $models;
	}

	/**
	 * Test API connection.
	 *
	 * @return bool|WP_Error
	 */
	public function test_connection() {
		$response = $this->request( 'models', array(), 'GET' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return true;
	}
}
