<?php
/**
 * Prompts management page template.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$prompts      = get_option( 'deepai_pg_prompts', array() );
$placeholders = DeepAI_Prompts::get_placeholder_variables();
?>
<div class="wrap deepai-wrap">
	<div class="deepai-header">
		<h1>
			<span class="dashicons dashicons-edit-large"></span>
			<?php esc_html_e( 'Prompt Templates', 'deepai-post-generator' ); ?>
		</h1>
		<span class="deepai-version"><?php echo esc_html( 'v' . DEEPAI_PG_VERSION ); ?></span>
	</div>

	<?php settings_errors(); ?>

	<div class="deepai-prompts-layout">
		<!-- Left: Prompt list -->
		<div class="deepai-prompts-list">
			<div class="deepai-card">
				<div class="deepai-card-header">
					<span class="dashicons dashicons-list-view"></span>
					<h2><?php esc_html_e( 'Templates', 'deepai-post-generator' ); ?></h2>
				</div>
				<div class="deepai-card-body">
					<?php if ( ! empty( $prompts ) ) : ?>
						<ul class="deepai-prompt-items">
							<?php foreach ( $prompts as $slug => $prompt ) : ?>
								<li class="deepai-prompt-item" data-slug="<?php echo esc_attr( $slug ); ?>">
									<span class="deepai-prompt-name"><?php echo esc_html( $prompt['name'] ); ?></span>
									<span class="deepai-prompt-slug"><?php echo esc_html( $slug ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="deepai-no-data"><?php esc_html_e( 'No prompt templates yet.', 'deepai-post-generator' ); ?></p>
					<?php endif; ?>

					<button type="button" id="deepai-add-prompt" class="button deepai-btn-secondary deepai-btn-full">
						<span class="dashicons dashicons-plus-alt"></span>
						<?php esc_html_e( 'Add New Template', 'deepai-post-generator' ); ?>
					</button>
				</div>
			</div>

			<!-- Available placeholders -->
			<div class="deepai-card">
				<div class="deepai-card-header">
					<span class="dashicons dashicons-info-outline"></span>
					<h2><?php esc_html_e( 'Available Placeholders', 'deepai-post-generator' ); ?></h2>
				</div>
				<div class="deepai-card-body">
					<table class="deepai-placeholders-table">
						<tbody>
							<?php foreach ( $placeholders as $var => $desc ) : ?>
								<tr>
									<td><code><?php echo esc_html( $var ); ?></code></td>
									<td><?php echo esc_html( $desc ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Right: Prompt editor -->
		<div class="deepai-prompts-editor">
			<div class="deepai-card">
				<div class="deepai-card-header">
					<span class="dashicons dashicons-edit"></span>
					<h2 id="deepai-editor-title"><?php esc_html_e( 'Select a Template', 'deepai-post-generator' ); ?></h2>
				</div>
				<div class="deepai-card-body">
					<form method="post" id="deepai-prompt-form">
						<?php wp_nonce_field( 'deepai_pg_save_prompt', 'deepai_pg_prompt_nonce' ); ?>
						<input type="hidden" name="prompt_action" value="save" />
						<input type="hidden" name="prompt_slug" id="deepai-prompt-slug" value="" />

						<div class="deepai-field">
							<label for="deepai-prompt-name"><?php esc_html_e( 'Template Name', 'deepai-post-generator' ); ?></label>
							<input type="text" name="prompt_name" id="deepai-prompt-name" class="deepai-input" placeholder="<?php esc_attr_e( 'e.g., Blog Article', 'deepai-post-generator' ); ?>" />
						</div>

						<div class="deepai-field">
							<label for="deepai-prompt-text"><?php esc_html_e( 'Prompt Template', 'deepai-post-generator' ); ?></label>
							<textarea name="prompt_text" id="deepai-prompt-text" class="deepai-textarea" rows="12" placeholder="<?php esc_attr_e( 'Write your prompt template here. Use placeholders like {topic}, {keywords}, etc.', 'deepai-post-generator' ); ?>"></textarea>
						</div>

						<div class="deepai-field deepai-actions">
							<button type="submit" class="button button-primary deepai-btn-primary">
								<span class="dashicons dashicons-saved"></span>
								<?php esc_html_e( 'Save Template', 'deepai-post-generator' ); ?>
							</button>
							<button type="button" id="deepai-delete-prompt" class="button deepai-btn-danger" style="display:none;">
								<span class="dashicons dashicons-trash"></span>
								<?php esc_html_e( 'Delete', 'deepai-post-generator' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
(function($) {
	'use strict';

	var prompts = <?php echo wp_json_encode( $prompts ); ?>;

	// Select a prompt from the list.
	$('.deepai-prompt-item').on('click', function() {
		var slug = $(this).data('slug');
		var data = prompts[slug];

		$('.deepai-prompt-item').removeClass('active');
		$(this).addClass('active');

		$('#deepai-prompt-slug').val(slug);
		$('#deepai-prompt-name').val(data.name);
		$('#deepai-prompt-text').val(data.prompt);
		$('#deepai-editor-title').text(data.name);
		$('#deepai-delete-prompt').show();
	});

	// Add new prompt.
	$('#deepai-add-prompt').on('click', function() {
		$('.deepai-prompt-item').removeClass('active');
		$('#deepai-prompt-slug').val('');
		$('#deepai-prompt-name').val('');
		$('#deepai-prompt-text').val('');
		$('#deepai-editor-title').text('<?php echo esc_js( __( 'New Template', 'deepai-post-generator' ) ); ?>');
		$('#deepai-delete-prompt').hide();
	});

	// Auto-generate slug.
	$('#deepai-prompt-name').on('blur', function() {
		if (!$('#deepai-prompt-slug').val()) {
			var slug = $(this).val()
				.toLowerCase()
				.replace(/[^a-z0-9]+/g, '_')
				.replace(/^_|_$/g, '');
			$('#deepai-prompt-slug').val(slug);
		}
	});

	// Delete prompt.
	$('#deepai-delete-prompt').on('click', function() {
		if (confirm(deepaiPG.strings.confirmDelete)) {
			$('input[name="prompt_action"]').val('delete');
			$('#deepai-prompt-form').submit();
		}
	});
})(jQuery);
</script>
