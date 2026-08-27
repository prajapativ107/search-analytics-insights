<?php
/**
 * Admin assets loader.
 *
 * @package VPLens
 */

namespace VPLens\Admin;

use VPLens\Core\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues admin-side assets for the plugin screens only.
 */
final class Assets {
	private const SCREEN_ID = 'toplevel_page_vplens';

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
	 * @return void
	 */
	public function enqueue_assets(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || false === strpos( $screen->id, 'vplens' ) ) {
			return;
		}

		wp_enqueue_style(
			'vplens-admin',
			VPLENS_URL . 'assets/css/admin.css',
			array(),
			Constants::VERSION
		);

		wp_enqueue_script(
			'vplens-admin',
			VPLENS_URL . 'assets/js/admin.js',
			array( 'wp-element' ),
			Constants::VERSION,
			true
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		if ( 'vplens-design' === $page ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			wp_enqueue_style(
				'vplens-ajax-search',
				VPLENS_URL . 'assets/css/ajax-search.css',
				array(),
				Constants::VERSION
			);

			wp_enqueue_style(
				'vplens-admin-design',
				VPLENS_URL . 'assets/css/admin-design.css',
				array( 'vplens-admin' ),
				Constants::VERSION
			);

			wp_enqueue_script(
				'vplens-admin-design',
				VPLENS_URL . 'assets/js/admin-design.js',
				array( 'jquery', 'wp-color-picker' ),
				Constants::VERSION,
				true
			);

			wp_localize_script(
				'vplens-admin-design',
				'vplensDesignI18n',
				array(
					'reset_confirm' => __( 'Are you sure you want to reset all design settings back to defaults?', 'search-analytics-insights' ),
					'searching'     => __( 'Searching...', 'search-analytics-insights' ),
					'no_results'    => __( 'No results found.', 'search-analytics-insights' ),
				)
			);
		}

		wp_localize_script(
			'vplens-admin',
			'vplensAdminI18n',
			array(
				'copied'       => __( 'Copied', 'search-analytics-insights' ),
				'details'      => __( 'Details', 'search-analytics-insights' ),
				'hide'         => __( 'Hide', 'search-analytics-insights' ),
				'show_filters' => __( 'Show Filters', 'search-analytics-insights' ),
				'hide_filters' => __( 'Hide Filters', 'search-analytics-insights' ),
			)
		);
	}
}
