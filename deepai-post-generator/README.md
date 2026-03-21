# DeepAI Post Generator

![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue?logo=wordpress)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![License](https://img.shields.io/badge/License-GPLv2-green)
![Version](https://img.shields.io/badge/Version-1.0.0-orange)

**The most complete AI-powered content generator for WordPress.** Connect to OpenAI or OpenRouter and create professional blog posts, articles, and images directly from your dashboard.

---

## Features

- **Dual API Support** — OpenAI (GPT-4, GPT-3.5) + OpenRouter (100+ models)
- **Smart Content Generation** — Full articles with headings, paragraphs, lists, and formatting
- **AI Image Generation** — Featured images and in-content images via DALL-E
- **Prompt Template System** — Reusable templates with dynamic placeholders
- **SEO Internal Linking** — Auto-inserts contextual links to existing posts
- **External Link Suggestions** — AI-recommended authoritative external links
- **16 Languages** — English, Spanish, Portuguese, French, German, Italian, Chinese, Japanese, Korean, Arabic, Russian, Hindi, and more
- **7 Writing Tones** — Professional, Casual, Academic, Conversational, Humorous, Formal, Friendly
- **Content Regeneration** — Refine content with specific instructions
- **Generation History** — Full log with tokens, model, and status
- **Encrypted API Keys** — AES-256-CBC encryption using WordPress salts
- **Modern Admin UI** — Beautiful, responsive card-based interface

## Requirements

| Requirement | Minimum |
|-------------|---------|
| WordPress   | 5.8     |
| PHP         | 7.4     |
| OpenSSL     | Recommended (for encryption) |

## Installation

### From ZIP

1. Download the latest release from the [Releases](../../releases) page.
2. In WordPress, go to **Plugins > Add New > Upload Plugin**.
3. Upload the ZIP file and click **Install Now**.
4. Activate the plugin.

### Manual

1. Clone this repository into your `wp-content/plugins/` directory:
   ```bash
   git clone https://github.com/your-username/deepai-post-generator.git wp-content/plugins/deepai-post-generator
   ```
2. Activate via **Plugins** in WordPress admin.

## Configuration

1. Go to **DeepAI Writer > Settings**.
2. Enter your API key:
   - **OpenAI**: Get a key at [platform.openai.com/api-keys](https://platform.openai.com/api-keys)
   - **OpenRouter**: Get a key at [openrouter.ai/keys](https://openrouter.ai/keys)
3. Configure defaults (model, language, tone, word count).
4. Optionally enable image generation and SEO linking.

## Usage

1. Navigate to **DeepAI Writer > Generate Content**.
2. Enter your topic/title and optional keywords.
3. Select article type, language, tone, and word count.
4. Click **Generate**.
5. Review the content, then **Save as Draft** or **Publish**.

## Article Types

| Type | Description |
|------|-------------|
| **Standard Article** | Well-structured blog post with introduction, body, and conclusion |
| **Listicle** | Numbered/bulleted list article |
| **How-To Guide** | Step-by-step tutorial with clear instructions |
| **Product Review** | Comprehensive review with pros, cons, and verdict |

## REST API

The plugin exposes a REST API under the `deepai/v1/` namespace:

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/generate` | POST | Generate content |
| `/regenerate` | POST | Regenerate with instructions |
| `/generate-image` | POST | Generate an image |
| `/test-api` | POST | Test API connection |
| `/models` | GET | List available models |
| `/analyze-links` | POST | Analyze content for link opportunities |
| `/save-post` | POST | Save generated content as a post |

All endpoints require `manage_options` capability.

## Project Structure

```
deepai-post-generator/
├── deepai-post-generator.php      # Main plugin entry point
├── uninstall.php                   # Clean uninstall handler
├── readme.txt                      # WordPress.org readme
├── includes/
│   ├── class-deepai-post-generator.php  # Core orchestrator
│   ├── class-deepai-loader.php          # Hook registration
│   ├── class-deepai-activator.php       # Activation (DB, defaults)
│   ├── class-deepai-deactivator.php     # Deactivation cleanup
│   ├── class-deepai-i18n.php            # Internationalization
│   ├── class-deepai-encryption.php      # AES-256 encryption
│   ├── class-deepai-seo-linker.php      # SEO link insertion
│   ├── class-deepai-rest-api.php        # REST API endpoints
│   └── api/
│       ├── class-deepai-api-base.php    # Abstract API base
│       ├── class-deepai-openai.php      # OpenAI implementation
│       └── class-deepai-openrouter.php  # OpenRouter implementation
├── admin/
│   ├── class-deepai-admin.php           # Admin menus & assets
│   ├── class-deepai-settings.php        # Settings API
│   ├── class-deepai-generator.php       # Generator helper
│   ├── class-deepai-prompts.php         # Prompt CRUD
│   └── partials/
│       ├── settings-page.php            # Settings template
│       ├── generator-page.php           # Generator template
│       ├── prompts-page.php             # Prompts template
│       └── history-page.php             # History template
├── assets/
│   ├── css/admin.css                    # Admin stylesheet
│   └── js/admin.js                      # Admin JavaScript
└── languages/
    ├── deepai-post-generator.pot        # Translation template
    ├── deepai-post-generator-pt_BR.po   # Portuguese
    └── deepai-post-generator-es_ES.po   # Spanish
```

## Security

- API keys encrypted with AES-256-CBC using `AUTH_KEY` and `AUTH_SALT`
- All REST endpoints protected with capability checks
- Nonce verification on all admin forms
- Input sanitization and output escaping throughout
- SQL injection prevention via `$wpdb->prepare()`
- XSS protection with `esc_html()`, `esc_attr()`, `wp_kses_post()`

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

This project is licensed under the [GNU General Public License v2.0](LICENSE) or later.
