<?php
/**
 * Settings page template.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = get_option( 'deepai_pg_settings', array() );
?>
<div class="wrap deepai-wrap">
	<div class="deepai-header">
		<h1>
			<span class="dashicons dashicons-edit-large"></span>
			<?php esc_html_e( 'DeepAI Post Generator — Settings', 'deepai-post-generator' ); ?>
		</h1>
		<span class="deepai-version"><?php echo esc_html( 'v' . DEEPAI_PG_VERSION ); ?></span>
	</div>

	<?php settings_errors(); ?>

	<form method="post" action="options.php" class="deepai-settings-form">
		<?php
		settings_fields( 'deepai_pg_settings_group' );
		?>

		<div class="deepai-settings-grid">
			<!-- API Configuration Card -->
			<div class="deepai-card">
				<div class="deepai-card-header">
					<span class="dashicons dashicons-admin-network"></span>
					<h2><?php esc_html_e( 'API Configuration', 'deepai-post-generator' ); ?></h2>
				</div>
				<div class="deepai-card-body">
					<?php do_settings_fields( 'deepai-post-generator-settings', 'deepai_pg_api_section' ); ?>
				</div>
			</div>

			<!-- Content Generation Card -->
			<div class="deepai-card">
				<div class="deepai-card-header">
					<span class="dashicons dashicons-editor-paste-text"></span>
					<h2><?php esc_html_e( 'Content Generation', 'deepai-post-generator' ); ?></h2>
				</div>
				<div class="deepai-card-body">
					<?php do_settings_fields( 'deepai-post-generator-settings', 'deepai_pg_content_section' ); ?>
				</div>
			</div>

			<!-- Image Generation Card -->
			<div class="deepai-card">
				<div class="deepai-card-header">
					<span class="dashicons dashicons-format-image"></span>
					<h2><?php esc_html_e( 'Image Generation', 'deepai-post-generator' ); ?></h2>
				</div>
				<div class="deepai-card-body">
					<?php do_settings_fields( 'deepai-post-generator-settings', 'deepai_pg_image_section' ); ?>
				</div>
			</div>

			<!-- SEO & Linking Card -->
			<div class="deepai-card">
				<div class="deepai-card-header">
					<span class="dashicons dashicons-admin-links"></span>
					<h2><?php esc_html_e( 'SEO & Linking', 'deepai-post-generator' ); ?></h2>
				</div>
				<div class="deepai-card-body">
					<?php do_settings_fields( 'deepai-post-generator-settings', 'deepai_pg_seo_section' ); ?>
				</div>
			</div>
		</div>

		<div class="deepai-settings-footer">
			<?php submit_button( __( 'Save Settings', 'deepai-post-generator' ), 'primary deepai-btn-primary', 'submit', false ); ?>
		</div>
	</form>
</div>
