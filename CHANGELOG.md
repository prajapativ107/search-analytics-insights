# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

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
