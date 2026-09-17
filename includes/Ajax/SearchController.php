<?php
/**
 * AJAX search controller.
 *
 * @package VPLens
 */

namespace VPLens\Ajax;

use VPLens\Core\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Handles AJAX search requests.
 */
final class SearchController {
	private SearchService $search_service;
	private SearchTracker $search_tracker;

	/**
	 * Constructor.
	 *
	 * @param SearchService $search_service Search service instance.
	 * @param SearchTracker $search_tracker Search tracker instance.
	 */
	public function __construct( SearchService $search_service, SearchTracker $search_tracker ) {
		$this->search_service = $search_service;
		$this->search_tracker = $search_tracker;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'wp_ajax_' . Constants::AJAX_ACTION_SEARCH, array( $this, 'handle_search' ) );
		add_action( 'wp_ajax_nopriv_' . Constants::AJAX_ACTION_SEARCH, array( $this, 'handle_search' ) );
		add_action( 'wp_ajax_' . Constants::AJAX_ACTION_CLICK, array( $this, 'handle_click_tracking' ) );
		add_action( 'wp_ajax_nopriv_' . Constants::AJAX_ACTION_CLICK, array( $this, 'handle_click_tracking' ) );
	}

	/**
	 * Handle the AJAX search request.
	 *
	 * @return void
	 */
	public function handle_search(): void {
		check_ajax_referer( Constants::NONCE_ACTION, 'nonce' );

		if ( ! $this->search_service->is_enabled() ) {
			wp_send_json_error(
				array(
					'message' => __( 'AJAX search is disabled.', 'search-analytics-insights' ),
				),
				403
			);
		}

		$search_term        = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['term'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$term_length        = function_exists( 'mb_strlen' ) ? mb_strlen( $search_term ) : strlen( $search_term );
		$minimum_characters = $this->search_service->get_minimum_characters();

		if ( $term_length < $minimum_characters ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %d: minimum character count */
						__( 'Please enter at least %d characters.', 'search-analytics-insights' ),
						$minimum_characters
					),
				),
				400
			);
		}

		$page_title = isset( $_POST['page_title'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['page_title'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$page_url   = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['page_url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$referrer   = isset( $_POST['referrer'] ) ? esc_url_raw( wp_unslash( (string) $_POST['referrer'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$page_type  = isset( $_POST['page_type'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['page_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$results   = $this->search_service->search( $search_term, $this->search_service->get_max_results() );
		$search_id = $this->search_tracker->record_search(
			$search_term,
			(int) $results['total'],
			isset( $results['matched_post_types'] ) && is_array( $results['matched_post_types'] ) ? $results['matched_post_types'] : array(),
			array(
				'page_title' => $page_title,
				'page_url'   => $page_url,
				'referrer'   => $referrer,
				'page_type'  => $page_type,
			)
		);

		wp_send_json_success(
			array(
				'search_id' => $search_id ? (int) $search_id : null,
				'items'     => $results['items'],
			)
		);
	}

	/**
	 * Handle AJAX click tracking request.
	 *
	 * @return void
	 */
	public function handle_click_tracking(): void {
		check_ajax_referer( Constants::NONCE_ACTION, 'nonce' );

		$search_id = isset( $_POST['search_id'] ) ? absint( $_POST['search_id'] ) : 0;
		$post_id   = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $search_id || ! $post_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Missing required parameters.', 'search-analytics-insights' ),
				),
				400
			);
		}

		$repository = new \VPLens\Database\Repository\SearchRepository();

		// 1. Verify search_id exists
		if ( ! $repository->search_exists( $search_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid search event reference.', 'search-analytics-insights' ),
				),
				400
			);
		}

		// 2. Verify post_id is valid and published
		$post_status = get_post_status( $post_id );
		if ( 'publish' !== $post_status ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid target content.', 'search-analytics-insights' ),
				),
				400
			);
		}

		// 3. Verify product is a legitimate result for that search where practical
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$search_term = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT search_term FROM %i WHERE id = %d',
				Constants::table_name(),
				$search_id
			)
		);

		$legitimate = false;
		if ( ! empty( $search_term ) ) {
			$title   = get_the_title( $post_id );
			$content = get_post_field( 'post_content', $post_id );
			$excerpt = get_post_field( 'post_excerpt', $post_id );
			$sku     = get_post_meta( $post_id, '_sku', true );

			$search_words = preg_split( '/\s+/u', mb_strtolower( trim( $search_term ) ) );
			$search_words = array_filter( $search_words );

			// A click only counts if at least one word from the original search term shows up
			// somewhere on the clicked post, so a click_id can't be forged for arbitrary content.
			foreach ( $search_words as $word ) {
				if (
					false !== mb_strpos( mb_strtolower( $title ), $word ) ||
					false !== mb_strpos( mb_strtolower( $content ), $word ) ||
					false !== mb_strpos( mb_strtolower( $excerpt ), $word ) ||
					( ! empty( $sku ) && false !== mb_strpos( mb_strtolower( $sku ), $word ) )
				) {
					$legitimate = true;
					break;
				}
			}
		}

		if ( ! $legitimate ) {
			wp_send_json_error(
				array(
					'message' => __( 'Content is not a legitimate match for the search query.', 'search-analytics-insights' ),
				),
				400
			);
		}

		$user_id    = get_current_user_id() ? absint( get_current_user_id() ) : null;
		$session_id = null;
		if ( ! empty( $_COOKIE['vplens_session'] ) ) {
			$session_id = sanitize_text_field( wp_unslash( (string) $_COOKIE['vplens_session'] ) );
		} elseif ( ! empty( $_COOKIE['search_analytics_insights_session'] ) ) {
			$session_id = sanitize_text_field( wp_unslash( (string) $_COOKIE['search_analytics_insights_session'] ) );
		}

		$click_id = $repository->insert_click( $search_id, $post_id, $user_id, $session_id );

		if ( ! $click_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to log click event.', 'search-analytics-insights' ),
				),
				500
			);
		}

		wp_send_json_success(
			array(
				'click_id' => $click_id,
			)
		);
	}
}
