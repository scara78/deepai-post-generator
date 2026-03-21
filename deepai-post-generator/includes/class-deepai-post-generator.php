<?php
/**
 * The core plugin class.
 *
 * Defines internationalization, admin hooks, and public hooks.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_Post_Generator {

	/**
	 * The loader that registers all hooks.
	 *
	 * @var DeepAI_Loader
	 */
	protected $loader;

	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * The plugin version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->version     = DEEPAI_PG_VERSION;
		$this->plugin_slug = 'deepai-post-generator';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_rest_hooks();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once DEEPAI_PG_PLUGIN_DIR . 'includes/class-deepai-loader.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'includes/class-deepai-i18n.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'includes/class-deepai-encryption.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'includes/api/class-deepai-api-base.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'includes/api/class-deepai-openai.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'includes/api/class-deepai-openrouter.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'includes/class-deepai-seo-linker.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'admin/class-deepai-admin.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'admin/class-deepai-settings.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'admin/class-deepai-generator.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'admin/class-deepai-prompts.php';
		require_once DEEPAI_PG_PLUGIN_DIR . 'includes/class-deepai-rest-api.php';

		$this->loader = new DeepAI_Loader();
	}

	/**
	 * Set the plugin locale for i18n.
	 */
	private function set_locale() {
		$i18n = new DeepAI_i18n();
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register admin hooks.
	 */
	private function define_admin_hooks() {
		$admin     = new DeepAI_Admin( $this->get_plugin_slug(), $this->get_version() );
		$settings  = new DeepAI_Settings( $this->get_plugin_slug(), $this->get_version() );
		$generator = new DeepAI_Generator( $this->get_plugin_slug(), $this->get_version() );
		$prompts   = new DeepAI_Prompts( $this->get_plugin_slug(), $this->get_version() );

		// Admin menu and pages.
		$this->loader->add_action( 'admin_menu', $admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		// Settings.
		$this->loader->add_action( 'admin_init', $settings, 'register_settings' );

		// Plugin action links.
		$this->loader->add_filter(
			'plugin_action_links_' . DEEPAI_PG_PLUGIN_BASENAME,
			$admin,
			'add_action_links'
		);
	}

	/**
	 * Register REST API hooks.
	 */
	private function define_rest_hooks() {
		$rest_api = new DeepAI_Rest_API( $this->get_plugin_slug(), $this->get_version() );
		$this->loader->add_action( 'rest_api_init', $rest_api, 'register_routes' );
	}

	/**
	 * Run the loader to execute all hooks.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get the plugin slug.
	 *
	 * @return string
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get the loader instance.
	 *
	 * @return DeepAI_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}
}
