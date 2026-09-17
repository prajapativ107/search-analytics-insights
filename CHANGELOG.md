# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.1.1] - 2026-09-17

### Fixed
* Uninstalling the plugin now also removes the search-clicks database table (previously only the searches table was dropped, leaving click/user/session data behind).
* AJAX click-tracking now requires every word of the original search term to match as a whole word (not a loose substring) on the clicked post, preventing fabricated clicks from inflating click-through analytics.
* WooCommerce search settings (enabled state, search fields, and all display toggles) are no longer reset to their defaults every time the settings page is saved.
* Several default UI strings (search placeholder, button text, "No results found.", "See all results") are now translatable instead of hardcoded in English.
* Corrected a string concatenation that could break translated word order in the "Most Clicked Products" dashboard table.

### Changed
* Minimum required WordPress version raised from 6.2 to 6.3, since the bundled classic widget relies on a `WP_Widget` behavior Core only added in 6.3.

## [1.1.0] - 2026-08-27

### Added
* Search Form Design Customizer page in the admin menu.
* Real-time live-preview sidebar with state controls and viewport simulators.
* Automatic generation and output of customizable `--vplens-*` CSS variables.
* Multi-layout settings supporting Inline, Stacked (Full-Width), and expandable Compact form designs.
* Styling overrides for input placeholders, borders, padding, and sizes.
* Styling overrides for button colors, icons, borders, and typography.
* Styling overrides for suggestion dropdown shadows, radius, and margins.
* Styling overrides for WooCommerce search suggestions, prices, categories, SKU, stock status, and badges.
* Reverted to standard WordPress Dashicons in forms and widgets.

### Changed
- Adapted widgets and shortcodes to inherit styles from the Design Customizer's CSS properties.

### Removed
- Removed the old `form_style` settings parameter from widgets, blocks, and shortcodes.
- Removed custom SVG markups from widgets and form structures.
- Removed suggestion box focus overlay options from AJAX search script.

## [1.0.0] - 2026-07-29

### Added
* Native WordPress search tracking (captures default site search events).
* Real-time AJAX search tracking and analytics capabilities.
* Interactive administration Analytics Dashboard.
* Aggregate overview boxes (Total searches, Unique searches, No-result searches).
* Log feeds showing recent search queries.
* Aggregated views for top searched keywords and rising trending searches.
* Result count tracker logging standard query sizes.
* Extensible page details logging (page title, page URL, page type, and referrer).
* Advanced logs filters (filtering by date range, search terms, and page metadata).
* CSV Export for search statistics inside the Tools menu.
* Classic WordPress widget support.
* Gutenberg block rendering matching search forms.
* Public Shortcode integrations:
  * `[searchlens_form]`
  * `[searchlens_ajax_form]`
  * `[searchlens_popular]`
  * `[searchlens_trending]`
* Configuration options to manage searchable public post types.
* Customizable AJAX search threshold and bounds parameters.
* Fully responsive layout design matching WordPress admin dashboard styles.
* Data privacy-first design storing all logs locally (no IP logging, no third-party APIs).
* PSR-4 autoloader structures and clean DI container setups.
* WordPress Coding Standards (WPCS) rules compliance.
