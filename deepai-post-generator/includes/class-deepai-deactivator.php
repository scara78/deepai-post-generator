<?php
/**
 * Plugin deactivator.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_Deactivator {

	/**
	 * Run deactivation routines.
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'deepai_pg_scheduled_generation' );
		flush_rewrite_rules();
	}
}
