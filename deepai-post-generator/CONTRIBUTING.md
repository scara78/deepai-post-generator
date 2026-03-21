# Contributing to DeepAI Post Generator

Thank you for your interest in contributing! Here are guidelines to help you get started.

## How to Contribute

### Reporting Bugs

1. Check existing [Issues](../../issues) to avoid duplicates.
2. Use the **Bug Report** template when creating a new issue.
3. Include: WordPress version, PHP version, plugin version, steps to reproduce, expected vs actual behavior.

### Suggesting Features

1. Open an issue with the **Feature Request** template.
2. Describe the use case and expected behavior.

### Submitting Code

1. Fork the repository and create a feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```
2. Follow the coding standards below.
3. Test your changes in a local WordPress environment.
4. Submit a Pull Request against the `main` branch.

## Coding Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) for PHP.
- Use tabs for indentation in PHP files.
- Prefix all functions, classes, and hooks with `deepai_pg_` or `DeepAI_PG_`.
- Sanitize all inputs, escape all outputs.
- Use `$wpdb->prepare()` for all database queries.
- Add nonce verification to all form submissions and AJAX handlers.
- Keep PHP 7.4 compatibility — no PHP 8.0+ features.

## Translation

- Use `__()` and `_e()` for all user-facing strings.
- Text domain: `deepai-post-generator`.
- New translations should be added as `.po` files in the `languages/` directory.

## Security

- Never expose API keys in responses or logs.
- Validate permissions with `current_user_can()` on all endpoints.
- Follow OWASP Top 10 guidelines.

## Code of Conduct

Be respectful and constructive. We follow the [WordPress Community Code of Conduct](https://make.wordpress.org/handbook/community-code-of-conduct/).
