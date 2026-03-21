=== DeepAI Post Generator ===
Contributors: deepai
Donate link: https://deepai.com/donate
Tags: ai, content generator, openai, chatgpt, openrouter, post generator, article writer, seo, image generation
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate high-quality blog posts, articles, and images using AI (OpenAI & OpenRouter). Smart SEO linking, prompt templates, and multilingual support.

== Description ==

**DeepAI Post Generator** is the most complete AI-powered content creation plugin for WordPress. Connect to OpenAI (GPT-4, GPT-3.5) or OpenRouter (access to 100+ models) and generate professional blog posts, articles, listicles, tutorials, and product reviews — all from your WordPress dashboard.

= Key Features =

* **Dual API Support** — Connect to OpenAI or OpenRouter (or both) and switch between them anytime.
* **Smart Content Generation** — Generate full articles with proper headings, paragraphs, lists, and formatting.
* **AI Image Generation** — Create featured images and in-content images using DALL-E or compatible models.
* **Prompt Template System** — Create, edit, and manage reusable prompt templates with dynamic placeholders.
* **SEO Internal Linking** — Automatically inserts relevant internal links to your existing posts for better SEO.
* **External Link Suggestions** — AI suggests authoritative external links to enrich your content.
* **Multilingual Content** — Generate content in 16+ languages including English, Spanish, Portuguese, French, German, Chinese, Japanese, Korean, Arabic, and more.
* **Tone Control** — Choose from 7 writing tones: Professional, Casual, Academic, Conversational, Humorous, Formal, Friendly.
* **Content Regeneration** — Regenerate content with specific instructions without starting from scratch.
* **Generation History** — Full log of all content generations with tokens used, model, and status.
* **Encrypted API Keys** — Your API keys are stored with AES-256-CBC encryption for maximum security.
* **Modern Admin Interface** — Beautiful, responsive card-based UI that fits naturally in WordPress.
* **One-Click Publish** — Save as draft or publish directly from the generator.

= How It Works =

1. Enter your API key (OpenAI or OpenRouter) in the Settings page.
2. Go to Generate Content, enter your topic, choose language, tone, and article type.
3. Click Generate and review the AI-written content.
4. Edit if needed, then Save as Draft or Publish directly.

= Article Types =

* **Standard Article** — Well-structured blog posts with introduction, body, and conclusion.
* **Listicle** — Numbered or bulleted list articles (e.g., "10 Best Tips for...").
* **How-To Guide** — Step-by-step tutorials with clear instructions.
* **Product Review** — Comprehensive product reviews with pros, cons, and verdict.

= Supported Models =

**OpenAI:** GPT-4o, GPT-4 Turbo, GPT-4, GPT-3.5 Turbo, DALL-E 3
**OpenRouter:** Access to 100+ models including Claude, Gemini, Mistral, LLaMa, and more.

== Installation ==

1. Upload the `deepai-post-generator` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **DeepAI Writer > Settings** and enter your API key.
4. Start generating content from the **Generate Content** page.

= Minimum Requirements =

* WordPress 5.8 or higher
* PHP 7.4 or higher
* OpenSSL PHP extension (recommended for API key encryption)

== Frequently Asked Questions ==

= Do I need an API key? =

Yes. You need an API key from either [OpenAI](https://platform.openai.com/api-keys) or [OpenRouter](https://openrouter.ai/keys). The plugin does not include AI capabilities — it connects to external AI services using your own API key.

= How much does it cost to generate content? =

The plugin itself is free. You pay only for API usage based on the provider's pricing. Typical costs:
* OpenAI GPT-3.5: ~$0.002 per article
* OpenAI GPT-4: ~$0.06-0.12 per article
* OpenRouter: varies by model, many free models available

= Is my API key secure? =

Yes. API keys are encrypted using AES-256-CBC encryption with WordPress security salts before being stored in the database. They are never exposed in the admin interface after saving.

= Can I use my own prompts? =

Absolutely! The Prompts page lets you create and manage custom prompt templates with placeholders like {topic}, {keywords}, {language}, {tone}, {word_count}, and {article_type}.

= Does it support WooCommerce product descriptions? =

Not in the current version, but product review templates can be adapted for this purpose. WooCommerce integration is planned for a future release.

= Can I generate images? =

Yes. The plugin supports AI image generation for featured images and in-content images using DALL-E (OpenAI) or compatible image models (OpenRouter).

= Does it work with Gutenberg? =

Generated content is saved as standard WordPress posts with HTML formatting, which works perfectly with both the Classic Editor and Gutenberg Block Editor.

= What languages can I generate content in? =

The plugin supports content generation in 16 languages: English, Spanish, Portuguese, French, German, Italian, Dutch, Swedish, Norwegian, Danish, Chinese, Japanese, Korean, Arabic, Russian, and Hindi.

= How does internal linking work? =

When enabled, the plugin analyzes your generated content and automatically inserts contextually relevant links to your existing WordPress posts. This helps improve SEO by creating a strong internal link structure.

== Screenshots ==

1. Content Generator — Main interface with topic input, settings sidebar, and content preview.
2. Settings Page — API configuration, content defaults, image settings, and SEO options.
3. Prompt Templates — Create and manage reusable prompt templates.
4. Generation History — Browse all past content generations with details.
5. Generated Content — Example of AI-generated article with formatting.

== Changelog ==

= 1.0.0 — 2026-03-21 =
* Initial release
* OpenAI and OpenRouter API integration
* Content generation with 4 article types
* AI image generation (featured + in-content)
* Prompt template management system
* SEO internal and external linking
* Generation history logging
* AES-256-CBC encrypted API key storage
* Multilingual content support (16 languages)
* 7 writing tone options
* Content regeneration with instructions
* Modern responsive admin interface
* Full i18n support (English, Spanish, Portuguese)
* WordPress REST API integration

== Upgrade Notice ==

= 1.0.0 =
Initial release. Install and configure your API key to start generating AI-powered content.
