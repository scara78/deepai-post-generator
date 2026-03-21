<?php
/**
 * DeepAI Post Generator
 *
 * @package           DeepAI_Post_Generator
 * @author            DeepAI
 * @copyright         2026 DeepAI
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       DeepAI Post Generator
 * Plugin URI:        https://github.com/deepai/deepai-post-generator
 * Description:       AI-powered content generator for WordPress. Create high-quality blog posts and articles using OpenAI and OpenRouter APIs with automatic SEO optimization and internal linking.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            DeepAI
 * Author URI:        https://github.com/deepai
 * Text Domain:       deepai-post-generator
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Abort if this file is called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 */
define( 'SUSPENDED_POST_GENERATOR_VERSION', '1.0.0' );

/**
 * Plugin constants.
 */
define( 'DEEPAI_PG_VERSION', '1.0.0' );
define( 'DEEPAI_PG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DEEPAI_PG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DEEPAI_PG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'DEEPAI_PG_PLUGIN_FILE', __FILE__ );
define( 'DEEPAI_PG_MIN_WP_VERSION', '5.8' );
define( 'DEEPAI_PG_MIN_PHP_VERSION', '7.4' );

/**
 * Activation hook.
 */
function deepai_pg_activate() {
	require_once DEEPAI_PG_PLUGIN_DIR . 'includes/class-deepai-activator.php';
	DeepAI_Activator::activate();
}
register_activation_hook( __FILE__, 'deepai_pg_activate' );

/**
 * Deactivation hook.
 */
function deepai_pg_deactivate() {
	require_once DEEPAI_PG_PLUGIN_DIR . 'includes/class-deepai-deactivator.php';
	DeepAI_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deepai_pg_deactivate' );

/**
 * Load the core plugin class.
 */
require_once DEEPAI_PG_PLUGIN_DIR . 'includes/class-deepai-post-generator.php';

/**
 * Begin plugin execution.
 *
 * @since 1.0.0
 * @return DeepAI_Post_Generator
 */
function deepai_pg() {
	static $instance = null;
	if ( null === $instance ) {
		$instance = new DeepAI_Post_Generator();
		$instance->run();
	}
	return $instance;
}
deepai_pg();
