<?php
/**
 * REST API endpoints.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_Rest_API {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	private $namespace;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @param string $version     Plugin version.
	 */
	public function __construct( $plugin_slug, $version ) {
		$this->plugin_slug = $plugin_slug;
		$this->version     = $version;
		$this->namespace   = 'deepai/v1';
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/generate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'generate_content' ),
			'permission_callback' => array( $this, 'check_permissions' ),
			'args'                => $this->get_generate_args(),
		) );

		register_rest_route( $this->namespace, '/regenerate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'regenerate_content' ),
			'permission_callback' => array( $this, 'check_permissions' ),
			'args'                => $this->get_regenerate_args(),
		) );

		register_rest_route( $this->namespace, '/generate-image', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'generate_image' ),
			'permission_callback' => array( $this, 'check_permissions' ),
			'args'                => array(
				'prompt'  => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'post_id' => array(
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
					'default'           => 0,
				),
			),
		) );

		register_rest_route( $this->namespace, '/test-api', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'test_api_connection' ),
			'permission_callback' => array( $this, 'check_permissions' ),
			'args'                => array(
				'provider' => array(
					'required'          => true,
					'type'              => 'string',
					'enum'              => array( 'openai', 'openrouter' ),
					'sanitize_callback' => 'sanitize_text_field',
				),
				'api_key'  => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		) );

		register_rest_route( $this->namespace, '/models', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_models' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );

		register_rest_route( $this->namespace, '/analyze-links', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'analyze_links' ),
			'permission_callback' => array( $this, 'check_permissions' ),
			'args'                => array(
				'post_id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		) );

		register_rest_route( $this->namespace, '/save-post', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'save_generated_post' ),
			'permission_callback' => array( $this, 'check_permissions' ),
			'args'                => $this->get_save_post_args(),
		) );
	}

	/**
	 * Permission callback — only admins/editors can use the generator.
	 *
	 * @param WP_REST_Request $request The request.
	 * @return bool|WP_Error
	 */
	public function check_permissions( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'deepai_forbidden',
				__( 'You do not have permission to use the content generator.', 'deepai-post-generator' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * Generate content endpoint.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function generate_content( $request ) {
		$settings = get_option( 'deepai_pg_settings', array() );
		$prompts  = get_option( 'deepai_pg_prompts', array() );

		$topic       = $request->get_param( 'topic' );
		$keywords    = $request->get_param( 'keywords' );
		$tone        = $request->get_param( 'tone' );
		$word_count  = $request->get_param( 'word_count' );
		$language    = $request->get_param( 'language' );
		$prompt_type = $request->get_param( 'prompt_type' );
		$model       = $request->get_param( 'model' );

		// Get the provider and API key.
		$api = $this->get_api_instance( $settings );
		if ( is_wp_error( $api ) ) {
			return $api;
		}

		// Build the prompt.
		$prompt_template = isset( $prompts[ $prompt_type ]['prompt'] )
			? $prompts[ $prompt_type ]['prompt']
			: $prompts['article']['prompt'];

		$language_name = $this->get_language_name( $language ?: $settings['ai_language'] ?? 'en' );

		$prompt = str_replace(
			array( '{topic}', '{keywords}', '{tone}', '{word_count}', '{language}' ),
			array(
				$topic,
				is_array( $keywords ) ? implode( ', ', $keywords ) : $keywords,
				$tone ?: ( $settings['default_tone'] ?? 'professional' ),
				$word_count ?: ( $settings['default_word_count'] ?? 1500 ),
				$language_name,
			),
			$prompt_template
		);

		// Add external link instructions if enabled.
		if ( ! empty( $settings['enable_external_links'] ) && 'yes' === $settings['enable_external_links'] ) {
			$seo_linker = new DeepAI_SEO_Linker();
			$prompt    .= $seo_linker->get_external_link_prompt_addition();
		}

		// Use specified model or default.
		$model_to_use = $model ?: ( $settings['default_model'] ?? 'gpt-4o-mini' );

		$result = $api->generate_text( $prompt, $model_to_use );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Add internal links if enabled.
		if ( ! empty( $settings['enable_internal_links'] ) && 'yes' === $settings['enable_internal_links'] ) {
			$seo_linker       = new DeepAI_SEO_Linker();
			$keywords_array   = is_array( $keywords ) ? $keywords : explode( ',', $keywords );
			$result['content'] = $seo_linker->add_internal_links(
				$result['content'],
				$topic,
				array_map( 'trim', $keywords_array )
			);
		}

		// Log generation.
		$this->log_generation( 0, $prompt, $result['model'], $result['provider'], $result['tokens_used'] );

		return rest_ensure_response( array(
			'success' => true,
			'data'    => array(
				'content'     => $result['content'],
				'tokens_used' => $result['tokens_used'],
				'model'       => $result['model'],
				'provider'    => $result['provider'],
			),
		) );
	}

	/**
	 * Regenerate content with modifications.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function regenerate_content( $request ) {
		$settings = get_option( 'deepai_pg_settings', array() );

		$content      = $request->get_param( 'content' );
		$instructions = $request->get_param( 'instructions' );
		$model        = $request->get_param( 'model' );

		$api = $this->get_api_instance( $settings );
		if ( is_wp_error( $api ) ) {
			return $api;
		}

		$prompt = sprintf(
			"Here is an existing article:\n\n%s\n\n"
			. "Please rewrite and improve this article with the following changes:\n%s\n\n"
			. 'Keep the same general structure and topic. Output valid HTML.',
			$content,
			$instructions
		);

		$model_to_use = $model ?: ( $settings['default_model'] ?? 'gpt-4o-mini' );
		$result       = $api->generate_text( $prompt, $model_to_use );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$this->log_generation( 0, $prompt, $result['model'], $result['provider'], $result['tokens_used'], 'regenerated' );

		return rest_ensure_response( array(
			'success' => true,
			'data'    => array(
				'content'     => $result['content'],
				'tokens_used' => $result['tokens_used'],
				'model'       => $result['model'],
				'provider'    => $result['provider'],
			),
		) );
	}

	/**
	 * Generate image endpoint.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function generate_image( $request ) {
		$settings = get_option( 'deepai_pg_settings', array() );

		$prompt  = $request->get_param( 'prompt' );
		$post_id = $request->get_param( 'post_id' );

		$api = $this->get_api_instance( $settings );
		if ( is_wp_error( $api ) ) {
			return $api;
		}

		$options = array(
			'size' => $settings['image_size'] ?? '1024x1024',
		);

		$result = $api->generate_image( $prompt, $options );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Upload to media library.
		$attachment_id = $api->upload_image_to_media( $result['url'], $prompt, $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		return rest_ensure_response( array(
			'success' => true,
			'data'    => array(
				'attachment_id'  => $attachment_id,
				'attachment_url' => wp_get_attachment_url( $attachment_id ),
			),
		) );
	}

	/**
	 * Test API connection.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function test_api_connection( $request ) {
		$provider = $request->get_param( 'provider' );
		$api_key  = $request->get_param( 'api_key' );

		if ( 'openai' === $provider ) {
			$api = new DeepAI_OpenAI( $api_key );
		} else {
			$api = new DeepAI_OpenRouter( $api_key );
		}

		$result = $api->test_connection();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'API connection successful!', 'deepai-post-generator' ),
		) );
	}

	/**
	 * Get available models.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_models( $request ) {
		$settings = get_option( 'deepai_pg_settings', array() );

		$api = $this->get_api_instance( $settings );
		if ( is_wp_error( $api ) ) {
			return $api;
		}

		$models = $api->list_models();

		if ( is_wp_error( $models ) ) {
			return $models;
		}

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $models,
		) );
	}

	/**
	 * Analyze post for linking opportunities.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function analyze_links( $request ) {
		$post_id = $request->get_param( 'post_id' );

		$linker      = new DeepAI_SEO_Linker();
		$suggestions = $linker->analyze_post_links( $post_id );

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $suggestions,
		) );
	}

	/**
	 * Save generated post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_generated_post( $request ) {
		$title      = $request->get_param( 'title' );
		$content    = $request->get_param( 'content' );
		$status     = $request->get_param( 'status' );
		$categories = $request->get_param( 'categories' );
		$tags       = $request->get_param( 'tags' );

		$post_data = array(
			'post_title'   => $title,
			'post_content' => wp_kses_post( $content ),
			'post_status'  => $status,
			'post_type'    => 'post',
			'post_author'  => get_current_user_id(),
		);

		if ( ! empty( $categories ) ) {
			$post_data['post_category'] = array_map( 'absint', $categories );
		}

		$post_id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Set tags.
		if ( ! empty( $tags ) ) {
			wp_set_post_tags( $post_id, array_map( 'sanitize_text_field', $tags ) );
		}

		// Mark post as AI-generated.
		update_post_meta( $post_id, '_deepai_pg_generated', '1' );
		update_post_meta( $post_id, '_deepai_pg_generated_at', current_time( 'mysql' ) );

		// Handle featured image generation.
		$settings = get_option( 'deepai_pg_settings', array() );
		if ( ! empty( $settings['generate_featured'] ) && 'yes' === $settings['generate_featured'] ) {
			$api = $this->get_api_instance( $settings );
			if ( ! is_wp_error( $api ) ) {
				$image_prompt = sprintf(
					/* translators: %s: post title */
					__( 'Create a professional, high-quality blog featured image for an article titled: %s', 'deepai-post-generator' ),
					$title
				);
				$image_result = $api->generate_image( $image_prompt, array(
					'size' => $settings['image_size'] ?? '1024x1024',
				) );

				if ( ! is_wp_error( $image_result ) ) {
					$attachment_id = $api->upload_image_to_media(
						$image_result['url'],
						$title,
						$post_id
					);
					if ( ! is_wp_error( $attachment_id ) ) {
						set_post_thumbnail( $post_id, $attachment_id );
					}
				}
			}
		}

		return rest_ensure_response( array(
			'success' => true,
			'data'    => array(
				'post_id'  => $post_id,
				'edit_url' => get_edit_post_link( $post_id, 'raw' ),
				'view_url' => get_permalink( $post_id ),
			),
		) );
	}

	/**
	 * Get the appropriate API instance based on settings.
	 *
	 * @param array $settings Plugin settings.
	 * @return DeepAI_API_Base|WP_Error
	 */
	private function get_api_instance( $settings ) {
		$provider = $settings['api_provider'] ?? 'openai';

		if ( 'openai' === $provider ) {
			$encrypted_key = $settings['openai_api_key'] ?? '';
			$api_key       = DeepAI_Encryption::decrypt( $encrypted_key );

			if ( empty( $api_key ) ) {
				return new WP_Error(
					'deepai_no_api_key',
					__( 'OpenAI API key is not configured. Please add it in Settings.', 'deepai-post-generator' ),
					array( 'status' => 400 )
				);
			}

			return new DeepAI_OpenAI( $api_key );
		}

		$encrypted_key = $settings['openrouter_api_key'] ?? '';
		$api_key       = DeepAI_Encryption::decrypt( $encrypted_key );

		if ( empty( $api_key ) ) {
			return new WP_Error(
				'deepai_no_api_key',
				__( 'OpenRouter API key is not configured. Please add it in Settings.', 'deepai-post-generator' ),
				array( 'status' => 400 )
			);
		}

		return new DeepAI_OpenRouter( $api_key );
	}

	/**
	 * Log a generation event to the history table.
	 *
	 * @param int    $post_id     Associated post ID.
	 * @param string $prompt      Prompt used.
	 * @param string $model       Model used.
	 * @param string $provider    Provider name.
	 * @param int    $tokens_used Tokens consumed.
	 * @param string $status      Generation status.
	 */
	private function log_generation( $post_id, $prompt, $model, $provider, $tokens_used, $status = 'completed' ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'deepai_pg_history';

		$wpdb->insert(
			$table_name,
			array(
				'post_id'     => $post_id > 0 ? $post_id : null,
				'prompt_used' => wp_kses_post( $prompt ),
				'model_used'  => sanitize_text_field( $model ),
				'provider'    => sanitize_text_field( $provider ),
				'tokens_used' => absint( $tokens_used ),
				'status'      => sanitize_text_field( $status ),
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
		);
	}

	/**
	 * Get generate endpoint arguments schema.
	 *
	 * @return array
	 */
	private function get_generate_args() {
		return array(
			'topic'       => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'keywords'    => array(
				'type'    => 'array',
				'items'   => array( 'type' => 'string' ),
				'default' => array(),
			),
			'tone'        => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'professional',
			),
			'word_count'  => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 1500,
			),
			'language'    => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'en',
			),
			'prompt_type' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'article',
			),
			'model'       => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			),
		);
	}

	/**
	 * Get regenerate endpoint arguments schema.
	 *
	 * @return array
	 */
	private function get_regenerate_args() {
		return array(
			'content'      => array(
				'required' => true,
				'type'     => 'string',
			),
			'instructions' => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			),
			'model'        => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			),
		);
	}

	/**
	 * Get save post endpoint arguments schema.
	 *
	 * @return array
	 */
	private function get_save_post_args() {
		return array(
			'title'      => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'content'    => array(
				'required' => true,
				'type'     => 'string',
			),
			'status'     => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'draft',
				'enum'              => array( 'draft', 'publish', 'pending' ),
			),
			'categories' => array(
				'type'    => 'array',
				'items'   => array( 'type' => 'integer' ),
				'default' => array(),
			),
			'tags'       => array(
				'type'    => 'array',
				'items'   => array( 'type' => 'string' ),
				'default' => array(),
			),
		);
	}

	/**
	 * Get human-readable language name from code.
	 *
	 * @param string $code Language code.
	 * @return string Language name.
	 */
	private function get_language_name( $code ) {
		$languages = array(
			'en'    => 'English',
			'es'    => 'Spanish',
			'pt'    => 'Portuguese',
			'fr'    => 'French',
			'de'    => 'German',
			'it'    => 'Italian',
			'zh'    => 'Chinese',
			'ja'    => 'Japanese',
			'ko'    => 'Korean',
			'ar'    => 'Arabic',
			'ru'    => 'Russian',
			'hi'    => 'Hindi',
			'nl'    => 'Dutch',
			'tr'    => 'Turkish',
			'pl'    => 'Polish',
			'sv'    => 'Swedish',
		);

		return $languages[ $code ] ?? 'English';
	}
}
