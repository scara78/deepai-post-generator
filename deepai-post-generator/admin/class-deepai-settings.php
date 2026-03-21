<?php
/**
 * Settings management.
 *
 * Registers and handles all plugin settings.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_Settings {

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
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name = 'deepai_pg_settings';

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
	 * Register all settings.
	 */
	public function register_settings() {
		register_setting(
			'deepai_pg_settings_group',
			$this->option_name,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		// API Configuration Section.
		add_settings_section(
			'deepai_pg_api_section',
			__( 'API Configuration', 'deepai-post-generator' ),
			array( $this, 'render_api_section' ),
			$this->plugin_slug . '-settings'
		);

		add_settings_field(
			'api_provider',
			__( 'API Provider', 'deepai-post-generator' ),
			array( $this, 'render_provider_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_api_section'
		);

		add_settings_field(
			'openai_api_key',
			__( 'OpenAI API Key', 'deepai-post-generator' ),
			array( $this, 'render_api_key_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_api_section',
			array( 'provider' => 'openai' )
		);

		add_settings_field(
			'openrouter_api_key',
			__( 'OpenRouter API Key', 'deepai-post-generator' ),
			array( $this, 'render_api_key_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_api_section',
			array( 'provider' => 'openrouter' )
		);

		add_settings_field(
			'default_model',
			__( 'Default Model', 'deepai-post-generator' ),
			array( $this, 'render_model_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_api_section'
		);

		// Content Generation Section.
		add_settings_section(
			'deepai_pg_content_section',
			__( 'Content Generation', 'deepai-post-generator' ),
			array( $this, 'render_content_section' ),
			$this->plugin_slug . '-settings'
		);

		add_settings_field(
			'ai_language',
			__( 'Default AI Language', 'deepai-post-generator' ),
			array( $this, 'render_language_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_content_section'
		);

		add_settings_field(
			'default_tone',
			__( 'Default Tone', 'deepai-post-generator' ),
			array( $this, 'render_tone_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_content_section'
		);

		add_settings_field(
			'default_word_count',
			__( 'Default Word Count', 'deepai-post-generator' ),
			array( $this, 'render_word_count_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_content_section'
		);

		add_settings_field(
			'default_post_status',
			__( 'Default Post Status', 'deepai-post-generator' ),
			array( $this, 'render_post_status_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_content_section'
		);

		// Image Generation Section.
		add_settings_section(
			'deepai_pg_image_section',
			__( 'Image Generation', 'deepai-post-generator' ),
			array( $this, 'render_image_section' ),
			$this->plugin_slug . '-settings'
		);

		add_settings_field(
			'generate_images',
			__( 'Generate In-Content Images', 'deepai-post-generator' ),
			array( $this, 'render_toggle_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_image_section',
			array( 'field' => 'generate_images' )
		);

		add_settings_field(
			'generate_featured',
			__( 'Generate Featured Image', 'deepai-post-generator' ),
			array( $this, 'render_toggle_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_image_section',
			array( 'field' => 'generate_featured' )
		);

		add_settings_field(
			'image_size',
			__( 'Image Size', 'deepai-post-generator' ),
			array( $this, 'render_image_size_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_image_section'
		);

		// SEO & Linking Section.
		add_settings_section(
			'deepai_pg_seo_section',
			__( 'SEO & Linking', 'deepai-post-generator' ),
			array( $this, 'render_seo_section' ),
			$this->plugin_slug . '-settings'
		);

		add_settings_field(
			'enable_internal_links',
			__( 'Enable Internal Linking', 'deepai-post-generator' ),
			array( $this, 'render_toggle_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_seo_section',
			array( 'field' => 'enable_internal_links' )
		);

		add_settings_field(
			'max_internal_links',
			__( 'Max Internal Links', 'deepai-post-generator' ),
			array( $this, 'render_number_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_seo_section',
			array( 'field' => 'max_internal_links', 'min' => 1, 'max' => 10 )
		);

		add_settings_field(
			'enable_external_links',
			__( 'Enable External Linking', 'deepai-post-generator' ),
			array( $this, 'render_toggle_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_seo_section',
			array( 'field' => 'enable_external_links' )
		);

		add_settings_field(
			'max_external_links',
			__( 'Max External Links', 'deepai-post-generator' ),
			array( $this, 'render_number_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_seo_section',
			array( 'field' => 'max_external_links', 'min' => 1, 'max' => 10 )
		);

		add_settings_field(
			'external_link_rel',
			__( 'External Link Rel Attribute', 'deepai-post-generator' ),
			array( $this, 'render_rel_field' ),
			$this->plugin_slug . '-settings',
			'deepai_pg_seo_section'
		);
	}

	/**
	 * Sanitize all settings.
	 *
	 * @param array $input Raw input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();
		$old       = get_option( $this->option_name, array() );

		// API Provider.
		$sanitized['api_provider'] = isset( $input['api_provider'] ) && in_array( $input['api_provider'], array( 'openai', 'openrouter' ), true )
			? $input['api_provider']
			: 'openai';

		// API keys — encrypt before storing.
		if ( ! empty( $input['openai_api_key'] ) ) {
			$sanitized['openai_api_key'] = DeepAI_Encryption::encrypt( sanitize_text_field( $input['openai_api_key'] ) );
		} else {
			$sanitized['openai_api_key'] = $old['openai_api_key'] ?? '';
		}

		if ( ! empty( $input['openrouter_api_key'] ) ) {
			$sanitized['openrouter_api_key'] = DeepAI_Encryption::encrypt( sanitize_text_field( $input['openrouter_api_key'] ) );
		} else {
			$sanitized['openrouter_api_key'] = $old['openrouter_api_key'] ?? '';
		}

		// Model.
		$sanitized['default_model'] = sanitize_text_field( $input['default_model'] ?? 'gpt-4o-mini' );

		// Language.
		$valid_languages = array( 'en', 'es', 'pt', 'fr', 'de', 'it', 'zh', 'ja', 'ko', 'ar', 'ru', 'hi', 'nl', 'tr', 'pl', 'sv' );
		$sanitized['ai_language'] = isset( $input['ai_language'] ) && in_array( $input['ai_language'], $valid_languages, true )
			? $input['ai_language']
			: 'en';

		// Tone.
		$valid_tones = array( 'professional', 'casual', 'academic', 'conversational', 'humorous', 'formal', 'friendly' );
		$sanitized['default_tone'] = isset( $input['default_tone'] ) && in_array( $input['default_tone'], $valid_tones, true )
			? $input['default_tone']
			: 'professional';

		// Word count.
		$sanitized['default_word_count'] = isset( $input['default_word_count'] )
			? max( 100, min( 10000, absint( $input['default_word_count'] ) ) )
			: 1500;

		// Post status.
		$valid_statuses = array( 'draft', 'publish', 'pending' );
		$sanitized['default_post_status'] = isset( $input['default_post_status'] ) && in_array( $input['default_post_status'], $valid_statuses, true )
			? $input['default_post_status']
			: 'draft';

		// Image settings.
		$sanitized['generate_images']  = isset( $input['generate_images'] ) && 'yes' === $input['generate_images'] ? 'yes' : 'no';
		$sanitized['generate_featured'] = isset( $input['generate_featured'] ) && 'yes' === $input['generate_featured'] ? 'yes' : 'no';

		$valid_sizes = array( '1024x1024', '1792x1024', '1024x1792', '512x512', '256x256' );
		$sanitized['image_size'] = isset( $input['image_size'] ) && in_array( $input['image_size'], $valid_sizes, true )
			? $input['image_size']
			: '1024x1024';

		// SEO settings.
		$sanitized['enable_internal_links'] = isset( $input['enable_internal_links'] ) && 'yes' === $input['enable_internal_links'] ? 'yes' : 'no';
		$sanitized['enable_external_links'] = isset( $input['enable_external_links'] ) && 'yes' === $input['enable_external_links'] ? 'yes' : 'no';
		$sanitized['max_internal_links']    = isset( $input['max_internal_links'] ) ? max( 1, min( 10, absint( $input['max_internal_links'] ) ) ) : 3;
		$sanitized['max_external_links']    = isset( $input['max_external_links'] ) ? max( 1, min( 10, absint( $input['max_external_links'] ) ) ) : 2;

		$valid_rels = array( 'nofollow', 'dofollow', 'nofollow noopener', 'noopener noreferrer' );
		$sanitized['external_link_rel'] = isset( $input['external_link_rel'] ) && in_array( $input['external_link_rel'], $valid_rels, true )
			? $input['external_link_rel']
			: 'nofollow';

		// Clear model cache when provider changes.
		if ( ( $old['api_provider'] ?? '' ) !== $sanitized['api_provider'] ) {
			delete_transient( 'deepai_pg_openai_models' );
			delete_transient( 'deepai_pg_openrouter_models' );
		}

		return $sanitized;
	}

	// -------------------------------------------------------------------------
	// Section renderers.
	// -------------------------------------------------------------------------

	public function render_api_section() {
		echo '<p>' . esc_html__( 'Configure your AI provider and API keys.', 'deepai-post-generator' ) . '</p>';
	}

	public function render_content_section() {
		echo '<p>' . esc_html__( 'Set default content generation preferences.', 'deepai-post-generator' ) . '</p>';
	}

	public function render_image_section() {
		echo '<p>' . esc_html__( 'Configure AI image generation options.', 'deepai-post-generator' ) . '</p>';
	}

	public function render_seo_section() {
		echo '<p>' . esc_html__( 'Configure internal and external linking for SEO.', 'deepai-post-generator' ) . '</p>';
	}

	// -------------------------------------------------------------------------
	// Field renderers.
	// -------------------------------------------------------------------------

	public function render_provider_field() {
		$settings = get_option( $this->option_name, array() );
		$value    = $settings['api_provider'] ?? 'openai';
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[api_provider]" id="deepai-api-provider" class="deepai-select">
			<option value="openai" <?php selected( $value, 'openai' ); ?>>OpenAI</option>
			<option value="openrouter" <?php selected( $value, 'openrouter' ); ?>>OpenRouter</option>
		</select>
		<p class="description"><?php esc_html_e( 'Choose your primary AI provider.', 'deepai-post-generator' ); ?></p>
		<?php
	}

	public function render_api_key_field( $args ) {
		$settings = get_option( $this->option_name, array() );
		$provider = $args['provider'];
		$key_name = $provider . '_api_key';
		$has_key  = ! empty( $settings[ $key_name ] );
		?>
		<div class="deepai-api-key-field" data-provider="<?php echo esc_attr( $provider ); ?>">
			<input
				type="password"
				name="<?php echo esc_attr( $this->option_name . '[' . $key_name . ']' ); ?>"
				id="deepai-<?php echo esc_attr( $key_name ); ?>"
				class="regular-text deepai-input"
				placeholder="<?php echo $has_key ? esc_attr__( '••••••••••••••••••••••••', 'deepai-post-generator' ) : esc_attr__( 'Enter your API key', 'deepai-post-generator' ); ?>"
				autocomplete="off"
			/>
			<button type="button" class="button deepai-toggle-key" data-target="deepai-<?php echo esc_attr( $key_name ); ?>">
				<span class="dashicons dashicons-visibility"></span>
			</button>
			<button type="button" class="button deepai-test-api" data-provider="<?php echo esc_attr( $provider ); ?>">
				<?php esc_html_e( 'Test Connection', 'deepai-post-generator' ); ?>
			</button>
			<?php if ( $has_key ) : ?>
				<span class="deepai-key-status deepai-key-saved">✓ <?php esc_html_e( 'Key saved', 'deepai-post-generator' ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}

	public function render_model_field() {
		$settings = get_option( $this->option_name, array() );
		$value    = $settings['default_model'] ?? 'gpt-4o-mini';
		?>
		<input
			type="text"
			name="<?php echo esc_attr( $this->option_name ); ?>[default_model]"
			id="deepai-default-model"
			class="regular-text deepai-input"
			value="<?php echo esc_attr( $value ); ?>"
		/>
		<button type="button" class="button deepai-load-models" id="deepai-load-models">
			<?php esc_html_e( 'Load Available Models', 'deepai-post-generator' ); ?>
		</button>
		<div id="deepai-models-list" class="deepai-models-list" style="display:none;"></div>
		<p class="description"><?php esc_html_e( 'Enter the model ID or click "Load Available Models" to see options.', 'deepai-post-generator' ); ?></p>
		<?php
	}

	public function render_language_field() {
		$settings  = get_option( $this->option_name, array() );
		$value     = $settings['ai_language'] ?? 'en';
		$languages = array(
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
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[ai_language]" class="deepai-select">
			<?php foreach ( $languages as $code => $name ) : ?>
				<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $value, $code ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'The language in which the AI will generate content.', 'deepai-post-generator' ); ?></p>
		<?php
	}

	public function render_tone_field() {
		$settings = get_option( $this->option_name, array() );
		$value    = $settings['default_tone'] ?? 'professional';
		$tones    = array(
			'professional'   => __( 'Professional', 'deepai-post-generator' ),
			'casual'         => __( 'Casual', 'deepai-post-generator' ),
			'academic'       => __( 'Academic', 'deepai-post-generator' ),
			'conversational' => __( 'Conversational', 'deepai-post-generator' ),
			'humorous'       => __( 'Humorous', 'deepai-post-generator' ),
			'formal'         => __( 'Formal', 'deepai-post-generator' ),
			'friendly'       => __( 'Friendly', 'deepai-post-generator' ),
		);
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[default_tone]" class="deepai-select">
			<?php foreach ( $tones as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public function render_word_count_field() {
		$settings = get_option( $this->option_name, array() );
		$value    = $settings['default_word_count'] ?? 1500;
		?>
		<input
			type="number"
			name="<?php echo esc_attr( $this->option_name ); ?>[default_word_count]"
			value="<?php echo esc_attr( $value ); ?>"
			min="100"
			max="10000"
			step="100"
			class="small-text deepai-input"
		/>
		<p class="description"><?php esc_html_e( 'Target word count for generated articles (100-10,000).', 'deepai-post-generator' ); ?></p>
		<?php
	}

	public function render_post_status_field() {
		$settings = get_option( $this->option_name, array() );
		$value    = $settings['default_post_status'] ?? 'draft';
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[default_post_status]" class="deepai-select">
			<option value="draft" <?php selected( $value, 'draft' ); ?>><?php esc_html_e( 'Draft', 'deepai-post-generator' ); ?></option>
			<option value="pending" <?php selected( $value, 'pending' ); ?>><?php esc_html_e( 'Pending Review', 'deepai-post-generator' ); ?></option>
			<option value="publish" <?php selected( $value, 'publish' ); ?>><?php esc_html_e( 'Published', 'deepai-post-generator' ); ?></option>
		</select>
		<?php
	}

	public function render_toggle_field( $args ) {
		$settings = get_option( $this->option_name, array() );
		$field    = $args['field'];
		$value    = $settings[ $field ] ?? 'no';
		?>
		<label class="deepai-toggle">
			<input
				type="checkbox"
				name="<?php echo esc_attr( $this->option_name . '[' . $field . ']' ); ?>"
				value="yes"
				<?php checked( $value, 'yes' ); ?>
			/>
			<span class="deepai-toggle-slider"></span>
		</label>
		<?php
	}

	public function render_image_size_field() {
		$settings = get_option( $this->option_name, array() );
		$value    = $settings['image_size'] ?? '1024x1024';
		$sizes    = array(
			'1024x1024' => '1024×1024 (Square)',
			'1792x1024' => '1792×1024 (Landscape)',
			'1024x1792' => '1024×1792 (Portrait)',
		);
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[image_size]" class="deepai-select">
			<?php foreach ( $sizes as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public function render_number_field( $args ) {
		$settings = get_option( $this->option_name, array() );
		$field    = $args['field'];
		$value    = $settings[ $field ] ?? 3;
		$min      = $args['min'] ?? 1;
		$max      = $args['max'] ?? 10;
		?>
		<input
			type="number"
			name="<?php echo esc_attr( $this->option_name . '[' . $field . ']' ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			min="<?php echo esc_attr( $min ); ?>"
			max="<?php echo esc_attr( $max ); ?>"
			class="small-text deepai-input"
		/>
		<?php
	}

	public function render_rel_field() {
		$settings = get_option( $this->option_name, array() );
		$value    = $settings['external_link_rel'] ?? 'nofollow';
		$options  = array(
			'nofollow'            => 'nofollow',
			'dofollow'            => 'dofollow (no rel attribute)',
			'nofollow noopener'   => 'nofollow noopener',
			'noopener noreferrer' => 'noopener noreferrer',
		);
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[external_link_rel]" class="deepai-select">
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}
}
