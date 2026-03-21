<?php
/**
 * Content generator page handler.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_Generator {

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
	 * Get available languages for the generator UI.
	 *
	 * @return array
	 */
	public static function get_languages() {
		return array(
			'en' => __( 'English', 'deepai-post-generator' ),
			'es' => __( 'Spanish', 'deepai-post-generator' ),
			'pt' => __( 'Portuguese', 'deepai-post-generator' ),
			'fr' => __( 'French', 'deepai-post-generator' ),
			'de' => __( 'German', 'deepai-post-generator' ),
			'it' => __( 'Italian', 'deepai-post-generator' ),
			'zh' => __( 'Chinese', 'deepai-post-generator' ),
			'ja' => __( 'Japanese', 'deepai-post-generator' ),
			'ko' => __( 'Korean', 'deepai-post-generator' ),
			'ar' => __( 'Arabic', 'deepai-post-generator' ),
			'ru' => __( 'Russian', 'deepai-post-generator' ),
			'hi' => __( 'Hindi', 'deepai-post-generator' ),
			'nl' => __( 'Dutch', 'deepai-post-generator' ),
			'tr' => __( 'Turkish', 'deepai-post-generator' ),
			'pl' => __( 'Polish', 'deepai-post-generator' ),
			'sv' => __( 'Swedish', 'deepai-post-generator' ),
		);
	}

	/**
	 * Get available tones.
	 *
	 * @return array
	 */
	public static function get_tones() {
		return array(
			'professional'   => __( 'Professional', 'deepai-post-generator' ),
			'casual'         => __( 'Casual', 'deepai-post-generator' ),
			'academic'       => __( 'Academic', 'deepai-post-generator' ),
			'conversational' => __( 'Conversational', 'deepai-post-generator' ),
			'humorous'       => __( 'Humorous', 'deepai-post-generator' ),
			'formal'         => __( 'Formal', 'deepai-post-generator' ),
			'friendly'       => __( 'Friendly', 'deepai-post-generator' ),
		);
	}
}
