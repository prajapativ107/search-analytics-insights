# Contributing to SearchLens

Thank you for your interest in contributing to SearchLens! We welcome community contributions, bug reports, and feature suggestions to help improve the plugin.

To ensure a smooth collaboration, please follow the guidelines below.

---

## Code of Conduct

By participating in this project, you agree to abide by our [Code of Conduct](CODE_OF_CONDUCT.md). Please report any unacceptable behavior to the project maintainers.

---

## Development Setup

To set up SearchLens for local development:

1. **Prerequisites**: Ensure you have a local WordPress installation running, plus **PHP 8.0+** and **Composer** installed.
2. **Clone the Repository**: Clone the repository into your WordPress plugins folder:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/prajapativ107/search-analytics-insights.git
   ```
3. **Install Dependencies**: Set up developer tools and coding standards:
   ```bash
   cd search-analytics-insights
   composer install
   ```

---

## Coding Standards

This project follows the **[WordPress Coding Standards (WPCS)](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)** for PHP code.

### Running the Linter
Before committing, lint your files to ensure code quality and style compliance:

* **Lint Check**:
  ```bash
  composer run lint
  ```
* **Auto-fix Style Violations**:
  ```bash
  composer run lint:fix
  ```

### Key Guidelines:
* Use standard WordPress function and variable naming styles (naming conventions, `snake_case` for variables/functions).
* Always escape dynamic output (`esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`, etc.).
* Always verify nonces when executing sensitive actions or AJAX.
* Check user capabilities using `current_user_can()`.

---

## Git Workflow

### Branch Naming Conventions
Choose descriptive branch names prefixed with the type of change:

* **Bug Fixes**: `fix/issue-description` or `fix/issue-number`
* **New Features**: `feature/feature-name`
* **Documentation**: `docs/documentation-topic`
* **Code Refactoring**: `refactor/component-name`

### Commit Message Guidelines
Write clear, imperative commit messages describing the change:

* **Example**: `Fix PHP warning in widgets when results are empty`
* Avoid vague commit messages like `update code` or `fixed bug`.
* If a commit closes an issue, reference it at the end (e.g. `(Closes #45)`).

---

## Pull Request Checklist

When submitting a pull request, ensure that you:

1. **Use the Template**: Complete the pull request template checklist.
2. **Target Branch**: Submit your PR against the `main` branch.
3. **Run Lint Checks**: Ensure that `composer run lint` completes without warnings or errors.
4. **Backward Compatibility**: Verify that your changes do not break existing public plugin APIs or modify database schemas.
5. **No Functional Disruptions**: Do not change plugin core functionality unless requested.

---

## Issue Reporting

* **Bugs**: Use the bug report template on GitHub and include detailed steps to reproduce.
* **Features**: Propose feature requests using the template, providing the context and proposed implementation if possible.
* **Security**: For security vulnerabilities, **do not** open a public issue. Follow the process in [SECURITY.md](SECURITY.md).

---

## Code Review Expectations

All PRs require review by a maintainer before merging. During review, we expect:
* Constructive and polite feedback.
* Prompt response to requested changes.
* Alignment with the plugin's architecture and performance requirements.
