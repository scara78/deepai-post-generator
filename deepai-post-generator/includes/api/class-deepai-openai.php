<?php
/**
 * OpenAI API integration.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_OpenAI extends DeepAI_API_Base {

	/**
	 * Constructor.
	 *
	 * @param string $api_key Decrypted API key.
	 */
	public function __construct( $api_key ) {
		parent::__construct( $api_key );
		$this->base_url = 'https://api.openai.com/v1';
	}

	/**
	 * Get provider name.
	 *
	 * @return string
	 */
	public function get_provider_name() {
		return 'openai';
	}

	/**
	 * Generate text content.
	 *
	 * @param string $prompt  The prompt.
	 * @param string $model   Model identifier.
	 * @param array  $options Additional options.
	 * @return array|WP_Error
	 */
	public function generate_text( $prompt, $model = 'gpt-4o-mini', $options = array() ) {
		$defaults = array(
			'temperature'    => 0.7,
			'max_tokens'     => 4096,
			'system_message' => 'You are a professional content writer. Write well-structured, engaging, and SEO-friendly content. Always output valid HTML markup for formatting.',
		);
		$options = wp_parse_args( $options, $defaults );

		// Legacy models use max_tokens; newer models use max_completion_tokens.
		$legacy_models = array( 'gpt-3.5-turbo', 'gpt-3.5-turbo-16k', 'gpt-4', 'gpt-4-32k' );
		$token_param   = ( in_array( $model, $legacy_models, true ) || strncmp( $model, 'gpt-3', 5 ) === 0 ) ? 'max_tokens' : 'max_completion_tokens';

		// Reasoning / o-series models and gpt-5 variants only accept temperature = 1.
		$no_temperature_models = array( 'o1', 'o1-mini', 'o1-preview', 'o3', 'o3-mini', 'o4-mini' );
		$is_reasoning = in_array( $model, $no_temperature_models, true )
			|| strncmp( $model, 'o1-', 3 ) === 0
			|| strncmp( $model, 'o3-', 3 ) === 0
			|| strncmp( $model, 'o4-', 3 ) === 0
			|| strncmp( $model, 'gpt-5', 5 ) === 0;

		$body = array(
			'model'        => $model,
			'messages'     => array(
				array(
					'role'    => 'system',
					'content' => $options['system_message'],
				),
				array(
					'role'    => 'user',
					'content' => $prompt,
				),
			),
			$token_param   => (int) $options['max_tokens'],
		);

		// Only add temperature for models that support it.
		if ( ! $is_reasoning ) {
			$body['temperature'] = (float) $options['temperature'];
		}

		$response = $this->request( 'chat/completions', $body );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['choices'][0]['message']['content'] ) ) {
			return new WP_Error(
				'deepai_invalid_response',
				__( 'Invalid response from OpenAI API.', 'deepai-post-generator' )
			);
		}

		return array(
			'content'     => $response['choices'][0]['message']['content'],
			'tokens_used' => isset( $response['usage']['total_tokens'] ) ? (int) $response['usage']['total_tokens'] : 0,
			'model'       => $model,
			'provider'    => 'openai',
		);
	}

	/**
	 * Generate an image using DALL-E.
	 *
	 * @param string $prompt  Image description.
	 * @param array  $options Image options.
	 * @return array|WP_Error
	 */
	public function generate_image( $prompt, $options = array() ) {
		$defaults = array(
			'model'   => 'dall-e-3',
			'size'    => '1024x1024',
			'quality' => 'standard',
			'n'       => 1,
		);
		$options = wp_parse_args( $options, $defaults );

		$body = array(
			'model'   => $options['model'],
			'prompt'  => $prompt,
			'size'    => $options['size'],
			'quality' => $options['quality'],
			'n'       => (int) $options['n'],
		);

		$response = $this->request( 'images/generations', $body );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['data'][0]['url'] ) ) {
			return new WP_Error(
				'deepai_invalid_image_response',
				__( 'Invalid image response from OpenAI API.', 'deepai-post-generator' )
			);
		}

		return array(
			'url'      => $response['data'][0]['url'],
			'provider' => 'openai',
		);
	}

	/**
	 * List available models.
	 *
	 * @return array|WP_Error
	 */
	public function list_models() {
		$cached = get_transient( 'deepai_pg_openai_models' );
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
				// Only include chat models.
				if ( isset( $model['id'] ) && ( strpos( $model['id'], 'gpt' ) !== false ) ) {
					$models[] = array(
						'id'   => $model['id'],
						'name' => $model['id'],
					);
				}
			}
		}

		// Sort alphabetically.
		usort( $models, function( $a, $b ) {
			return strcmp( $a['id'], $b['id'] );
		});

		set_transient( 'deepai_pg_openai_models', $models, HOUR_IN_SECONDS );

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
