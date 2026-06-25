<?php
/**
 * Plugin activation handler.
 *
 * @package SearchAnalyticsInsights
 */

namespace SearchAnalyticsInsights\Core;

use SearchAnalyticsInsights\Database\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin activation tasks.
 */
final class Activator {
	private const DEFAULT_SETTINGS = array(
		'search_form'    => array(
			'placeholder' => 'Search posts and pages...',
			'button_text' => 'Search',
			'show_button' => true,
			'form_style'  => 'rounded',
		),
		'ajax_search'    => array(
			'enabled'            => true,
			'minimum_characters' => 2,
			'maximum_results'    => 10,
			'debounce_time'      => 300,
		),
		'search_results' => array(
			'show_featured_images' => true,
			'show_post_type_label' => true,
			'no_results_message'   => 'No results found.',
		),
		'search_sources' => array(
			'load_all_public_post_types' => false,
			'searchable_post_types'      => array( 'post', 'page' ),
		),
		'widget_block'   => array(
			'open_mode'  => 'dropdown',
			'show_label' => true,
		),
		'analytics'      => array(
			'track_logged_in_users'   => false,
			'track_guests'            => true,
			'search_retention_period' => 30,
		),
	);

	/**
	 * Run activation logic.
	 *
	 * @return void
	 */
	public static function activate(): void {
		Schema::create_table();

		if ( false === get_option( Constants::OPTION_SETTINGS, false ) ) {
			$settings             = self::DEFAULT_SETTINGS;
			$legacy_post_types    = get_option( Constants::OPTION_SEARCHABLE_POST_TYPES, false );
			$legacy_ajax_settings = get_option( Constants::OPTION_AJAX_SEARCH_SETTINGS, false );

			if ( is_array( $legacy_post_types ) && ! empty( $legacy_post_types ) ) {
				$settings['search_sources']['searchable_post_types'] = array_values(
					array_filter(
						array_map(
							static function ( $post_type ): string {
								return sanitize_key( (string) $post_type );
							},
							$legacy_post_types
						)
					)
				);
			}

			if ( is_array( $legacy_ajax_settings ) ) {
				if ( isset( $legacy_ajax_settings['max_results'] ) ) {
					$settings['ajax_search']['maximum_results'] = absint( $legacy_ajax_settings['max_results'] );
				}

				if ( isset( $legacy_ajax_settings['minimum_characters'] ) ) {
					$settings['ajax_search']['minimum_characters'] = absint( $legacy_ajax_settings['minimum_characters'] );
				}

				$settings['search_results']['show_featured_images'] = ! empty( $legacy_ajax_settings['show_featured_images'] );
			}

			add_option( Constants::OPTION_SETTINGS, $settings );
		}

		update_option( Constants::OPTION_SCHEMA_VERSION, Constants::VERSION, true );
		update_option( Constants::OPTION_PLUGIN_VERSION, Constants::VERSION, true );
	}
}
