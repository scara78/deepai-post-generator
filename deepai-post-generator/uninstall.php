<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove plugin options.
$options_to_delete = array(
	'deepai_pg_settings',
	'deepai_pg_prompts',
	'deepai_pg_seo_settings',
	'deepai_pg_version',
);

foreach ( $options_to_delete as $option ) {
	delete_option( $option );
}

// Remove generation history table.
global $wpdb;
$table_name = $wpdb->prefix . 'deepai_pg_history';
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table_name ) );

// Remove post meta created by this plugin.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
		'_deepai_pg_%'
	)
);

// Clear any scheduled cron events.
wp_clear_scheduled_hook( 'deepai_pg_scheduled_generation' );
