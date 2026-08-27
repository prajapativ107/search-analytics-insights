<?php
/**
 * Admin dashboard renderer.
 *
 * @package VPLens
 */

namespace VPLens\Admin;

use VPLens\Analytics\Service\AnalyticsService;
use VPLens\Core\Constants;
defined( 'ABSPATH' ) || exit;

/**
 * Renders the analytics dashboard in wp-admin.
 */
final class Dashboard {
	private AnalyticsService $analytics_service;

	/**
	 * Constructor.
	 *
	 * @param AnalyticsService $analytics_service Analytics service instance.
	 */
	public function __construct( AnalyticsService $analytics_service ) {
		$this->analytics_service = $analytics_service;
	}

	/**
	 * Render the legacy top-level page callback (delegates to dashboard).
	 *
	 * @return void
	 */
	public function render(): void {
		$this->render_dashboard_page();
	}

	/**
	 * Render the main dashboard page.
	 *
	 * @return void
	 */
	public function render_dashboard_page(): void {
		$filters         = array();
		$summary         = $this->analytics_service->get_dashboard_summary( $filters );
		$top_terms       = $this->analytics_service->get_top_search_terms( $filters, 5 );
		$recent_activity = $this->analytics_service->get_recent_search_activity( $filters, 5 );

		$this->render_tabbed_page(
			'dashboard',
			function () use ( $summary, $top_terms, $recent_activity ) {
				?>
			<div class="vplens-dashboard-grid">
				<div class="vplens-card-column">
				<div class="vplens-panel">
					<h3><?php esc_html_e( 'Overview Summary', 'search-analytics-insights' ); ?></h3>
					<div class="vplens-summary">
						<?php $this->render_summary_card( __( 'Total Searches', 'search-analytics-insights' ), (int) $summary['total_searches'] ); ?>
						<?php $this->render_summary_card( __( 'Unique Searches', 'search-analytics-insights' ), (int) $summary['unique_searches'] ); ?>
						<?php $this->render_summary_card( __( 'No Results', 'search-analytics-insights' ), (int) $summary['no_result_searches'] ); ?>
						<?php $this->render_summary_card( __( 'Search CTR', 'search-analytics-insights' ), number_format( $summary['ctr'] ?? 0, 1 ) . '%' ); ?>
					</div>
				</div>
				
				<div class="vplens-panel">
					<h3><?php esc_html_e( 'Quick Actions', 'search-analytics-insights' ); ?></h3>
					<div class="vplens-quick-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=vplens-analytics' ) ); ?>" class="button button-primary"><?php esc_html_e( 'View Analytics', 'search-analytics-insights' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=vplens-settings' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Manage Settings', 'search-analytics-insights' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=vplens-help' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Read Help Docs', 'search-analytics-insights' ); ?></a>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=vplens-tools' ) ); ?>">
							<?php wp_nonce_field( 'vplens_tools_action', 'vplens_tools_nonce' ); ?>
							<input type="hidden" name="vplens_action" value="export" />
							<button type="submit" class="button button-secondary"><?php esc_html_e( 'Export to CSV', 'search-analytics-insights' ); ?></button>
						</form>
					</div>
				</div>
			</div>

				<div class="vplens-card-column">
					<div class="vplens-panel">
						<h3><?php esc_html_e( 'Top Searches (Overall)', 'search-analytics-insights' ); ?></h3>
						<table class="widefat striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Term', 'search-analytics-insights' ); ?></th>
									<th><?php esc_html_e( 'Searches', 'search-analytics-insights' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( empty( $top_terms ) ) : ?>
									<tr><td colspan="2"><?php esc_html_e( 'No search terms recorded yet.', 'search-analytics-insights' ); ?></td></tr>
								<?php else : ?>
									<?php foreach ( $top_terms as $row ) : ?>
										<tr>
											<td><strong><?php echo esc_html( (string) $row['search_term'] ); ?></strong></td>
											<td><?php echo esc_html( (string) $row['search_count'] ); ?></td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					<div class="vplens-panel">
						<h3><?php esc_html_e( 'Recent Search Activity', 'search-analytics-insights' ); ?></h3>
						<table class="widefat striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Term', 'search-analytics-insights' ); ?></th>
									<th><?php esc_html_e( 'Date', 'search-analytics-insights' ); ?></th>
									<th><?php esc_html_e( 'Results', 'search-analytics-insights' ); ?></th>
									<th><?php esc_html_e( 'From', 'search-analytics-insights' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'search-analytics-insights' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( empty( $recent_activity ) ) : ?>
									<tr><td colspan="5"><?php esc_html_e( 'No search activity found.', 'search-analytics-insights' ); ?></td></tr>
								<?php else : ?>
									<?php foreach ( $recent_activity as $record ) : ?>
										<tr>
											<td><?php echo esc_html( (string) $record['search_term'] ); ?></td>
											<td><span class="description"><?php echo esc_html( (string) $record['searched_at'] ); ?></span></td>
											<td><span class="badge <?php echo $record['result_count'] > 0 ? 'badge-success' : 'badge-danger'; ?>"><?php echo esc_html( (string) $record['result_count'] ); ?></span></td>
											<td><?php $this->render_page_column( $record ); ?></td>
											<td>
												<?php
												$record_id = ! empty( $record['id'] ) ? (string) $record['id'] : md5( (string) $record['search_term'] . '-' . (string) $record['searched_at'] );
												?>
												<button type="button" class="button button-small vplens-toggle-details" data-target="vplens-details-<?php echo esc_attr( $record_id ); ?>" data-show-label="<?php echo esc_attr__( 'Details', 'search-analytics-insights' ); ?>" data-hide-label="<?php echo esc_attr__( 'Hide', 'search-analytics-insights' ); ?>">
													<?php esc_html_e( 'Details', 'search-analytics-insights' ); ?>
												</button>
											</td>
										</tr>
										<?php $this->render_details_row( $record, 5 ); ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
				<?php
			}
		);
	}

	/**
	 * Render the detailed analytics reports page.
	 *
	 * @return void
	 */
	public function render_analytics_page(): void {
		$filters                         = $this->get_filters();
		$summary                         = $this->analytics_service->get_dashboard_summary( $filters );
		$aggregated_activity             = $this->analytics_service->get_aggregated_search_activity(
			$filters,
			$filters['page'],
			$filters['per_page']
		);
		$recent_activity                 = $this->analytics_service->get_recent_search_activity( $filters, $filters['per_page'] );
		$top_terms                       = $this->analytics_service->get_top_search_terms( $filters, 10 );
		$no_result_filters               = $filters;
		$no_result_filters['no_results'] = 1;
		$no_result_terms                 = $this->analytics_service->get_top_search_terms( $no_result_filters, $filters['per_page'] );
		$daily_counts                    = $this->analytics_service->get_searches_per_day( $filters );
		$top_pages_by_title              = $this->analytics_service->get_top_pages_by_title( $filters, 10 );
		$top_pages_by_url                = $this->analytics_service->get_top_pages_by_url( $filters, 10 );
		$searches_by_page_type           = $this->analytics_service->get_searches_by_page_type( $filters, 10 );
		$most_clicked_products           = $this->analytics_service->get_most_clicked_products( $filters, 10 );
		$search_perf                     = $this->analytics_service->get_search_performance( $filters, 10 );

		$total_pages = max( 1, (int) ceil( $aggregated_activity['total'] / $filters['per_page'] ) );
		$page        = min( $filters['page'], $total_pages );

		$this->render_tabbed_page(
			'analytics',
			function () use ( $filters, $summary, $aggregated_activity, $recent_activity, $top_terms, $no_result_terms, $daily_counts, $total_pages, $page, $top_pages_by_title, $top_pages_by_url, $searches_by_page_type, $most_clicked_products, $search_perf ) {
				?>
			<div class="vplens-filters-card" aria-labelledby="vplens-filters-card-title">
				<div class="vplens-filters-header">
					<div class="vplens-filters-header-title">
						<span class="dashicons dashicons-filter vplens-filter-icon" aria-hidden="true"></span>
						<div>
							<h3 id="vplens-filters-card-title"><?php esc_html_e( 'Search Filters', 'search-analytics-insights' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Refine your search analytics results', 'search-analytics-insights' ); ?></p>
						</div>
					</div>
					<button type="button" class="vplens-filters-toggle-btn" aria-expanded="true" aria-controls="vplens-filters-form" data-show-text="<?php echo esc_attr__( 'Show Filters', 'search-analytics-insights' ); ?>" data-hide-text="<?php echo esc_attr__( 'Hide Filters', 'search-analytics-insights' ); ?>">
						<span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
						<span class="vplens-toggle-text"><?php esc_html_e( 'Hide Filters', 'search-analytics-insights' ); ?></span>
					</button>
				</div>

				<form id="vplens-filters-form" method="get" class="vplens-filters-form">
					<input type="hidden" name="page" value="vplens-analytics" />
					<div class="vplens-filters-form-inner">
						<div class="vplens-filters-grid">
							<!-- From Date -->
							<div class="vplens-filter-field">
								<label for="vplens-date-from"><?php esc_html_e( 'From', 'search-analytics-insights' ); ?></label>
								<div class="vplens-input-icon-wrapper">
									<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
									<input id="vplens-date-from" type="date" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" aria-label="<?php esc_attr_e( 'From Date', 'search-analytics-insights' ); ?>" />
								</div>
							</div>

							<!-- To Date -->
							<div class="vplens-filter-field">
								<label for="vplens-date-to"><?php esc_html_e( 'To', 'search-analytics-insights' ); ?></label>
								<div class="vplens-input-icon-wrapper">
									<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
									<input id="vplens-date-to" type="date" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" aria-label="<?php esc_attr_e( 'To Date', 'search-analytics-insights' ); ?>" />
								</div>
							</div>

							<!-- Search Term -->
							<div class="vplens-filter-field">
								<label for="vplens-term"><?php esc_html_e( 'Search Term', 'search-analytics-insights' ); ?></label>
								<div class="vplens-input-icon-wrapper">
									<span class="dashicons dashicons-search" aria-hidden="true"></span>
									<input id="vplens-term" type="search" name="search_term" value="<?php echo esc_attr( $filters['search_term'] ); ?>" placeholder="<?php esc_attr_e( 'Search term...', 'search-analytics-insights' ); ?>" />
								</div>
							</div>

							<!-- Post Type -->
							<div class="vplens-filter-field">
								<label for="vplens-page-type"><?php esc_html_e( 'Post Type', 'search-analytics-insights' ); ?></label>
								<select id="vplens-page-type" name="page_type">
									<option value=""><?php esc_html_e( 'All Post Types', 'search-analytics-insights' ); ?></option>
									<?php
									$post_types = get_post_types( array( 'public' => true ), 'objects' );
									$exclude    = array(
										'attachment',
										'revision',
										'nav_menu_item',
										'wp_navigation',
										'wp_template',
										'wp_template_part',
										'customize_changeset',
										'oembed_cache',
										'user_request',
										'wp_font_family',
										'wp_font_face',
									);
									foreach ( $post_types as $post_type ) :
										if ( in_array( $post_type->name, $exclude, true ) ) {
											continue;
										}
										$label = ! empty( $post_type->labels->singular_name ) ? $post_type->labels->singular_name : $post_type->name;
										?>
										<option value="<?php echo esc_attr( $post_type->name ); ?>" <?php selected( $filters['page_type'], $post_type->name ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<!-- Page Title -->
							<div class="vplens-filter-field">
								<label for="vplens-page-title"><?php esc_html_e( 'Page Title', 'search-analytics-insights' ); ?></label>
								<input id="vplens-page-title" type="text" name="page_title" value="<?php echo esc_attr( $filters['page_title'] ); ?>" placeholder="<?php esc_attr_e( 'Search page title...', 'search-analytics-insights' ); ?>" />
							</div>

							<!-- Page URL -->
							<div class="vplens-filter-field">
								<label for="vplens-page-url"><?php esc_html_e( 'Page URL', 'search-analytics-insights' ); ?></label>
								<div class="vplens-input-icon-wrapper">
									<span class="dashicons dashicons-admin-links" aria-hidden="true"></span>
									<input id="vplens-page-url" type="text" name="page_url" value="<?php echo esc_attr( $filters['page_url'] ); ?>" placeholder="<?php esc_attr_e( 'Search page URL...', 'search-analytics-insights' ); ?>" />
								</div>
							</div>

							<!-- Username -->
							<div class="vplens-filter-field">
								<label for="vplens-username"><?php esc_html_e( 'Username', 'search-analytics-insights' ); ?></label>
								<div class="vplens-input-icon-wrapper">
									<span class="dashicons dashicons-admin-users" aria-hidden="true"></span>
									<input id="vplens-username" type="text" name="username" value="<?php echo esc_attr( $filters['username'] ); ?>" placeholder="<?php esc_attr_e( 'Search username...', 'search-analytics-insights' ); ?>" />
								</div>
							</div>

							<!-- No Results Only Checkbox -->
							<div class="vplens-filter-field vplens-filter-field-checkbox">
								<label for="vplens-no-results">
									<input id="vplens-no-results" type="checkbox" name="no_results" value="1" <?php checked( 1, (int) $filters['no_results'] ); ?> />
									<span><?php esc_html_e( 'No Results Only', 'search-analytics-insights' ); ?></span>
								</label>
								<p class="description"><?php esc_html_e( 'Show only searches that returned zero results.', 'search-analytics-insights' ); ?></p>
							</div>

							<!-- Per Page Select -->
							<div class="vplens-filter-field">
								<label for="vplens-per-page"><?php esc_html_e( 'Per Page', 'search-analytics-insights' ); ?></label>
								<select id="vplens-per-page" name="per_page">
									<?php foreach ( array( 10, 20, 50, 100 ) as $option ) : ?>
										<option value="<?php echo esc_attr( (string) $option ); ?>" <?php selected( $filters['per_page'], $option ); ?>><?php echo esc_html( (string) $option ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<!-- Actions Buttons -->
						<div class="vplens-filters-actions">
							<div class="vplens-filters-actions-left">
								<button type="submit" class="button button-primary vplens-filter-submit-btn">
									<span class="dashicons dashicons-filter" aria-hidden="true"></span>
									<?php esc_html_e( 'Filter', 'search-analytics-insights' ); ?>
								</button>
							</div>
							<div class="vplens-filters-actions-right">
								<?php
								$reset_url = admin_url( 'admin.php?page=vplens-analytics' );
								?>
								<a href="<?php echo esc_url( $reset_url ); ?>" class="button button-secondary vplens-filter-reset-btn">
									<span class="dashicons dashicons-undo" aria-hidden="true"></span>
									<?php esc_html_e( 'Reset Filters', 'search-analytics-insights' ); ?>
								</a>
							</div>
						</div>

						<!-- Tip / Information Box -->
						<div class="vplens-filters-tip-box">
							<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
							<p><?php esc_html_e( 'Tip: You can filter results by date range, page information, and search terms to find exactly what you need.', 'search-analytics-insights' ); ?></p>
						</div>
					</div>
				</form>

				<!-- Summary Bar -->
				<div class="vplens-filters-summary-bar">
					<div class="vplens-filters-summary-left">
						<strong><?php esc_html_e( 'Active Filters:', 'search-analytics-insights' ); ?></strong>
						<span class="vplens-active-filters-list">
							<?php
							$active_filters_output = array();
							if ( ! empty( $filters['date_from'] ) ) {
								/* translators: %s: From date */
								$active_filters_output[] = sprintf( __( 'From: %s', 'search-analytics-insights' ), $filters['date_from'] );
							}
							if ( ! empty( $filters['date_to'] ) ) {
								/* translators: %s: To date */
								$active_filters_output[] = sprintf( __( 'To: %s', 'search-analytics-insights' ), $filters['date_to'] );
							}
							if ( ! empty( $filters['search_term'] ) ) {
								/* translators: %s: Search term */
								$active_filters_output[] = sprintf( __( 'Term: "%s"', 'search-analytics-insights' ), $filters['search_term'] );
							}
							if ( ! empty( $filters['page_type'] ) ) {
								/* translators: %s: Page type */
								$active_filters_output[] = sprintf( __( 'Type: %s', 'search-analytics-insights' ), $filters['page_type'] );
							}
							if ( ! empty( $filters['page_title'] ) ) {
								/* translators: %s: Page title */
								$active_filters_output[] = sprintf( __( 'Title: "%s"', 'search-analytics-insights' ), $filters['page_title'] );
							}
							if ( ! empty( $filters['page_url'] ) ) {
								/* translators: %s: Page URL */
								$active_filters_output[] = sprintf( __( 'URL: "%s"', 'search-analytics-insights' ), $filters['page_url'] );
							}
							if ( ! empty( $filters['username'] ) ) {
								/* translators: %s: Username */
								$active_filters_output[] = sprintf( __( 'Username: "%s"', 'search-analytics-insights' ), $filters['username'] );
							}
							if ( ! empty( $filters['no_results'] ) ) {
								$active_filters_output[] = __( 'No Results Only', 'search-analytics-insights' );
							}

							if ( empty( $active_filters_output ) ) {
								esc_html_e( 'Showing all searches', 'search-analytics-insights' );
							} else {
								echo esc_html( implode( ' | ', $active_filters_output ) );
							}
							?>
						</span>
					</div>
					<div class="vplens-filters-summary-right">
						<span class="vplens-summary-total-label"><?php esc_html_e( 'Total Searches:', 'search-analytics-insights' ); ?></span>
						<strong class="vplens-summary-total-value"><?php echo esc_html( number_format_i18n( (int) $aggregated_activity['total'] ) ); ?></strong>
					</div>
				</div>
			</div>

			<div class="vplens-summary">
				<?php $this->render_summary_card( __( 'Total Searches', 'search-analytics-insights' ), (int) $summary['total_searches'] ); ?>
				<?php $this->render_summary_card( __( 'Unique Searches', 'search-analytics-insights' ), (int) $summary['unique_searches'] ); ?>
				<?php $this->render_summary_card( __( 'No Result Searches', 'search-analytics-insights' ), (int) $summary['no_result_searches'] ); ?>
				<?php $this->render_summary_card( __( 'Search CTR', 'search-analytics-insights' ), number_format( $summary['ctr'] ?? 0, 1 ) . '%' ); ?>
			</div>

			<div class="vplens-summary">
				<div class="vplens-panel">
					<h2><?php esc_html_e( 'Searches per day', 'search-analytics-insights' ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Date', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Searches', 'search-analytics-insights' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $daily_counts ) ) : ?>
								<tr><td colspan="2"><?php esc_html_e( 'No searches found for this range.', 'search-analytics-insights' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $daily_counts as $row ) : ?>
									<tr>
										<td><?php echo esc_html( (string) $row['search_date'] ); ?></td>
										<td><?php echo esc_html( (string) $row['search_count'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="vplens-panel">
					<h2><?php esc_html_e( 'Search Performance (CTR)', 'search-analytics-insights' ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Search Keyword', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Searches', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Clicked', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Search CTR', 'search-analytics-insights' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $search_perf ) ) : ?>
								<tr><td colspan="4"><?php esc_html_e( 'No search performance data yet.', 'search-analytics-insights' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $search_perf as $row ) : ?>
									<tr>
										<td><strong><?php echo esc_html( (string) $row['search_term'] ); ?></strong></td>
										<td><?php echo esc_html( (string) $row['search_count'] ); ?></td>
										<td><?php echo esc_html( (string) $row['clicked_searches'] ); ?></td>
										<td><?php echo esc_html( number_format( $row['ctr'], 1 ) . '%' ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="vplens-panel">
					<h2><?php esc_html_e( 'No Result Searches', 'search-analytics-insights' ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Search Term', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Searches', 'search-analytics-insights' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $no_result_terms ) ) : ?>
								<tr><td colspan="2"><?php esc_html_e( 'No searches with zero results have been recorded yet.', 'search-analytics-insights' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $no_result_terms as $row ) : ?>
									<tr>
										<td><?php echo esc_html( (string) $row['search_term'] ); ?></td>
										<td><?php echo esc_html( (string) $row['search_count'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>

			<div class="vplens-summary" style="margin-top: 20px;">
				<div class="vplens-panel" style="grid-column: span 3; width: 100%;">
					<h2><?php esc_html_e( 'Most Clicked Products from Search', 'search-analytics-insights' ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Product Name', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Total Clicks', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Unique Searches With Clicks', 'search-analytics-insights' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $most_clicked_products ) ) : ?>
								<tr><td colspan="3"><?php esc_html_e( 'No product clicks recorded yet.', 'search-analytics-insights' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $most_clicked_products as $row ) : ?>
									<tr>
										<td>
											<strong><?php echo esc_html( $row['title'] ); ?></strong>
											<br />
											<span class="description" style="font-size: 0.85em; color: #5f6b7a;">
												<?php echo esc_html__( 'Product ID:', 'search-analytics-insights' ) . ' ' . absint( $row['post_id'] ); ?>
											</span>
										</td>
										<td><?php echo esc_html( (string) $row['click_count'] ); ?></td>
										<td><?php echo esc_html( (string) $row['unique_searches'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>

			<div class="vplens-summary">
				<div class="vplens-panel">
					<h2><?php esc_html_e( 'Top Pages Where Users Search', 'search-analytics-insights' ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Page Title', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Searches', 'search-analytics-insights' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $top_pages_by_title ) ) : ?>
								<tr><td colspan="2"><?php esc_html_e( 'No searches recorded by page title yet.', 'search-analytics-insights' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $top_pages_by_title as $row ) : ?>
									<tr>
										<td>
											<strong><?php echo esc_html( ! empty( $row['page_title'] ) ? $row['page_title'] : __( 'Untitled Page', 'search-analytics-insights' ) ); ?></strong>
											<?php if ( ! empty( $row['page_url'] ) ) : ?>
												<br />
												<a href="<?php echo esc_url( $row['page_url'] ); ?>" target="_blank" class="description" style="font-size: 0.85em; word-break: break-all; color: var(--vplens-muted);">
													<?php echo esc_html( wp_parse_url( $row['page_url'], PHP_URL_PATH ) ); ?>
												</a>
											<?php endif; ?>
										</td>
										<td><?php echo esc_html( (string) $row['search_count'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="vplens-panel">
					<h2><?php esc_html_e( 'Most Active Search Pages', 'search-analytics-insights' ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Page URL', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Searches', 'search-analytics-insights' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $top_pages_by_url ) ) : ?>
								<tr><td colspan="2"><?php esc_html_e( 'No searches recorded by URL yet.', 'search-analytics-insights' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $top_pages_by_url as $row ) : ?>
									<tr>
										<td>
											<a href="<?php echo esc_url( $row['page_url'] ); ?>" target="_blank" style="word-break: break-all;">
												<?php echo esc_html( $row['page_url'] ); ?>
											</a>
										</td>
										<td><?php echo esc_html( (string) $row['search_count'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="vplens-panel">
					<h2><?php esc_html_e( 'Searches by Post Type', 'search-analytics-insights' ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Post Type', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Searches', 'search-analytics-insights' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $searches_by_page_type ) ) : ?>
								<tr><td colspan="2"><?php esc_html_e( 'No searches by Post type recorded yet.', 'search-analytics-insights' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $searches_by_page_type as $row ) : ?>
									<tr>
										<td>
											<span class="vplens-page-type-badge" style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; background-color: var(--vplens-accent-soft); color: var(--vplens-accent);">
												<?php echo esc_html( ! empty( $row['page_type'] ) ? $row['page_type'] : __( 'Other / Unknown', 'search-analytics-insights' ) ); ?>
											</span>
										</td>
										<td><?php echo esc_html( (string) $row['search_count'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>

			<div class="vplens-panel vplens-table-panel">
				<h2><?php esc_html_e( 'Aggregated Search Activity', 'search-analytics-insights' ); ?></h2>
				<p><?php esc_html_e( 'Repeated searches are grouped by search term and user.', 'search-analytics-insights' ); ?></p>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Search Term', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'Search Count', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'Last Searched', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'Results', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'From', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'Username', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'search-analytics-insights' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $aggregated_activity['items'] ) ) : ?>
							<tr><td colspan="7"><?php esc_html_e( 'No search records found.', 'search-analytics-insights' ); ?></td></tr>
						<?php else : ?>
							<?php foreach ( $aggregated_activity['items'] as $record ) : ?>
								<tr>
									<td><?php echo esc_html( (string) $record['search_term'] ); ?></td>
									<td><?php echo esc_html( (string) $record['search_count'] ); ?></td>
									<td><?php echo esc_html( (string) $record['last_searched'] ); ?></td>
									<td><?php echo esc_html( (string) $record['result_count'] ); ?></td>
									<td><?php $this->render_page_column( $record ); ?></td>
									<td><?php echo esc_html( $this->get_user_label( $record ) ); ?></td>
									<td>
										<?php
										$record_id = md5( (string) $record['search_term'] . '-' . $this->get_user_label( $record ) );
										?>
										<button type="button" class="button button-small vplens-toggle-details" data-target="vplens-details-<?php echo esc_attr( $record_id ); ?>" data-show-label="<?php echo esc_attr__( 'Details', 'search-analytics-insights' ); ?>" data-hide-label="<?php echo esc_attr__( 'Hide', 'search-analytics-insights' ); ?>">
											<?php esc_html_e( 'Details', 'search-analytics-insights' ); ?>
										</button>
									</td>
								</tr>
								<?php $this->render_details_row( $record, 7 ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav">
						<div class="tablenav-pages">
							<?php
							$pagination_base = add_query_arg(
								array_filter(
									array(
										'page'        => 'vplens-analytics',
										'date_from'   => $filters['date_from'],
										'date_to'     => $filters['date_to'],
										'search_term' => $filters['search_term'],
										'no_results'  => $filters['no_results'],
										'per_page'    => $filters['per_page'],
										'page_type'   => $filters['page_type'],
										'page_title'  => $filters['page_title'],
										'page_url'    => $filters['page_url'],
										'username'    => $filters['username'],
										'paged'       => '%#%',
									),
									static function ( $value ): bool {
										return '' !== $value && null !== $value;
									}
								),
								admin_url( 'admin.php' )
							);
							?>
							<?php
							echo wp_kses_post(
								paginate_links(
									array(
										'base'      => $pagination_base,
										'format'    => '',
										'current'   => $page,
										'total'     => $total_pages,
										'prev_text' => '&laquo;',
										'next_text' => '&raquo;',
									)
								)
							);
							?>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div class="vplens-panel vplens-table-panel">
				<h2><?php esc_html_e( 'Recent Search Activity', 'search-analytics-insights' ); ?></h2>
				<p><?php esc_html_e( 'The latest 20 raw search records.', 'search-analytics-insights' ); ?></p>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Search Term', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'Date', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'Results', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'From', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'Username', 'search-analytics-insights' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'search-analytics-insights' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $recent_activity ) ) : ?>
							<tr><td colspan="6"><?php esc_html_e( 'No recent search activity found.', 'search-analytics-insights' ); ?></td></tr>
						<?php else : ?>
							<?php foreach ( $recent_activity as $record ) : ?>
								<tr>
									<td><?php echo esc_html( (string) $record['search_term'] ); ?></td>
									<td><?php echo esc_html( (string) $record['searched_at'] ); ?></td>
									<td><?php echo esc_html( (string) $record['result_count'] ); ?></td>
									<td><?php $this->render_page_column( $record ); ?></td>
									<td><?php echo esc_html( $this->get_user_label( $record ) ); ?></td>
									<td>
										<?php
										$record_id = ! empty( $record['id'] ) ? (string) $record['id'] : md5( (string) $record['search_term'] . '-' . (string) $record['searched_at'] );
										?>
										<button type="button" class="button button-small vplens-toggle-details" data-target="vplens-details-<?php echo esc_attr( $record_id ); ?>" data-show-label="<?php echo esc_attr__( 'Details', 'search-analytics-insights' ); ?>" data-hide-label="<?php echo esc_attr__( 'Hide', 'search-analytics-insights' ); ?>">
											<?php esc_html_e( 'Details', 'search-analytics-insights' ); ?>
										</button>
									</td>
								</tr>
								<?php $this->render_details_row( $record, 6 ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
				<?php
			}
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		$this->render_tabbed_page(
			'settings',
			function () {
				global $wp_settings_sections, $wp_settings_fields;
				?>
			<div class="vplens-panel">
				<h3><?php esc_html_e( 'Search Settings', 'search-analytics-insights' ); ?></h3>
				<p class="vplens-settings-description"><?php esc_html_e( 'Configure the search form, live results, sources, and analytics in one place.', 'search-analytics-insights' ); ?></p>
				<form method="post" action="options.php">
					<?php settings_fields( 'vplens_settings' ); ?>
					<?php
					if ( isset( $wp_settings_sections['vplens'] ) ) {
						$first = true;
						foreach ( (array) $wp_settings_sections['vplens'] as $section ) {
							$active_class = $first ? 'active' : '';
							echo '<div class="vplens-customizer-section ' . esc_attr( $active_class ) . '">';
							echo '<h4 class="vplens-customizer-section-title">';
							echo esc_html( $section['title'] );
							echo '<span class="dashicons dashicons-arrow-down"></span>';
							echo '</h4>';
							echo '<div class="vplens-customizer-section-content">';
							if ( $section['callback'] ) {
								call_user_func( $section['callback'], $section );
							}
							if ( isset( $wp_settings_fields['vplens'][ $section['id'] ] ) ) {
								echo '<table class="form-table" role="presentation">';
								do_settings_fields( 'vplens', $section['id'] );
								echo '</table>';
							}
							echo '</div>';
							echo '</div>';
							$first = false;
						}
					} else {
						do_settings_sections( 'vplens' );
					}
					?>
					<?php submit_button( __( 'Save Settings', 'search-analytics-insights' ) ); ?>
				</form>
			</div>
				<?php
			}
		);
	}

	/**
	 * Render the tools and data utility page.
	 *
	 * @return void
	 */
	public function render_tools_page(): void {
		$this->render_tabbed_page(
			'tools',
			function () {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$message = isset( $_GET['vplens_message'] ) ? sanitize_key( $_GET['vplens_message'] ) : '';
				?>
			<div class="vplens-tools-wrapper">
				<?php if ( 'clear_success' === $message ) : ?>
					<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'All search analytics data has been successfully cleared.', 'search-analytics-insights' ); ?></p></div>
				<?php elseif ( 'reset_success' === $message ) : ?>
					<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'All plugin settings have been successfully reset to defaults.', 'search-analytics-insights' ); ?></p></div>
				<?php endif; ?>

				<!-- Export Card -->
				<div class="vplens-panel">
					<h3><?php esc_html_e( 'Export Analytics Data', 'search-analytics-insights' ); ?></h3>
					<p><?php esc_html_e( 'Download the complete search logs database in CSV format for offline reporting or backups.', 'search-analytics-insights' ); ?></p>
					<form method="post" action="">
						<?php wp_nonce_field( 'vplens_tools_action', 'vplens_tools_nonce' ); ?>
						<input type="hidden" name="vplens_action" value="export" />
						<?php submit_button( __( 'Download CSV Export', 'search-analytics-insights' ), 'primary', 'submit_export', false ); ?>
					</form>
				</div>

				<!-- Clear Database Card -->
				<div class="vplens-panel card-destructive">
					<h3 class="destructive"><?php esc_html_e( 'Clear Search Logs', 'search-analytics-insights' ); ?></h3>
					<p><?php esc_html_e( 'Warning: This action is permanent and will completely empty the search tracking database table. This cannot be undone.', 'search-analytics-insights' ); ?></p>
					<form method="post" action="" onsubmit="return confirm('<?php echo esc_attr__( 'Are you absolutely sure you want to permanently delete all search analytics logs?', 'search-analytics-insights' ); ?>');">
						<?php wp_nonce_field( 'vplens_tools_action', 'vplens_tools_nonce' ); ?>
						<input type="hidden" name="vplens_action" value="clear" />
						<?php submit_button( __( 'Permanently Delete Logs', 'search-analytics-insights' ), 'destructive', 'submit_clear', false ); ?>
					</form>
				</div>

				<!-- Reset Settings Card -->
				<div class="vplens-panel card-destructive">
					<h3 class="destructive"><?php esc_html_e( 'Reset Plugin Settings', 'search-analytics-insights' ); ?></h3>
					<p><?php esc_html_e( 'Restore all settings on this plugin back to their factory defaults. This does not delete search logs.', 'search-analytics-insights' ); ?></p>
					<form method="post" action="" onsubmit="return confirm('<?php echo esc_attr__( 'Are you sure you want to reset all plugin settings back to defaults?', 'search-analytics-insights' ); ?>');">
						<?php wp_nonce_field( 'vplens_tools_action', 'vplens_tools_nonce' ); ?>
						<input type="hidden" name="vplens_action" value="reset" />
						<?php submit_button( __( 'Reset Settings to Defaults', 'search-analytics-insights' ), 'destructive', 'submit_reset', false ); ?>
					</form>
				</div>
			</div>
				<?php
			}
		);
	}

	/**
	 * Render the help, shortcodes, and documentation page.
	 *
	 * @return void
	 */
	public function render_help_page(): void {
		$this->render_tabbed_page(
			'help',
			function () {
				?>
			<div class="vplens-help-wrapper">
				<div class="vplens-panel">
					<h3><?php esc_html_e( 'Available Shortcodes', 'search-analytics-insights' ); ?></h3>
					<p><?php esc_html_e( 'Use the following shortcodes to add search form and search insights blocks to your pages or posts.', 'search-analytics-insights' ); ?></p>
					<table class="widefat striped vplens-shortcodes-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Shortcode', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Description', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Example Usage', 'search-analytics-insights' ); ?></th>
								<th><?php esc_html_e( 'Action', 'search-analytics-insights' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $this->get_shortcode_docs() as $shortcode ) : ?>
								<tr>
									<td><code><?php echo esc_html( $shortcode['tag'] ); ?></code></td>
									<td><?php echo esc_html( $shortcode['description'] ); ?></td>
									<td><code><?php echo esc_html( $shortcode['example'] ); ?></code></td>
									<td>
										<button
											type="button"
											class="button button-small vplens-copy-shortcode"
											data-copy-shortcode="<?php echo esc_attr( $shortcode['example'] ); ?>"
										>
											<?php esc_html_e( 'Copy', 'search-analytics-insights' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="vplens-panel">
					<h3><?php esc_html_e( 'Widget & Block Usage', 'search-analytics-insights' ); ?></h3>
					<div class="vplens-usage-grid">
						<div>
							<h4><?php esc_html_e( 'Gutenberg Block', 'search-analytics-insights' ); ?></h4>
							<p><?php esc_html_e( 'Open any post or page editor. Search for the "SearchLens Search" block in the block library and insert it. You can adjust Open Mode (Dropdown/Modal/Slide Down) and Show Label under the block sidebar panel.', 'search-analytics-insights' ); ?></p>
						</div>
						<div>
							<h4><?php esc_html_e( 'WordPress Widget', 'search-analytics-insights' ); ?></h4>
							<p><?php esc_html_e( 'Navigate to Appearance > Widgets. Add the "SearchLens Search Widget" into any sidebar or widget area. You can customize the Widget Title, icon size, toggle Open Mode, and choose whether to display text labels.', 'search-analytics-insights' ); ?></p>
						</div>
					</div>
				</div>

				<div class="vplens-panel">
					<h3><?php esc_html_e( 'Frequently Asked Questions', 'search-analytics-insights' ); ?></h3>
					<div class="vplens-faq-list">
						<div>
							<h4><?php esc_html_e( 'Does this track personal user data?', 'search-analytics-insights' ); ?></h4>
							<p><?php esc_html_e( 'The plugin is designed to be privacy-friendly. It does not collect raw IP addresses, relying on optional logged-in user IDs and anonymous session IDs to compile search counts.', 'search-analytics-insights' ); ?></p>
						</div>
						<div>
							<h4><?php esc_html_e( 'How are search sources managed?', 'search-analytics-insights' ); ?></h4>
							<p><?php esc_html_e( 'By default, search indexes pages and posts. You can select specific public post types (e.g. products, events) under the Search Settings tab.', 'search-analytics-insights' ); ?></p>
						</div>
					</div>
				</div>
			</div>
				<?php
			}
		);
	}

	/**
	 * Common tab layout wrapper for premium multi-page UI.
	 *
	 * @param string   $active_tab        Slug of the active tab.
	 * @param callable $content_renderer Callback rendering page content.
	 *
	 * @return void
	 */
	private function render_tabbed_page( string $active_tab, callable $content_renderer ): void {
		if ( ! current_user_can( Constants::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'search-analytics-insights' ) );
		}
		?>
		<div class="wrap vplens-wrap vplens-admin-wrap" data-copied-label="<?php echo esc_attr__( 'Copied', 'search-analytics-insights' ); ?>">
			<div class="vplens-admin-header">
				<h1><?php esc_html_e( 'SearchLens – Search Analytics & Insights', 'search-analytics-insights' ); ?></h1>
				<p class="vplens-admin-header-subtitle"><?php esc_html_e( 'Privacy-first search activity tracking and reporting.', 'search-analytics-insights' ); ?></p>
			</div>
			
			<h2 class="nav-tab-wrapper">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=vplens' ) ); ?>" class="nav-tab <?php echo 'dashboard' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-dashboard"></span> <?php esc_html_e( 'Dashboard', 'search-analytics-insights' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=vplens-analytics' ) ); ?>" class="nav-tab <?php echo 'analytics' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e( 'Analytics & Insights', 'search-analytics-insights' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=vplens-settings' ) ); ?>" class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Search Settings', 'search-analytics-insights' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=vplens-design' ) ); ?>" class="nav-tab <?php echo 'design' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( 'Search Form Design', 'search-analytics-insights' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=vplens-tools' ) ); ?>" class="nav-tab <?php echo 'tools' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e( 'Tools', 'search-analytics-insights' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=vplens-help' ) ); ?>" class="nav-tab <?php echo 'help' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'Help & Docs', 'search-analytics-insights' ); ?>
				</a>
			</h2>
			
			<div class="vplens-admin-content-area">
				<?php call_user_func( $content_renderer ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Normalize incoming filter values.
	 *
	 * @return array<string, mixed>
	 */
	private function get_filters(): array {
		$page     = isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$per_page = isset( $_GET['per_page'] ) ? absint( wp_unslash( $_GET['per_page'] ) ) : 10; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return array(
			'date_from'   => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'date_to'     => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'search_term' => isset( $_GET['search_term'] ) ? sanitize_text_field( wp_unslash( $_GET['search_term'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'no_results'  => isset( $_GET['no_results'] ) ? absint( wp_unslash( $_GET['no_results'] ) ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'page'        => max( 1, $page ),
			'per_page'    => max( 1, min( 100, $per_page ) ),
			'page_type'   => isset( $_GET['page_type'] ) ? sanitize_text_field( wp_unslash( $_GET['page_type'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'page_title'  => isset( $_GET['page_title'] ) ? sanitize_text_field( wp_unslash( $_GET['page_title'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'page_url'    => isset( $_GET['page_url'] ) ? sanitize_text_field( wp_unslash( $_GET['page_url'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'username'    => isset( $_GET['username'] ) ? sanitize_text_field( wp_unslash( $_GET['username'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
	}

	/**
	 * Get the display label for the user column.
	 *
	 * @param array<string, mixed> $record Search record.
	 *
	 * @return string
	 */
	private function get_user_label( array $record ): string {
		$display_name = ! empty( $record['display_name'] ) ? (string) $record['display_name'] : '';
		if ( '' !== $display_name ) {
			return $display_name;
		}

		return ! empty( $record['user_login'] ) ? (string) $record['user_login'] : __( 'Guest', 'search-analytics-insights' );
	}

	/**
	 * Get shortcode documentation rows.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function get_shortcode_docs(): array {
		return array(
			array(
				'tag'         => '[searchlens_form]',
				'description' => __( 'Displays a search form that automatically uses live AJAX search or native search depending on plugin settings.', 'search-analytics-insights' ),
				'example'     => '[searchlens_form placeholder="Search..." button_text="Search"]',
			),
			array(
				'tag'         => '[searchlens_ajax_form]',
				'description' => __( 'Displays a live AJAX search form directly on the page.', 'search-analytics-insights' ),
				'example'     => '[searchlens_ajax_form placeholder="Search..." limit="10"]',
			),
			array(
				'tag'         => '[searchlens_popular]',
				'description' => __( 'Displays the most searched terms from the analytics database.', 'search-analytics-insights' ),
				'example'     => '[searchlens_popular limit="5" show_count="true"]',
			),
			array(
				'tag'         => '[searchlens_trending]',
				'description' => __( 'Displays trending searches from the last 7 days.', 'search-analytics-insights' ),
				'example'     => '[searchlens_trending limit="5" title="Trending Searches"]',
			),
		);
	}

	/**
	 * Render a summary card.
	 *
	 * @param string     $label Label text.
	 * @param string|int $value Value text.
	 *
	 * @return void
	 */
	private function render_summary_card( string $label, $value ): void {
		?>
		<div class="vplens-card">
			<span class="vplens-card-label"><?php echo esc_html( $label ); ?></span>
			<span class="vplens-card-value"><?php echo esc_html( (string) $value ); ?></span>
		</div>
		<?php
	}

	/**
	 * Render the page context column cell.
	 *
	 * @param array<string, mixed> $record Search record.
	 *
	 * @return void
	 */
	private function render_page_column( array $record ): void {
		$page_title = ! empty( $record['page_title'] ) ? $record['page_title'] : '';
		$page_url   = ! empty( $record['page_url'] ) ? $record['page_url'] : '';
		$referrer   = ! empty( $record['referrer'] ) ? $record['referrer'] : '';
		$page_type  = ! empty( $record['page_type'] ) ? $record['page_type'] : '';

		if ( empty( $page_title ) && empty( $page_url ) ) {
			echo '<span class="description">' . esc_html__( 'Direct/Unknown', 'search-analytics-insights' ) . '</span>';
			return;
		}

		$display_title = ! empty( $page_title ) ? $page_title : __( 'Untitled Page', 'search-analytics-insights' );
		$display_path  = $page_url;
		if ( ! empty( $page_url ) ) {
			$parsed_url   = wp_parse_url( $page_url );
			$display_path = isset( $parsed_url['path'] ) ? $parsed_url['path'] : $page_url;
			if ( isset( $parsed_url['query'] ) ) {
				$display_path .= '?' . $parsed_url['query'];
			}
		}

		?>
		<div class="vplens-page-column-cell" style="display: flex; flex-direction: column; gap: 2px;">
			<div class="vplens-page-title-row" style="display: flex; align-items: center; gap: 6px;">
				<span class="dashicons dashicons-admin-page" style="font-size: 16px; width: 16px; height: 16px; color: var(--vplens-muted);" title="<?php echo esc_attr( $page_type ); ?>"></span>
				<strong class="vplens-page-title" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo esc_attr( $display_title ); ?>">
					<?php echo esc_html( $display_title ); ?>
				</strong>
			</div>
			<?php if ( ! empty( $page_url ) ) : ?>
				<a class="vplens-page-url-link" href="<?php echo esc_url( $page_url ); ?>" target="_blank" style="font-size: 0.85em; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--vplens-muted);" title="<?php echo esc_attr( $page_url ); ?>">
					<?php echo esc_html( $display_path ); ?>
				</a>
			<?php endif; ?>
			<?php if ( ! empty( $referrer ) ) : ?>
				<div class="vplens-referrer-row" style="display: flex; align-items: center; gap: 4px; font-size: 0.8em; color: var(--vplens-muted);">
					<span class="dashicons dashicons-share-alt2" style="font-size: 14px; width: 14px; height: 14px;" title="<?php echo esc_attr__( 'Referrer: ', 'search-analytics-insights' ) . esc_attr( $referrer ); ?>"></span>
					<span style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo esc_attr( $referrer ); ?>">
						<?php echo esc_html( $referrer ); ?>
					</span>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the expandable details row.
	 *
	 * @param array<string, mixed> $record  Search record.
	 * @param int                  $colspan Number of columns to span.
	 *
	 * @return void
	 */
	private function render_details_row( array $record, int $colspan ): void {
		$page_title = ! empty( $record['page_title'] ) ? $record['page_title'] : __( 'N/A', 'search-analytics-insights' );
		$page_url   = ! empty( $record['page_url'] ) ? $record['page_url'] : '';
		$referrer   = ! empty( $record['referrer'] ) ? $record['referrer'] : __( 'Direct / None', 'search-analytics-insights' );
		$page_type  = ! empty( $record['page_type'] ) ? $record['page_type'] : __( 'Other / Unknown', 'search-analytics-insights' );

		$record_id = '';
		if ( ! empty( $record['id'] ) ) {
			$record_id = (string) $record['id'];
		} elseif ( ! empty( $record['latest_id'] ) ) {
			$record_id = (string) $record['latest_id'];
		} else {
			$record_id = md5( (string) $record['search_term'] . '-' . (string) ( isset( $record['searched_at'] ) ? $record['searched_at'] : $this->get_user_label( $record ) ) );
		}
		?>
		<tr id="vplens-details-<?php echo esc_attr( $record_id ); ?>" class="vplens-details-row" style="display: none;">
			<td colspan="<?php echo esc_attr( (string) $colspan ); ?>">
				<div class="vplens-details-content" style="padding: 12px 16px; background-color: var(--vplens-surface-alt); border-top: 1px solid var(--vplens-border); border-bottom: 1px solid var(--vplens-border);">
					<div class="vplens-details-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
						<div>
							<strong><?php esc_html_e( 'Page Title:', 'search-analytics-insights' ); ?></strong>
							<div><?php echo esc_html( $page_title ); ?></div>
						</div>
						<div>
							<strong><?php esc_html_e( 'Page URL:', 'search-analytics-insights' ); ?></strong>
							<div>
								<?php if ( ! empty( $page_url ) ) : ?>
									<a href="<?php echo esc_url( $page_url ); ?>" target="_blank"><?php echo esc_html( $page_url ); ?></a>
								<?php else : ?>
									<?php echo esc_html__( 'N/A', 'search-analytics-insights' ); ?>
								<?php endif; ?>
							</div>
						</div>
						<div>
							<strong><?php esc_html_e( 'Post Type:', 'search-analytics-insights' ); ?></strong>
							<div>
								<span class="vplens-page-type-badge" style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; background-color: var(--vplens-accent-soft); color: var(--vplens-accent);">
									<?php echo esc_html( $page_type ); ?>
								</span>
							</div>
						</div>
						<div>
							<strong><?php esc_html_e( 'Referrer:', 'search-analytics-insights' ); ?></strong>
							<div style="word-break: break-all;">
								<?php if ( filter_var( $referrer, FILTER_VALIDATE_URL ) ) : ?>
									<a href="<?php echo esc_url( $referrer ); ?>" target="_blank"><?php echo esc_html( $referrer ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $referrer ); ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render the Search Form Design Customizer page.
	 *
	 * @return void
	 */
	public function render_design_page(): void {
		$settings_helper = new \VPLens\Admin\Settings();
		$design          = $settings_helper->get_design_settings();

		// Fetch existing choices and settings
		$this->render_tabbed_page(
			'design',
			function () use ( $design ) {
				$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
				?>
				<!-- Screen ID: <?php echo esc_html( $current_screen ? $current_screen->id : 'unknown' ); ?> -->
				<div class="vplens-customizer-container">
					<form method="post" action="options.php" class="vplens-customizer-form">
						<?php settings_fields( 'vplens_settings' ); ?>
						
						<!-- Keep other settings intact -->
						<?php
						$all_settings = get_option( 'vplens_settings', array() );
						foreach ( $all_settings as $section_key => $section_data ) {
							if ( 'design' === $section_key ) {
								continue;
							}
							if ( is_array( $section_data ) ) {
								foreach ( $section_data as $field_key => $field_val ) {
									if ( is_array( $field_val ) ) {
										foreach ( $field_val as $sub_key => $sub_val ) {
											echo '<input type="hidden" name="vplens_settings[' . esc_attr( $section_key ) . '][' . esc_attr( $field_key ) . '][' . esc_attr( $sub_key ) . ']" value="' . esc_attr( $sub_val ) . '" />';
										}
									} else {
										echo '<input type="hidden" name="vplens_settings[' . esc_attr( $section_key ) . '][' . esc_attr( $field_key ) . ']" value="' . esc_attr( $field_val ) . '" />';
									}
								}
							}
						}
						?>

						<div class="vplens-customizer-sidebar">
							<div class="vplens-customizer-header">
								<h3><?php esc_html_e( 'Design Customizer', 'search-analytics-insights' ); ?></h3>
								<p class="description"><?php esc_html_e( 'Customize search colors, sizing, layout, and results styles.', 'search-analytics-insights' ); ?></p>
							</div>

							<!-- Category: Search Input -->
							<div class="vplens-customizer-section active">
								<h4 class="vplens-customizer-section-title">
									<?php esc_html_e( 'Search Input', 'search-analytics-insights' ); ?>
									<span class="dashicons dashicons-arrow-down"></span>
								</h4>
								<div class="vplens-customizer-section-content">
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Background Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][input][bg_color]" value="<?php echo esc_attr( $design['input']['bg_color'] ); ?>" data-default-color="#ffffff" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Text Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][input][text_color]" value="<?php echo esc_attr( $design['input']['text_color'] ); ?>" data-default-color="#102033" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Placeholder Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][input][placeholder_color]" value="<?php echo esc_attr( $design['input']['placeholder_color'] ); ?>" data-default-color="#5f6b7a" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Border Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][input][border_color]" value="<?php echo esc_attr( $design['input']['border_color'] ); ?>" data-default-color="#c8d3df" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Border Width (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][input][border_width]" value="<?php echo esc_attr( (string) $design['input']['border_width'] ); ?>" min="0" max="20" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Border Radius (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][input][border_radius]" value="<?php echo esc_attr( (string) $design['input']['border_radius'] ); ?>" min="0" max="100" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Height (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][input][height]" value="<?php echo esc_attr( (string) $design['input']['height'] ); ?>" min="20" max="120" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Font Size (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][input][font_size]" value="<?php echo esc_attr( (string) $design['input']['font_size'] ); ?>" min="8" max="72" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Horizontal Padding (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][input][padding]" value="<?php echo esc_attr( (string) $design['input']['padding'] ); ?>" min="0" max="100" class="small-text" />
									</div>
								</div>
							</div>

							<!-- Category: Search Button -->
							<div class="vplens-customizer-section">
								<h4 class="vplens-customizer-section-title">
									<?php esc_html_e( 'Search Button', 'search-analytics-insights' ); ?>
									<span class="dashicons dashicons-arrow-down"></span>
								</h4>
								<div class="vplens-customizer-section-content">
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Background Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][button][bg_color]" value="<?php echo esc_attr( $design['button']['bg_color'] ); ?>" data-default-color="#0b7285" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Hover Background Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][button][hover_bg_color]" value="<?php echo esc_attr( $design['button']['hover_bg_color'] ); ?>" data-default-color="#0f9bb0" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Text Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][button][text_color]" value="<?php echo esc_attr( $design['button']['text_color'] ); ?>" data-default-color="#ffffff" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Button Text', 'search-analytics-insights' ); ?></label>
										<input type="text" name="vplens_settings[design][button][text]" value="<?php echo esc_attr( $design['button']['text'] ); ?>" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Width (auto, 100%, or px value)', 'search-analytics-insights' ); ?></label>
										<input type="text" name="vplens_settings[design][button][width]" value="<?php echo esc_attr( $design['button']['width'] ); ?>" class="regular-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Height (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][button][height]" value="<?php echo esc_attr( (string) $design['button']['height'] ); ?>" min="20" max="120" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Border Radius (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][button][border_radius]" value="<?php echo esc_attr( (string) $design['button']['border_radius'] ); ?>" min="0" max="100" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Font Size (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][button][font_size]" value="<?php echo esc_attr( (string) $design['button']['font_size'] ); ?>" min="8" max="72" class="small-text" />
									</div>
									<div class="vplens-control-group inline-checkbox">
										<label>
											<input type="checkbox" name="vplens_settings[design][button][show_icon]" value="1" <?php checked( ! empty( $design['button']['show_icon'] ) ); ?> />
											<?php esc_html_e( 'Show Icon inside Button', 'search-analytics-insights' ); ?>
										</label>
									</div>
								</div>
							</div>

							<!-- Category: Search Icon -->
							<div class="vplens-customizer-section">
								<h4 class="vplens-customizer-section-title">
									<?php esc_html_e( 'Search Icon (Input)', 'search-analytics-insights' ); ?>
									<span class="dashicons dashicons-arrow-down"></span>
								</h4>
								<div class="vplens-customizer-section-content">
									<div class="vplens-control-group inline-checkbox">
										<label>
											<input type="checkbox" name="vplens_settings[design][icon][show]" value="1" <?php checked( ! empty( $design['icon']['show'] ) ); ?> />
											<?php esc_html_e( 'Show Search Icon in Input', 'search-analytics-insights' ); ?>
										</label>
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Icon Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][icon][color]" value="<?php echo esc_attr( $design['icon']['color'] ); ?>" data-default-color="#5f6b7a" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Icon Size (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][icon][size]" value="<?php echo esc_attr( (string) $design['icon']['size'] ); ?>" min="6" max="120" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Icon Position', 'search-analytics-insights' ); ?></label>
										<select name="vplens_settings[design][icon][position]">
											<option value="right" <?php selected( 'right', $design['icon']['position'] ); ?>><?php esc_html_e( 'Right', 'search-analytics-insights' ); ?></option>
											<option value="left" <?php selected( 'left', $design['icon']['position'] ); ?>><?php esc_html_e( 'Left', 'search-analytics-insights' ); ?></option>
										</select>
									</div>
								</div>
							</div>

							<!-- Category: Search Form Layout -->
							<div class="vplens-customizer-section">
								<h4 class="vplens-customizer-section-title">
									<?php esc_html_e( 'Search Form Layout', 'search-analytics-insights' ); ?>
									<span class="dashicons dashicons-arrow-down"></span>
								</h4>
								<div class="vplens-customizer-section-content">
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Layout Type', 'search-analytics-insights' ); ?></label>
										<select name="vplens_settings[design][layout][type]">
											<option value="inline" <?php selected( 'inline', $design['layout']['type'] ); ?>><?php esc_html_e( 'Inline (Input + Button)', 'search-analytics-insights' ); ?></option>
											<option value="full_width" <?php selected( 'full_width', $design['layout']['type'] ); ?>><?php esc_html_e( 'Full Width (Stacked)', 'search-analytics-insights' ); ?></option>
											<option value="compact" <?php selected( 'compact', $design['layout']['type'] ); ?>><?php esc_html_e( 'Compact (Icon only toggle)', 'search-analytics-insights' ); ?></option>
										</select>
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Maximum Form Width (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][layout][width]" value="<?php echo esc_attr( (string) $design['layout']['width'] ); ?>" min="10" max="2000" class="regular-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Input/Button Gap (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][layout][gap]" value="<?php echo esc_attr( (string) $design['layout']['gap'] ); ?>" min="0" max="100" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Alignment', 'search-analytics-insights' ); ?></label>
										<select name="vplens_settings[design][layout][align]">
											<option value="left" <?php selected( 'left', $design['layout']['align'] ); ?>><?php esc_html_e( 'Left', 'search-analytics-insights' ); ?></option>
											<option value="center" <?php selected( 'center', $design['layout']['align'] ); ?>><?php esc_html_e( 'Center', 'search-analytics-insights' ); ?></option>
											<option value="right" <?php selected( 'right', $design['layout']['align'] ); ?>><?php esc_html_e( 'Right', 'search-analytics-insights' ); ?></option>
										</select>
									</div>
								</div>
							</div>

							<!-- Category: Results Dropdown -->
							<div class="vplens-customizer-section">
								<h4 class="vplens-customizer-section-title">
									<?php esc_html_e( 'Results Dropdown', 'search-analytics-insights' ); ?>
									<span class="dashicons dashicons-arrow-down"></span>
								</h4>
								<div class="vplens-customizer-section-content">
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Background Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][dropdown][bg_color]" value="<?php echo esc_attr( $design['dropdown']['bg_color'] ); ?>" data-default-color="#ffffff" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Border Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][dropdown][border_color]" value="<?php echo esc_attr( $design['dropdown']['border_color'] ); ?>" data-default-color="#d9e1ea" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Border Radius (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][dropdown][border_radius]" value="<?php echo esc_attr( (string) $design['dropdown']['border_radius'] ); ?>" min="0" max="100" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Shadow', 'search-analytics-insights' ); ?></label>
										<select name="vplens_settings[design][dropdown][shadow]">
											<option value="none" <?php selected( 'none', $design['dropdown']['shadow'] ); ?>><?php esc_html_e( 'None', 'search-analytics-insights' ); ?></option>
											<option value="low" <?php selected( 'low', $design['dropdown']['shadow'] ); ?>><?php esc_html_e( 'Low shadow', 'search-analytics-insights' ); ?></option>
											<option value="medium" <?php selected( 'medium', $design['dropdown']['shadow'] ); ?>><?php esc_html_e( 'Medium shadow', 'search-analytics-insights' ); ?></option>
											<option value="high" <?php selected( 'high', $design['dropdown']['shadow'] ); ?>><?php esc_html_e( 'High shadow', 'search-analytics-insights' ); ?></option>
										</select>
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Maximum Height (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][dropdown][max_height]" value="<?php echo esc_attr( (string) $design['dropdown']['max_height'] ); ?>" min="100" max="2000" class="regular-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Result Spacing (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][dropdown][result_spacing]" value="<?php echo esc_attr( (string) $design['dropdown']['result_spacing'] ); ?>" min="0" max="100" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Result Padding (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][dropdown][result_padding]" value="<?php echo esc_attr( (string) $design['dropdown']['result_padding'] ); ?>" min="0" max="100" class="small-text" />
									</div>
								</div>
							</div>

							<!-- Category: Product Results -->
							<div class="vplens-customizer-section">
								<h4 class="vplens-customizer-section-title">
									<?php esc_html_e( 'Product Results', 'search-analytics-insights' ); ?>
									<span class="dashicons dashicons-arrow-down"></span>
								</h4>
								<div class="vplens-customizer-section-content">
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Product Thumbnail Size (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][product][image_size]" value="<?php echo esc_attr( (string) $design['product']['image_size'] ); ?>" min="16" max="256" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Title Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][product][title_color]" value="<?php echo esc_attr( $design['product']['title_color'] ); ?>" data-default-color="#102033" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Title Font Size (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][product][title_font_size]" value="<?php echo esc_attr( (string) $design['product']['title_font_size'] ); ?>" min="8" max="72" class="small-text" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Price Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][product][price_color]" value="<?php echo esc_attr( $design['product']['price_color'] ); ?>" data-default-color="#102033" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'SKU Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][product][sku_color]" value="<?php echo esc_attr( $design['product']['sku_color'] ); ?>" data-default-color="#5f6b7a" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Category Label Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][product][category_color]" value="<?php echo esc_attr( $design['product']['category_color'] ); ?>" data-default-color="#868e96" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Stock Status Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][product][stock_color]" value="<?php echo esc_attr( $design['product']['stock_color'] ); ?>" data-default-color="#12b886" />
									</div>
								</div>
							</div>

							<!-- Category: Hover States -->
							<div class="vplens-customizer-section">
								<h4 class="vplens-customizer-section-title">
									<?php esc_html_e( 'Result Hover Style', 'search-analytics-insights' ); ?>
									<span class="dashicons dashicons-arrow-down"></span>
								</h4>
								<div class="vplens-customizer-section-content">
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Hover Background Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][hover][bg_color]" value="<?php echo esc_attr( $design['hover']['bg_color'] ); ?>" data-default-color="#f8fafc" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Hover Text Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][hover][text_color]" value="<?php echo esc_attr( $design['hover']['text_color'] ); ?>" data-default-color="#0b7285" />
									</div>
								</div>
							</div>

							<!-- Category: Badges -->
							<div class="vplens-customizer-section">
								<h4 class="vplens-customizer-section-title">
									<?php esc_html_e( 'Sale & Featured Badges', 'search-analytics-insights' ); ?>
									<span class="dashicons dashicons-arrow-down"></span>
								</h4>
								<div class="vplens-customizer-section-content">
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Sale Badge Background', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][badges][sale_bg]" value="<?php echo esc_attr( $design['badges']['sale_bg'] ); ?>" data-default-color="#e64980" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Sale Badge Text Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][badges][sale_text]" value="<?php echo esc_attr( $design['badges']['sale_text'] ); ?>" data-default-color="#ffffff" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Featured Badge Background', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][badges][featured_bg]" value="<?php echo esc_attr( $design['badges']['featured_bg'] ); ?>" data-default-color="#0b7285" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Featured Badge Text Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][badges][featured_text]" value="<?php echo esc_attr( $design['badges']['featured_text'] ); ?>" data-default-color="#ffffff" />
									</div>
								</div>
							</div>

							<!-- Category: No Results -->
							<div class="vplens-customizer-section">
								<h4 class="vplens-customizer-section-title">
									<?php esc_html_e( 'No Results Message', 'search-analytics-insights' ); ?>
									<span class="dashicons dashicons-arrow-down"></span>
								</h4>
								<div class="vplens-customizer-section-content">
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Message Text', 'search-analytics-insights' ); ?></label>
										<input type="text" name="vplens_settings[design][no_results][message]" value="<?php echo esc_attr( $design['no_results']['message'] ); ?>" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Text Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][no_results][text_color]" value="<?php echo esc_attr( $design['no_results']['text_color'] ); ?>" data-default-color="#5f6b7a" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Font Size (px)', 'search-analytics-insights' ); ?></label>
										<input type="number" name="vplens_settings[design][no_results][font_size]" value="<?php echo esc_attr( (string) $design['no_results']['font_size'] ); ?>" min="8" max="72" class="small-text" />
									</div>
								</div>
							</div>

							<!-- Category: View All Results -->
							<div class="vplens-customizer-section">
								<h4 class="vplens-customizer-section-title">
									<?php esc_html_e( 'View All Results Button', 'search-analytics-insights' ); ?>
									<span class="dashicons dashicons-arrow-down"></span>
								</h4>
								<div class="vplens-customizer-section-content">
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'View All Text', 'search-analytics-insights' ); ?></label>
										<input type="text" name="vplens_settings[design][view_all][text]" value="<?php echo esc_attr( $design['view_all']['text'] ); ?>" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Text Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][view_all][text_color]" value="<?php echo esc_attr( $design['view_all']['text_color'] ); ?>" data-default-color="#0b7285" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Background Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][view_all][bg_color]" value="<?php echo esc_attr( $design['view_all']['bg_color'] ); ?>" data-default-color="#f8fafc" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Hover Color', 'search-analytics-insights' ); ?></label>
										<input type="text" class="vplens-color-picker" name="vplens_settings[design][view_all][hover_color]" value="<?php echo esc_attr( $design['view_all']['hover_color'] ); ?>" data-default-color="#0f9bb0" />
									</div>
									<div class="vplens-control-group">
										<label><?php esc_html_e( 'Alignment', 'search-analytics-insights' ); ?></label>
										<select name="vplens_settings[design][view_all][align]">
											<option value="center" <?php selected( 'center', $design['view_all']['align'] ); ?>><?php esc_html_e( 'Center', 'search-analytics-insights' ); ?></option>
											<option value="left" <?php selected( 'left', $design['view_all']['align'] ); ?>><?php esc_html_e( 'Left', 'search-analytics-insights' ); ?></option>
											<option value="right" <?php selected( 'right', $design['view_all']['align'] ); ?>><?php esc_html_e( 'Right', 'search-analytics-insights' ); ?></option>
										</select>
									</div>
								</div>
							</div>

							<!-- Customizer Actions -->
							<div class="vplens-customizer-actions">
								<?php submit_button( __( 'Save Design', 'search-analytics-insights' ), 'primary', 'submit', false ); ?>
								<button type="button" id="vplens-reset-design-btn" class="button button-secondary"><?php esc_html_e( 'Reset to Defaults', 'search-analytics-insights' ); ?></button>
							</div>
						</div>
					</form>

					<!-- Right Column: Live Preview Area -->
					<div class="vplens-preview-panel">
						<div class="vplens-preview-header">
							<h3><?php esc_html_e( 'Live Preview', 'search-analytics-insights' ); ?></h3>
							
							<div class="vplens-preview-controls">
								<!-- Preview State Switchers -->
								<div class="vplens-preview-state-tabs">
									<button type="button" class="vplens-state-tab active" data-state="form"><?php esc_html_e( 'Form', 'search-analytics-insights' ); ?></button>
									<button type="button" class="vplens-state-tab" data-state="results"><?php esc_html_e( 'Results', 'search-analytics-insights' ); ?></button>
									<button type="button" class="vplens-state-tab" data-state="no_results"><?php esc_html_e( 'No Results', 'search-analytics-insights' ); ?></button>
									<button type="button" class="vplens-state-tab" data-state="loading"><?php esc_html_e( 'Loading', 'search-analytics-insights' ); ?></button>
								</div>

								<!-- Responsive Switcher -->
								<div class="vplens-preview-device-toggle">
									<button type="button" class="vplens-device-btn active" data-device="desktop" title="<?php esc_attr_e( 'Desktop Preview', 'search-analytics-insights' ); ?>">
										<span class="dashicons dashicons-desktop"></span>
									</button>
									<button type="button" class="vplens-device-btn" data-device="mobile" title="<?php esc_attr_e( 'Mobile Preview', 'search-analytics-insights' ); ?>">
										<span class="dashicons dashicons-smartphone"></span>
									</button>
								</div>
							</div>
						</div>

						<!-- Mockup Window Sandbox Container -->
						<div class="vplens-preview-sandbox-wrap device-desktop">
							<div class="vplens-preview-sandbox">
								<div class="vplens-preview-element-wrapper" id="vplens-preview-root">
									
									<!-- The Autocomplete Search Component Mock -->
									<div class="vplens-ajax-search">
										<div class="vplens-ajax-search-form">
											<div class="vplens-ajax-search-wrapper vplens-layout-preview">
												
												<!-- Input Wrap -->
												<div class="vplens-ajax-search-fields">
													<!-- Optional Left/Right icon -->
													<span class="vplens-input-icon vplens-preview-search-icon" style="display: flex; align-items: center; justify-content: center; position: absolute; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 2;">
														<span class="dashicons dashicons-search" aria-hidden="true"></span>
													</span>
													<input type="text" class="vplens-ajax-search-input" placeholder="<?php esc_attr_e( 'Search products...', 'search-analytics-insights' ); ?>" value="Nike" style="width: 100%; box-sizing: border-box; outline: none; transition: all 0.15s ease;" />
												</div>

												<!-- Button -->
												<?php
												$show_btn_icon = ! empty( $design['button']['show_icon'] );
												$layout_type   = $design['layout']['type'] ?? 'inline';
												$btn_classes   = 'vplens-ajax-search-button';
												if ( $show_btn_icon ) {
													$btn_classes .= ' vplens-btn-has-icon';
												}
												if ( 'compact' === $layout_type ) {
													$btn_classes .= ' vplens-btn-icon-only';
												}
												?>
												<button type="button" class="<?php echo esc_attr( $btn_classes ); ?>" style="display: flex; align-items: center; justify-content: center; font-weight: 600; cursor: pointer; transition: all 0.15s ease; border: none; box-sizing: border-box;">
													<span class="vplens-btn-icon">
														<span class="dashicons dashicons-search" aria-hidden="true"></span>
													</span>
													<span class="vplens-btn-text"><?php esc_html_e( 'Search', 'search-analytics-insights' ); ?></span>
												</button>

											</div>

											<!-- Live AJAX Dropdown Mock -->
											<div class="vplens-ajax-search-results-wrap">
												
												<!-- Loading state -->
												<div class="vplens-ajax-search-status is-loading" style="display: none;">
													<?php esc_html_e( 'Searching...', 'search-analytics-insights' ); ?>
												</div>

												<!-- Results Suggestions list -->
												<ul class="vplens-ajax-search-results" style="list-style: none; margin: 0; padding: 0;">
													
													<!-- Suggestion 1: Nike Air Max -->
													<li class="vplens-ajax-search-result vplens-ajax-search-result--product">
														<a href="#" class="vplens-ajax-search-link has-featured-image">
															<span class="vplens-ajax-search-image-wrap">
																<img class="vplens-ajax-search-image" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='3' width='18' height='18' rx='2' ry='2'></rect><circle cx='8.5' cy='8.5' r='1.5'></circle><polyline points='21 15 16 10 5 21'></polyline></svg>" alt="" />
															</span>
															<span class="vplens-ajax-search-details">
																<span class="vplens-ajax-search-title">Nike Air Max</span>
																<span class="vplens-product-badges">
																	<span class="vplens-badge vplens-badge--sale"><?php esc_html_e( 'Sale', 'search-analytics-insights' ); ?></span>
																</span>
																<span class="vplens-product-meta-row">
																	<span class="vplens-product-sku">SKU: NIKE-001</span>
																</span>
																<span class="vplens-product-price-stock-row">
																	<span class="vplens-product-price">$129.00</span>
																	<span class="vplens-stock vplens-stock--instock"><?php esc_html_e( 'In Stock', 'search-analytics-insights' ); ?></span>
																</span>
															</span>
														</a>
													</li>

													<!-- Suggestion 2: Adidas Running Shoes -->
													<li class="vplens-ajax-search-result vplens-ajax-search-result--product">
														<a href="#" class="vplens-ajax-search-link has-featured-image">
															<span class="vplens-ajax-search-image-wrap">
																<img class="vplens-ajax-search-image" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='3' width='18' height='18' rx='2' ry='2'></rect><circle cx='8.5' cy='8.5' r='1.5'></circle><polyline points='21 15 16 10 5 21'></polyline></svg>" alt="" />
															</span>
															<span class="vplens-ajax-search-details">
																<span class="vplens-ajax-search-title">Adidas Running Shoes</span>
																<span class="vplens-product-meta-row">
																	<span class="vplens-product-sku">SKU: ADIDAS-002</span>
																	<span class="vplens-product-cat"><?php esc_html_e( 'Shoes', 'search-analytics-insights' ); ?></span>
																</span>
																<span class="vplens-product-price-stock-row">
																	<span class="vplens-product-price">$99.00</span>
																</span>
															</span>
														</a>
													</li>

													<!-- Suggestion 3: Premium Running Shoe -->
													<li class="vplens-ajax-search-result vplens-ajax-search-result--product">
														<a href="#" class="vplens-ajax-search-link has-featured-image">
															<span class="vplens-ajax-search-image-wrap">
																<img class="vplens-ajax-search-image" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='3' width='18' height='18' rx='2' ry='2'></rect><circle cx='8.5' cy='8.5' r='1.5'></circle><polyline points='21 15 16 10 5 21'></polyline></svg>" alt="" />
															</span>
															<span class="vplens-ajax-search-details">
																<span class="vplens-ajax-search-title">Premium Running Shoe</span>
																<span class="vplens-product-badges">
																	<span class="vplens-badge vplens-badge--featured"><?php esc_html_e( 'Featured', 'search-analytics-insights' ); ?></span>
																</span>
																<span class="vplens-product-meta-row">
																	<span class="vplens-product-sku">SKU: RUN-003</span>
																</span>
																<span class="vplens-product-price-stock-row">
																	<span class="vplens-product-price">$149.00</span>
																</span>
															</span>
														</a>
													</li>

													<!-- Footer link -->
													<li class="vplens-ajax-search-result vplens-ajax-search-result--view-all vplens-ajax-search-all-results">
														<a href="#" class="vplens-ajax-search-link vplens-ajax-search-link--view-all vplens-ajax-search-all-link">See all results</a>
													</li>

												</ul>

											</div>

										</div>
									</div>

								</div>
							</div>
						</div>

					</div>
				</div>
				<?php
			}
		);
	}
}