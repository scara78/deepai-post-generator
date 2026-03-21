<?php
/**
 * Admin functionality.
 *
 * Handles menu registration, asset enqueuing, and action links.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_Admin {

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
	 * Register admin menu pages.
	 */
	public function add_admin_menu() {
		// Main menu page.
		add_menu_page(
			__( 'DeepAI Post Generator', 'deepai-post-generator' ),
			__( 'DeepAI Writer', 'deepai-post-generator' ),
			'edit_posts',
			$this->plugin_slug,
			array( $this, 'render_generator_page' ),
			'dashicons-edit-large',
			30
		);

		// Generator sub-menu (same as main).
		add_submenu_page(
			$this->plugin_slug,
			__( 'Generate Content', 'deepai-post-generator' ),
			__( 'Generate', 'deepai-post-generator' ),
			'edit_posts',
			$this->plugin_slug,
			array( $this, 'render_generator_page' )
		);

		// Prompts sub-menu.
		add_submenu_page(
			$this->plugin_slug,
			__( 'Prompt Templates', 'deepai-post-generator' ),
			__( 'Prompts', 'deepai-post-generator' ),
			'manage_options',
			$this->plugin_slug . '-prompts',
			array( $this, 'render_prompts_page' )
		);

		// History sub-menu.
		add_submenu_page(
			$this->plugin_slug,
			__( 'Generation History', 'deepai-post-generator' ),
			__( 'History', 'deepai-post-generator' ),
			'edit_posts',
			$this->plugin_slug . '-history',
			array( $this, 'render_history_page' )
		);

		// Settings sub-menu.
		add_submenu_page(
			$this->plugin_slug,
			__( 'Settings', 'deepai-post-generator' ),
			__( 'Settings', 'deepai-post-generator' ),
			'manage_options',
			$this->plugin_slug . '-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin styles.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( ! $this->is_plugin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_slug . '-admin',
			DEEPAI_PG_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$this->version
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( ! $this->is_plugin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_slug . '-admin',
			DEEPAI_PG_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-api-fetch' ),
			$this->version,
			true
		);

		wp_localize_script( $this->plugin_slug . '-admin', 'deepaiPG', array(
			'restUrl'   => rest_url( 'deepai/v1/' ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'pluginUrl' => DEEPAI_PG_PLUGIN_URL,
			'strings'   => array(
				'generating'    => __( 'Generating content...', 'deepai-post-generator' ),
				'regenerating'  => __( 'Regenerating content...', 'deepai-post-generator' ),
				'saving'        => __( 'Saving post...', 'deepai-post-generator' ),
				'success'       => __( 'Content generated successfully!', 'deepai-post-generator' ),
				'saved'         => __( 'Post saved successfully!', 'deepai-post-generator' ),
				'error'         => __( 'An error occurred. Please try again.', 'deepai-post-generator' ),
				'confirmDelete' => __( 'Are you sure you want to delete this prompt?', 'deepai-post-generator' ),
				'testSuccess'   => __( 'API connection successful!', 'deepai-post-generator' ),
				'testFail'      => __( 'API connection failed.', 'deepai-post-generator' ),
				'noContent'     => __( 'No content generated yet.', 'deepai-post-generator' ),
				'imageGen'      => __( 'Generating image...', 'deepai-post-generator' ),
				'imageDone'     => __( 'Image generated successfully!', 'deepai-post-generator' ),
			),
		) );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$plugin_links = array(
			sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=' . $this->plugin_slug . '-settings' ),
				__( 'Settings', 'deepai-post-generator' )
			),
			sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=' . $this->plugin_slug ),
				__( 'Generate', 'deepai-post-generator' )
			),
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Render the generator page.
	 */
	public function render_generator_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'deepai-post-generator' ) );
		}
		include DEEPAI_PG_PLUGIN_DIR . 'admin/partials/generator-page.php';
	}

	/**
	 * Render the prompts page.
	 */
	public function render_prompts_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'deepai-post-generator' ) );
		}
		include DEEPAI_PG_PLUGIN_DIR . 'admin/partials/prompts-page.php';
	}

	/**
	 * Render the history page.
	 */
	public function render_history_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'deepai-post-generator' ) );
		}
		include DEEPAI_PG_PLUGIN_DIR . 'admin/partials/history-page.php';
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'deepai-post-generator' ) );
		}
		include DEEPAI_PG_PLUGIN_DIR . 'admin/partials/settings-page.php';
	}

	/**
	 * Check if current admin page belongs to this plugin.
	 *
	 * @param string $hook_suffix Current page hook.
	 * @return bool
	 */
	private function is_plugin_page( $hook_suffix ) {
		$plugin_pages = array(
			'toplevel_page_' . $this->plugin_slug,
			'deepai-writer_page_' . $this->plugin_slug . '-settings',
			'deepai-writer_page_' . $this->plugin_slug . '-prompts',
			'deepai-writer_page_' . $this->plugin_slug . '-history',
		);

		return in_array( $hook_suffix, $plugin_pages, true );
	}
}
