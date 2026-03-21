<?php
/**
 * Prompts management page handler.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_Prompts {

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
	 * Constructor.
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @param string $version     Plugin version.
	 */
	public function __construct( $plugin_slug, $version ) {
		$this->plugin_slug = $plugin_slug;
		$this->version     = $version;
	}

	/**
	 * Get the list of available placeholder variables.
	 *
	 * @return array
	 */
	public static function get_placeholder_variables() {
		return array(
			'{topic}'      => __( 'The topic or subject of the article', 'deepai-post-generator' ),
			'{keywords}'   => __( 'Comma-separated keywords', 'deepai-post-generator' ),
			'{language}'   => __( 'Target language for the content', 'deepai-post-generator' ),
			'{tone}'       => __( 'Writing tone (professional, casual, etc.)', 'deepai-post-generator' ),
			'{word_count}' => __( 'Target word count', 'deepai-post-generator' ),
		);
	}

	/**
	 * Handle prompt save via admin POST (non-AJAX fallback).
	 */
	public static function handle_prompt_save() {
		if ( ! isset( $_POST['deepai_pg_prompt_nonce'] ) ||
			 ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['deepai_pg_prompt_nonce'] ) ), 'deepai_pg_save_prompt' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$prompts = get_option( 'deepai_pg_prompts', array() );

		$action = isset( $_POST['prompt_action'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt_action'] ) ) : '';

		if ( 'save' === $action ) {
			$slug = isset( $_POST['prompt_slug'] ) ? sanitize_key( wp_unslash( $_POST['prompt_slug'] ) ) : '';
			$name = isset( $_POST['prompt_name'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt_name'] ) ) : '';
			$text = isset( $_POST['prompt_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt_text'] ) ) : '';

			if ( ! empty( $slug ) && ! empty( $name ) && ! empty( $text ) ) {
				$prompts[ $slug ] = array(
					'name'   => $name,
					'prompt' => $text,
				);
				update_option( 'deepai_pg_prompts', $prompts );
			}
		} elseif ( 'delete' === $action ) {
			$slug = isset( $_POST['prompt_slug'] ) ? sanitize_key( wp_unslash( $_POST['prompt_slug'] ) ) : '';
			if ( ! empty( $slug ) && isset( $prompts[ $slug ] ) ) {
				unset( $prompts[ $slug ] );
				update_option( 'deepai_pg_prompts', $prompts );
			}
		}
	}
}

// Register the admin_init handler for prompt saves.
add_action( 'admin_init', array( 'DeepAI_Prompts', 'handle_prompt_save' ) );
