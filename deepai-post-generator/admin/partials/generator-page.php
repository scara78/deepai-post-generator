<?php
/**
 * Content generator page template.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings  = get_option( 'deepai_pg_settings', array() );
$prompts   = get_option( 'deepai_pg_prompts', array() );
$languages = DeepAI_Generator::get_languages();
$tones     = DeepAI_Generator::get_tones();

$categories = get_categories( array( 'hide_empty' => false ) );
?>
<div class="wrap deepai-wrap">
	<div class="deepai-header">
		<h1>
			<span class="dashicons dashicons-edit-large"></span>
			<?php esc_html_e( 'DeepAI Post Generator', 'deepai-post-generator' ); ?>
		</h1>
		<span class="deepai-version"><?php echo esc_html( 'v' . DEEPAI_PG_VERSION ); ?></span>
	</div>

	<!-- Toast notification container -->
	<div id="deepai-toast-container" class="deepai-toast-container"></div>

	<div class="deepai-generator-layout">
		<!-- Left Panel: Configuration -->
		<div class="deepai-generator-sidebar">
			<div class="deepai-card">
				<div class="deepai-card-header">
					<span class="dashicons dashicons-admin-settings"></span>
					<h2><?php esc_html_e( 'Content Settings', 'deepai-post-generator' ); ?></h2>
				</div>
				<div class="deepai-card-body">
					<!-- Topic -->
					<div class="deepai-field">
						<label for="deepai-topic"><?php esc_html_e( 'Topic / Title', 'deepai-post-generator' ); ?> <span class="required">*</span></label>
						<input type="text" id="deepai-topic" class="deepai-input" placeholder="<?php esc_attr_e( 'e.g., 10 Best Practices for Remote Work', 'deepai-post-generator' ); ?>" required />
					</div>

					<!-- Keywords -->
					<div class="deepai-field">
						<label for="deepai-keywords"><?php esc_html_e( 'Keywords', 'deepai-post-generator' ); ?></label>
						<input type="text" id="deepai-keywords" class="deepai-input" placeholder="<?php esc_attr_e( 'e.g., remote work, productivity, home office', 'deepai-post-generator' ); ?>" />
						<p class="description"><?php esc_html_e( 'Separate with commas.', 'deepai-post-generator' ); ?></p>
					</div>

					<!-- Prompt Template -->
					<div class="deepai-field">
						<label for="deepai-prompt-type"><?php esc_html_e( 'Article Type', 'deepai-post-generator' ); ?></label>
						<select id="deepai-prompt-type" class="deepai-select">
							<?php foreach ( $prompts as $slug => $prompt ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>">
									<?php echo esc_html( $prompt['name'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Language -->
					<div class="deepai-field">
						<label for="deepai-language"><?php esc_html_e( 'Language', 'deepai-post-generator' ); ?></label>
						<select id="deepai-language" class="deepai-select">
							<?php foreach ( $languages as $code => $name ) : ?>
								<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $settings['ai_language'] ?? 'en', $code ); ?>>
									<?php echo esc_html( $name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Tone -->
					<div class="deepai-field">
						<label for="deepai-tone"><?php esc_html_e( 'Tone', 'deepai-post-generator' ); ?></label>
						<select id="deepai-tone" class="deepai-select">
							<?php foreach ( $tones as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['default_tone'] ?? 'professional', $key ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Word Count -->
					<div class="deepai-field">
						<label for="deepai-word-count"><?php esc_html_e( 'Word Count', 'deepai-post-generator' ); ?></label>
						<input type="number" id="deepai-word-count" class="deepai-input" value="<?php echo esc_attr( $settings['default_word_count'] ?? 1500 ); ?>" min="100" max="10000" step="100" />
					</div>

					<!-- Model -->
					<div class="deepai-field">
						<label for="deepai-model"><?php esc_html_e( 'AI Model', 'deepai-post-generator' ); ?></label>
						<input type="text" id="deepai-model" class="deepai-input" value="<?php echo esc_attr( $settings['default_model'] ?? 'gpt-4o-mini' ); ?>" />
					</div>

					<!-- Category -->
					<div class="deepai-field">
						<label for="deepai-category"><?php esc_html_e( 'Category', 'deepai-post-generator' ); ?></label>
						<select id="deepai-category" class="deepai-select" multiple>
							<?php foreach ( $categories as $cat ) : ?>
								<option value="<?php echo esc_attr( $cat->term_id ); ?>">
									<?php echo esc_html( $cat->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Tags -->
					<div class="deepai-field">
						<label for="deepai-tags"><?php esc_html_e( 'Tags', 'deepai-post-generator' ); ?></label>
						<input type="text" id="deepai-tags" class="deepai-input" placeholder="<?php esc_attr_e( 'e.g., tech, AI, blogging', 'deepai-post-generator' ); ?>" />
						<p class="description"><?php esc_html_e( 'Separate with commas.', 'deepai-post-generator' ); ?></p>
					</div>

					<!-- Post Status -->
					<div class="deepai-field">
						<label for="deepai-post-status"><?php esc_html_e( 'Post Status', 'deepai-post-generator' ); ?></label>
						<select id="deepai-post-status" class="deepai-select">
							<option value="draft" <?php selected( $settings['default_post_status'] ?? 'draft', 'draft' ); ?>><?php esc_html_e( 'Draft', 'deepai-post-generator' ); ?></option>
							<option value="pending" <?php selected( $settings['default_post_status'] ?? 'draft', 'pending' ); ?>><?php esc_html_e( 'Pending Review', 'deepai-post-generator' ); ?></option>
							<option value="publish" <?php selected( $settings['default_post_status'] ?? 'draft', 'publish' ); ?>><?php esc_html_e( 'Published', 'deepai-post-generator' ); ?></option>
						</select>
					</div>

					<!-- Generate Button -->
					<div class="deepai-field deepai-actions">
						<button type="button" id="deepai-generate-btn" class="button button-primary deepai-btn-primary deepai-btn-large">
							<span class="dashicons dashicons-superhero-alt"></span>
							<?php esc_html_e( 'Generate Content', 'deepai-post-generator' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Right Panel: Generated Content Preview -->
		<div class="deepai-generator-content">
			<div class="deepai-card">
				<div class="deepai-card-header">
					<span class="dashicons dashicons-text-page"></span>
					<h2><?php esc_html_e( 'Generated Content', 'deepai-post-generator' ); ?></h2>
					<div class="deepai-card-actions">
						<span id="deepai-token-count" class="deepai-badge" style="display:none;"></span>
					</div>
				</div>
				<div class="deepai-card-body">
					<!-- Loading skeleton -->
					<div id="deepai-loading" class="deepai-loading" style="display:none;">
						<div class="deepai-spinner"></div>
						<p id="deepai-loading-text"><?php esc_html_e( 'Generating content...', 'deepai-post-generator' ); ?></p>
					</div>

					<!-- Empty state -->
					<div id="deepai-empty-state" class="deepai-empty-state">
						<span class="dashicons dashicons-edit-large"></span>
						<h3><?php esc_html_e( 'Ready to Generate', 'deepai-post-generator' ); ?></h3>
						<p><?php esc_html_e( 'Configure your content settings on the left and click "Generate Content" to start.', 'deepai-post-generator' ); ?></p>
					</div>

					<!-- Content preview -->
					<div id="deepai-content-preview" class="deepai-content-preview" style="display:none;">
						<div id="deepai-generated-content" class="deepai-generated-content"></div>
					</div>
				</div>

				<!-- Content actions -->
				<div id="deepai-content-actions" class="deepai-card-footer" style="display:none;">
					<div class="deepai-action-group">
						<button type="button" id="deepai-save-draft" class="button deepai-btn-secondary">
							<span class="dashicons dashicons-media-document"></span>
							<?php esc_html_e( 'Save as Draft', 'deepai-post-generator' ); ?>
						</button>
						<button type="button" id="deepai-publish" class="button button-primary deepai-btn-primary">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'Publish', 'deepai-post-generator' ); ?>
						</button>
					</div>
					<div class="deepai-action-group">
						<button type="button" id="deepai-regenerate-btn" class="button deepai-btn-outline">
							<span class="dashicons dashicons-image-rotate"></span>
							<?php esc_html_e( 'Regenerate', 'deepai-post-generator' ); ?>
						</button>
						<button type="button" id="deepai-generate-featured-btn" class="button deepai-btn-outline">
							<span class="dashicons dashicons-format-image"></span>
							<?php esc_html_e( 'Generate Featured Image', 'deepai-post-generator' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Regeneration modal -->
			<div id="deepai-regen-modal" class="deepai-modal" style="display:none;">
				<div class="deepai-modal-overlay"></div>
				<div class="deepai-modal-content">
					<div class="deepai-modal-header">
						<h3><?php esc_html_e( 'Regenerate Content', 'deepai-post-generator' ); ?></h3>
						<button type="button" class="deepai-modal-close">&times;</button>
					</div>
					<div class="deepai-modal-body">
						<div class="deepai-field">
							<label for="deepai-regen-instructions"><?php esc_html_e( 'What changes would you like?', 'deepai-post-generator' ); ?></label>
							<textarea id="deepai-regen-instructions" class="deepai-textarea" rows="4" placeholder="<?php esc_attr_e( 'e.g., Make it more casual, add more examples, shorten the introduction...', 'deepai-post-generator' ); ?>"></textarea>
						</div>
					</div>
					<div class="deepai-modal-footer">
						<button type="button" class="button deepai-modal-cancel"><?php esc_html_e( 'Cancel', 'deepai-post-generator' ); ?></button>
						<button type="button" id="deepai-confirm-regen" class="button button-primary deepai-btn-primary"><?php esc_html_e( 'Regenerate', 'deepai-post-generator' ); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
