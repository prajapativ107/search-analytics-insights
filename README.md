# SearchLens – Search Analytics & Insights

Track WordPress search activity with powerful analytics, AJAX search support, Gutenberg blocks, widgets, CSV export, and privacy-friendly reporting—all inside your WordPress dashboard.

[![WordPress Version](https://img.shields.io/wordpress/v/search-analytics-insights.svg)](https://wordpress.org/plugins/search-analytics-insights/)
[![WordPress Tested Version](https://img.shields.io/wordpress/plugin/tested/search-analytics-insights.svg)](https://wordpress.org/plugins/search-analytics-insights/)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.0-8892bf.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Description

**SearchLens – Search Analytics & Insights** helps you understand exactly what your visitors are searching for on your WordPress website.

Monitor search activity, discover popular search terms, identify searches with no results, and improve your website's content using actionable analytics.

Unlike third-party analytics services, SearchLens stores all search data locally inside your WordPress database, making it lightweight, privacy-friendly, and easy to use.

Whether you use the default WordPress search or AJAX-powered search, SearchLens records every search and presents meaningful insights through a clean analytics dashboard.

### Why SearchLens?

Search analytics help you understand visitor intent and improve your website experience.

With SearchLens you can:
* Discover what users are searching for.
* Identify missing or unavailable content.
* Find searches returning no results.
* Improve navigation and search experience.
* Optimize your content strategy.
* Make data-driven decisions.
* Keep all analytics inside WordPress.
* No external analytics service required.

---

## Features

* **Native WordPress search tracking** - Track and log default WordPress searches.
* **AJAX search tracking** - Capture and monitor AJAX-powered search queries.
* **Powerful analytics dashboard** - View overall search metrics and statistics.
* **Total searches overview** - Track the total number of searches performed.
* **Unique searches overview** - Monitor unique search terms and counts.
* **No-result search tracking** - Identify search terms that returned zero results.
* **Recent search activity** - Review a live feed of recent search queries.
* **Top searched keywords** - Identify the most frequently searched terms.
* **Trending searches** - Spot rising search terms and popularity trends.
* **Search result count tracking** - Log the number of search results returned for each query.
* **Track page details** - Log the page title, page URL, page type, and referrer for every search.
* **Advanced analytics filters** - Filter search logs by search term, page title, page type, and date range.
* **CSV export** - Export search data reports to CSV format for external analysis.
* **Search widget** - Use the classic SearchLens widget to display search forms.
* **Gutenberg Search Widget block** - Easily add a search block to pages and posts.
* **Search shortcodes** - Insert search forms and lists using simple shortcodes.
* **Configurable searchable post types** - Select which post types are searchable.
* **Configurable AJAX search settings** - Fine-tune thresholds, search limits, and behaviors.
* **Responsive admin interface** - Access your search insights on any device.
* **Lightweight and fast** - Optimized query performance to ensure zero impact on site speed.
* **Privacy-friendly** - Keeps all search data local without storing IP addresses.
* **Stores data locally** - Self-host your analytics data directly within WordPress.
* **No third-party services** - Runs 100% independently without external API connections.
* **Built following WordPress Coding Standards** - Secure, structured, and compliant code.

---

## Screenshots

*Screenshots are located in the `assets/` directory.*

1. **Dashboard Overview** - Modern and responsive WordPress admin interface displaying aggregate search charts, totals, and unique queries.
2. **Detailed Analytics** - Filters by date, keyword, and result counts, displaying individual search logs.
3. **Top Terms & Activity** - Overview of popular keywords and recent activity.
4. **Configuration Settings** - Controls for custom post types and AJAX thresholds.

---

## Installation

1. Upload the plugin files to the `/wp-content/plugins/search-analytics-insights/` directory, or install the plugin directly through the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Open **SearchLens** from the WordPress admin menu.
4. Configure your preferred search settings in the **Search Settings** tab.
5. Add the SearchLens widget, Gutenberg block, or shortcode wherever required.

---

## Usage & Integration

### Gutenberg Block

SearchLens includes a dedicated **Gutenberg Search Widget** block. 
1. Open the Block Editor on any page/post.
2. Search for the **SearchLens** block and add it to your page layout.
3. Use the block sidebar options to customize placeholders, button text, and AJAX behaviors.

### Shortcodes

You can display search forms and popular/trending queries anywhere on your site using the following shortcodes:

#### Standard Search Form
```text
[searchlens_form placeholder="Search..." button_text="Search"]
```

#### AJAX Search Form
```text
[searchlens_ajax_form placeholder="Search..." limit="10"]
```

#### Popular Searches
```text
[searchlens_popular limit="5" show_count="true"]
```

#### Trending Searches
```text
[searchlens_trending limit="5" title="Trending Searches"]
```

---

## Frequently Asked Questions

### Does SearchLens support AJAX search?
Yes. SearchLens supports real-time AJAX search tracking and analytics.

### Does it work with Custom Post Types?
Yes. Under the Settings tab, you can select which public post types (e.g., portfolio, products) are allowed to be searched.

### Does it support Gutenberg?
Yes. A native Gutenberg block is included to drop search inputs onto any block-enabled layout.

### Can I export analytics?
Yes. You can export the search analytics logs to CSV under the **Tools** tab.

### Does SearchLens store IP addresses?
No. To protect visitor privacy and maintain compliance with data privacy regulations (like GDPR), raw IP addresses are never recorded or stored.

### Is it GDPR friendly?
Yes. All search log records are kept entirely within your local WordPress database. No external services, trackers, or cookies are loaded.

---

## Contributing

Contributions are welcome! Please read our [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests to the project.

### Local Development Setup
1. Clone the repository into your local WordPress plugin directory.
2. Run `composer install` to setup local linting tools.
3. Build frontend assets if modifying Gutenberg blocks.

---

## Bug Reports

Found a bug? Please report it using our GitHub issue tracker. Make sure to use the Bug Report template and search existing issues first.

For security-related issues, please refer to our [Security Policy](SECURITY.md) instead of creating a public issue.

---

## Security Policy

We take security seriously. Please read our [SECURITY.md](SECURITY.md) to understand supported versions and how to report vulnerabilities responsibly.

---

## Changelog

See the full history of changes in [CHANGELOG.md](CHANGELOG.md).

---

## Release Process

We use GitHub Actions to automate linting checks and security scans for development. Actual plugin deployments to the WordPress.org directory are performed manually using SVN.

### 1. Development & Continuous Integration
Pushing changes or submitting pull requests triggers automation checks:
* **CI Workflow**: Runs syntax check, composer validation, and PHPCS standard checks across PHP 8.0–8.3.
* **Security Scanning**: Automated CodeQL scans identify vulnerabilities weekly and on push.
* **Deployments**: No automatic deployment is performed from GitHub.

### 2. Manual WordPress.org Release
To publish a new version of the plugin:
1. Update version headers in `searchlens-search-analytics.php` and `includes/Core/Constants.php`.
2. Commit and push version updates to the `main` branch on GitHub.
3. Tag the release commit:
   ```bash
   git tag v1.0.1
   git push origin v1.0.1
   ```
4. Checkout the official WordPress.org SVN repository, copy assets and built plugin files into `trunk/` and `tags/1.0.1/`, and commit the changes to SVN manually to publish the release.

---

## Credits

* Maintained by [Vivek Prajapati](https://github.com/prajapativ107)
* All Contributors and Open Source projects used.

---

## License

This project is licensed under the GPL-2.0-or-later License - see the [LICENSE](LICENSE) file for details.
