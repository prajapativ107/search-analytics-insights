<?php
/**
 * Admin assets loader.
 *
 * @package SearchAnalyticsInsights
 */

namespace SearchAnalyticsInsights\Admin;

use SearchAnalyticsInsights\Core\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues admin-side assets for the plugin screens only.
 */
final class Assets {
	private const SCREEN_ID = 'toplevel_page_search-analytics-insights';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue CSS and JS for the plugin screen.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 *
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || false === strpos( $screen->id, 'search-analytics' ) ) {
			return;
		}

		wp_enqueue_style(
			'search-analytics-insights-admin',
			SEARCH_ANALYTICS_INSIGHTS_URL . 'assets/css/admin.css',
			array(),
			Constants::VERSION
		);

		wp_enqueue_script(
			'search-analytics-insights-admin',
			SEARCH_ANALYTICS_INSIGHTS_URL . 'assets/js/admin.js',
			array( 'wp-element' ),
			Constants::VERSION,
			true
		);
	}
}
