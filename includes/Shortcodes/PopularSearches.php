<?php
/**
 * Popular searches shortcode.
 *
 * @package SearchAnalyticsInsights
 */

namespace SearchAnalyticsInsights\Shortcodes;

use SearchAnalyticsInsights\Analytics\Service\AnalyticsService;
use SearchAnalyticsInsights\Core\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Renders a list of the most searched terms.
 */
final class PopularSearches {
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
	 * Render the shortcode output.
	 *
	 * @param array<string, mixed> $attributes Shortcode attributes.
	 *
	 * @return string
	 */
	public function render( array $attributes = array() ): string {
		$attributes = shortcode_atts(
			array(
				'limit'      => 5,
				'title'      => __( 'Popular Searches', Constants::TEXT_DOMAIN ),
				'show_count' => 'false',
			),
			$attributes,
			'search_insights_popular'
		);

		$limit      = max( 1, min( 50, absint( $attributes['limit'] ) ) );
		$title      = sanitize_text_field( (string) $attributes['title'] );
		$show_count = filter_var( $attributes['show_count'], FILTER_VALIDATE_BOOLEAN );
		$searches   = $this->analytics_service->get_top_search_terms( array(), $limit );

		ob_start();
		?>
		<div class="search-analytics-insights-shortcode search-analytics-insights-popular-searches">
			<?php if ( '' !== $title ) : ?>
				<h2 class="search-analytics-insights-shortcode-title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( empty( $searches ) ) : ?>
				<p><?php esc_html_e( 'No popular searches found yet.', Constants::TEXT_DOMAIN ); ?></p>
			<?php else : ?>
				<ul class="search-analytics-insights-popular-searches-list">
					<?php foreach ( $searches as $row ) : ?>
						<?php
						$term   = isset( $row['search_term'] ) ? (string) $row['search_term'] : '';
						$count  = isset( $row['search_count'] ) ? absint( $row['search_count'] ) : 0;
						$search = add_query_arg( 's', rawurlencode( $term ), home_url( '/' ) );
						?>
						<li class="search-analytics-insights-popular-searches-item">
							<a href="<?php echo esc_url( $search ); ?>"><?php echo esc_html( $term ); ?></a>
							<?php if ( $show_count ) : ?>
								<span class="search-analytics-insights-search-count">
									<?php
									echo esc_html(
										sprintf(
										/* translators: %d: search count */
											_n( '%d search', '%d searches', $count, Constants::TEXT_DOMAIN ),
											$count
										)
									);
									?>
								</span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php

		return (string) ob_get_clean();
	}
}