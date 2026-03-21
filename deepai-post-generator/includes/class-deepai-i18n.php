<?php
/**
 * Internationalization handler.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_i18n {

	/**
	 * Load the plugin text domain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'deepai-post-generator',
			false,
			dirname( DEEPAI_PG_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
