<?php
/**
 * AJAX search shortcode.
 *
 * @package VPLens
 */

namespace VPLens\Shortcodes;

use VPLens\Admin\Settings;
use VPLens\Core\Constants;
use VPLens\Widgets\SearchWidget;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the live AJAX search form.
 */
final class AjaxSearchForm {
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Plugin settings instance.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
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
				'placeholder'          => $this->settings->get_placeholder(),
				'button_text'          => $this->settings->get_button_text(),
				'limit'                => $this->settings->get_maximum_results(),
				'show_featured_images' => null,
			),
			$attributes,
			'searchlens_ajax_form'
		);

		$placeholder     = sanitize_text_field( (string) $attributes['placeholder'] );
		$button_text     = sanitize_text_field( (string) $attributes['button_text'] );
		$limit           = max( 1, absint( $attributes['limit'] ) );
		$settings        = $this->settings->get_ajax_search_settings();
		$show_images     = null === $attributes['show_featured_images']
			? $this->settings->get_show_featured_images()
			: (bool) $attributes['show_featured_images'];
		$is_ajax_enabled = $this->settings->is_ajax_search_enabled();

		wp_enqueue_style( 'dashicons' );

		wp_enqueue_style(
			'vplens-ajax-search',
			VPLENS_URL . 'assets/css/ajax-search.css',
			array(),
			Constants::VERSION
		);

		wp_enqueue_script(
			'vplens-ajax-search',
			VPLENS_URL . 'assets/js/ajax-search.js',
			array( 'vplens-frontend' ),
			Constants::VERSION,
			true
		);

		wp_localize_script(
			'vplens-ajax-search',
			'vplensAjaxSearchI18n',
			array(
				'loading'   => __( 'Searching...', 'search-analytics-insights' ),
				'empty'     => __( 'No results found.', 'search-analytics-insights' ),
				'minimum'   => __( 'Type at least 2 characters to search.', 'search-analytics-insights' ),
				'error'     => __( 'Unable to search right now.', 'search-analytics-insights' ),
				'in_stock'  => __( 'In stock', 'search-analytics-insights' ),
				'out_stock' => __( 'Out of stock', 'search-analytics-insights' ),
				'sale'      => __( 'Sale', 'search-analytics-insights' ),
				'featured'  => __( 'Featured', 'search-analytics-insights' ),
			)
		);

		ob_start();
		$wc_settings     = $this->settings->get_woocommerce_settings();
		$design          = $this->settings->get_design_settings();
		$layout_type     = $design['layout']['type'] ?? 'inline';
		$show_btn        = true;
		$btn_text        = $design['button']['text'] ?? $button_text;
		$show_input_icon = ! empty( $design['icon']['show'] );
		$icon_pos        = $design['icon']['position'] ?? 'right';
		$show_btn_icon   = ! empty( $design['button']['show_icon'] );

		$wrapper_classes = 'vplens-ajax-search-wrapper vplens-layout-' . esc_attr( $layout_type );
		$fields_classes  = 'vplens-ajax-search-fields';
		if ( $show_input_icon ) {
			$fields_classes .= ' vplens-ajax-search-fields--has-icon';
		}
		if ( ! $show_btn ) {
			$fields_classes .= ' vplens-ajax-search-fields--no-button';
		}

		$input_style = '';
		$icon_style  = 'display: flex; align-items: center; justify-content: center; position: absolute; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 2;';
		if ( $show_input_icon ) {
			if ( 'left' === $icon_pos ) {
				$icon_style  .= ' left: 16px; right: auto;';
				$input_style .= ' padding-left: 44px !important; padding-right: var(--vplens-input-padding) !important;';
			} else {
				$icon_style  .= ' right: 16px; left: auto;';
				$input_style .= ' padding-right: 44px !important; padding-left: var(--vplens-input-padding) !important;';
			}
		} else {
			$icon_style .= ' display: none;';
		}

		$allowed_tags = array(
			'svg'  => array(
				'class'       => true,
				'aria-hidden' => true,
				'focusable'   => true,
				'viewbox'     => true,
				'xmlns'       => true,
			),
			'path' => array(
				'd'    => true,
				'fill' => true,
			),
		);
		?>
		<div
			class="vplens-ajax-search"
			data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-nonce="<?php echo esc_attr( wp_create_nonce( Constants::NONCE_ACTION ) ); ?>"
			data-action="<?php echo esc_attr( Constants::AJAX_ACTION_SEARCH ); ?>"
			data-click-action="<?php echo esc_attr( Constants::AJAX_ACTION_CLICK ); ?>"
			data-min-chars="<?php echo esc_attr( (string) $settings['minimum_characters'] ); ?>"
			data-debounce="<?php echo esc_attr( (string) $this->settings->get_debounce_time() ); ?>"
			data-limit="<?php echo esc_attr( (string) $limit ); ?>"
			data-show-featured-images="<?php echo esc_attr( $show_images ? '1' : '0' ); ?>"
			data-show-post-type-label="<?php echo esc_attr( $this->settings->get_show_post_type_label() ? '1' : '0' ); ?>"
			data-loading-text="<?php echo esc_attr__( 'Searching...', 'search-analytics-insights' ); ?>"
			data-empty-text="<?php echo esc_attr( $this->settings->get_no_results_message() ); ?>"
			data-min-chars-text="<?php echo esc_attr( sprintf( /* translators: %d: minimum characters count */ __( 'Type at least %d characters to search.', 'search-analytics-insights' ), (int) $settings['minimum_characters'] ) ); ?>"
			data-error-text="<?php echo esc_attr__( 'Unable to search right now.', 'search-analytics-insights' ); ?>"
			data-wc-enabled="<?php echo esc_attr( class_exists( 'WooCommerce' ) && ! empty( $wc_settings['enabled'] ) ? '1' : '0' ); ?>"
			data-wc-show-price="<?php echo esc_attr( ! empty( $wc_settings['show_price'] ) ? '1' : '0' ); ?>"
			data-wc-show-stock="<?php echo esc_attr( ! empty( $wc_settings['show_stock'] ) ? '1' : '0' ); ?>"
			data-wc-show-sku="<?php echo esc_attr( ! empty( $wc_settings['show_sku'] ) ? '1' : '0' ); ?>"
			data-wc-show-sale="<?php echo esc_attr( ! empty( $wc_settings['show_sale_badge'] ) ? '1' : '0' ); ?>"
			data-wc-show-featured="<?php echo esc_attr( ! empty( $wc_settings['show_featured_badge'] ) ? '1' : '0' ); ?>"
			data-wc-show-view-all="<?php echo esc_attr( ! empty( $wc_settings['show_view_all'] ) ? '1' : '0' ); ?>"
			data-wc-view-all-label="<?php echo esc_attr( $design['view_all']['text'] ?? 'See all results' ); ?>"
			data-wc-show-loader="<?php echo esc_attr( ! empty( $wc_settings['show_loader'] ) ? '1' : '0' ); ?>"
			data-wc-focus-overlay="<?php echo esc_attr( ! empty( $wc_settings['focus_overlay'] ) ? '1' : '0' ); ?>"
		>
			<form class="vplens-ajax-search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" novalidate>
				<label class="screen-reader-text" for="vplens-ajax-search-input">
					<?php esc_html_e( 'Search for:', 'search-analytics-insights' ); ?>
				</label>
				<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
					<div class="<?php echo esc_attr( $fields_classes ); ?>">
						<span class="vplens-input-icon vplens-preview-search-icon" style="<?php echo esc_attr( $icon_style ); ?>">
							<span class="dashicons dashicons-search" aria-hidden="true"></span>
						</span>
						<input
							id="vplens-ajax-search-input"
							type="search"
							class="vplens-ajax-search-input"
							name="s"
							placeholder="<?php echo esc_attr( $placeholder ); ?>"
							autocomplete="off"
							aria-autocomplete="list"
							aria-expanded="false"
							aria-controls="vplens-ajax-search-results"
							style="<?php echo esc_attr( $input_style ); ?>"
						/>
					</div>
					<?php if ( $show_btn ) : ?>
						<?php
						$btn_classes = 'vplens-ajax-search-button';
						if ( $show_btn_icon ) {
							$btn_classes .= ' vplens-btn-has-icon';
						}
						if ( 'compact' === $layout_type ) {
							$btn_classes .= ' vplens-btn-icon-only';
						}
						?>
						<button type="submit" class="<?php echo esc_attr( $btn_classes ); ?>">
							<span class="vplens-btn-icon">
								<span class="dashicons dashicons-search" aria-hidden="true"></span>
							</span>
							<span class="vplens-btn-text"><?php echo esc_html( $btn_text ); ?></span>
						</button>
					<?php endif; ?>
				</div>
				<span class="vplens-ajax-search-status" style="display:none;" aria-live="polite" aria-atomic="true"></span>
			</form>
			<div class="vplens-ajax-search-results-wrap" style="display:none;">
				<ul id="vplens-ajax-search-results" class="vplens-ajax-search-results" role="listbox"></ul>
			</div>
		</div>
		<?php

		return (string) ob_get_clean();
	}
}