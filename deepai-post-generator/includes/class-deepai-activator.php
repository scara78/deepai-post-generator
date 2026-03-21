<?php
/**
 * Plugin activator.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_Activator {

	/**
	 * Run activation routines.
	 */
	public static function activate() {
		self::check_requirements();
		self::create_tables();
		self::set_default_options();
		update_option( 'deepai_pg_version', DEEPAI_PG_VERSION );
		flush_rewrite_rules();
	}

	/**
	 * Check minimum requirements.
	 */
	private static function check_requirements() {
		if ( version_compare( PHP_VERSION, DEEPAI_PG_MIN_PHP_VERSION, '<' ) ) {
			deactivate_plugins( DEEPAI_PG_PLUGIN_BASENAME );
			wp_die(
				sprintf(
					/* translators: %s: minimum PHP version */
					esc_html__( 'DeepAI Post Generator requires PHP %s or higher.', 'deepai-post-generator' ),
					esc_html( DEEPAI_PG_MIN_PHP_VERSION )
				),
				'Plugin Activation Error',
				array( 'back_link' => true )
			);
		}

		global $wp_version;
		if ( version_compare( $wp_version, DEEPAI_PG_MIN_WP_VERSION, '<' ) ) {
			deactivate_plugins( DEEPAI_PG_PLUGIN_BASENAME );
			wp_die(
				sprintf(
					/* translators: %s: minimum WordPress version */
					esc_html__( 'DeepAI Post Generator requires WordPress %s or higher.', 'deepai-post-generator' ),
					esc_html( DEEPAI_PG_MIN_WP_VERSION )
				),
				'Plugin Activation Error',
				array( 'back_link' => true )
			);
		}
	}

	/**
	 * Create custom database tables.
	 */
	private static function create_tables() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'deepai_pg_history';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED DEFAULT NULL,
			prompt_used TEXT NOT NULL,
			model_used VARCHAR(100) NOT NULL,
			provider VARCHAR(50) NOT NULL,
			tokens_used INT(11) UNSIGNED DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'completed',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Set default plugin options.
	 */
	private static function set_default_options() {
		$defaults = array(
			'api_provider'          => 'openai',
			'openai_api_key'        => '',
			'openrouter_api_key'    => '',
			'default_model'         => 'gpt-4o-mini',
			'ai_language'           => 'en',
			'generate_images'       => 'no',
			'generate_featured'     => 'no',
			'image_model'           => 'dall-e-3',
			'image_size'            => '1024x1024',
			'enable_internal_links' => 'no',
			'enable_external_links' => 'no',
			'max_internal_links'    => 3,
			'max_external_links'    => 2,
			'external_link_rel'     => 'nofollow',
			'default_post_status'   => 'draft',
			'default_tone'          => 'professional',
			'default_word_count'    => 1500,
		);

		if ( false === get_option( 'deepai_pg_settings' ) ) {
			add_option( 'deepai_pg_settings', $defaults );
		}

		$default_prompts = array(
			'article' => array(
				'name'   => 'Standard Article',
				'prompt' => "Write a comprehensive, well-structured article about {topic}.\n\nRequirements:\n- Language: {language}\n- Tone: {tone}\n- Approximate word count: {word_count}\n- Keywords to include: {keywords}\n- Use proper headings (H2, H3) to organize content\n- Include an engaging introduction and a clear conclusion\n- Write original, plagiarism-free content\n- Use short paragraphs for readability\n- Format output as valid HTML",
			),
			'listicle' => array(
				'name'   => 'Listicle',
				'prompt' => "Write a listicle article about {topic}.\n\nRequirements:\n- Language: {language}\n- Tone: {tone}\n- Approximate word count: {word_count}\n- Keywords to include: {keywords}\n- Format as a numbered list with detailed descriptions\n- Include an introduction and conclusion\n- Each item should have a heading and 2-3 paragraphs\n- Format output as valid HTML",
			),
			'how_to' => array(
				'name'   => 'How-To Guide',
				'prompt' => "Write a detailed how-to guide about {topic}.\n\nRequirements:\n- Language: {language}\n- Tone: {tone}\n- Approximate word count: {word_count}\n- Keywords to include: {keywords}\n- Include step-by-step instructions\n- Add tips and warnings where appropriate\n- Include a materials/prerequisites section if relevant\n- Format output as valid HTML",
			),
			'review' => array(
				'name'   => 'Review',
				'prompt' => "Write an in-depth review about {topic}.\n\nRequirements:\n- Language: {language}\n- Tone: {tone}\n- Approximate word count: {word_count}\n- Keywords to include: {keywords}\n- Include pros and cons\n- Provide a balanced, honest assessment\n- Include a rating or recommendation\n- Format output as valid HTML",
			),
		);

		if ( false === get_option( 'deepai_pg_prompts' ) ) {
			add_option( 'deepai_pg_prompts', $default_prompts );
		}
	}
}
