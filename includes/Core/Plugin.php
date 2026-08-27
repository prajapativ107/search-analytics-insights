<?php
/**
 * Main plugin orchestrator.
 *
 * @package VPLens
 */

namespace VPLens\Core;

use VPLens\API\RestController;
use VPLens\Ajax\SearchController;
use VPLens\Ajax\SearchService as AjaxSearchService;
use VPLens\Ajax\SearchTracker as AjaxSearchTracker;
use VPLens\Admin\Dashboard;
use VPLens\Admin\Assets;
use VPLens\Admin\Menu;
use VPLens\Admin\Settings;
use VPLens\Analytics\Service\AnalyticsService;
use VPLens\Database\Repository\SearchRepository;
use VPLens\Database\Schema;
use VPLens\Helpers\Privacy;
use VPLens\Tracking\Tracker;
use VPLens\Shortcodes\Registrar;
use VPLens\Shortcodes\AjaxSearchForm;
use VPLens\Shortcodes\SearchForm;
use VPLens\Shortcodes\PopularSearches;
use VPLens\Shortcodes\TrendingSearches;
use VPLens\Widgets\WidgetManager;
use VPLens\Blocks\BlockManager;

defined( 'ABSPATH' ) || exit;

/**
 * Coordinates plugin bootstrapping and runtime hook registration.
 */
final class Plugin {
	private static ?self $instance = null;
	private Container $container;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->container = new Container();
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->register_hooks();
		}

		return self::$instance;
	}

	/**
	 * Register runtime hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		add_action( 'init', array( $this, 'boot_modules' ), 5 );
		add_action( 'vplens_cleanup', array( $this, 'run_cleanup' ) );
		add_action( 'wp_head', array( $this, 'output_design_css_variables' ) );
		add_action( 'admin_head', array( $this, 'output_design_css_variables' ) );
	}



	/**
	 * Get the service container.
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Boot feature modules that are available.
	 *
	 * @return void
	 */
	public function boot_modules(): void {
		$this->maybe_upgrade_schema();

		$this->container->set(
			SearchRepository::class,
			static function (): SearchRepository {
				return new SearchRepository();
			}
		);

		$this->container->set(
			Tracker::class,
			function ( Container $container ): Tracker {
				return new Tracker( $container->get( SearchRepository::class ) );
			}
		);

		$this->container->set(
			AnalyticsService::class,
			function ( Container $container ): AnalyticsService {
				return new AnalyticsService( $container->get( SearchRepository::class ) );
			}
		);

		$this->container->set(
			Settings::class,
			static function (): Settings {
				return new Settings();
			}
		);

		$this->container->set(
			AjaxSearchService::class,
			function ( Container $container ): AjaxSearchService {
				return new AjaxSearchService(
					$container->get( SearchRepository::class ),
					$container->get( Settings::class )
				);
			}
		);

		$this->container->set(
			AjaxSearchTracker::class,
			function ( Container $container ): AjaxSearchTracker {
				return new AjaxSearchTracker( $container->get( SearchRepository::class ) );
			}
		);

		$this->container->set(
			Dashboard::class,
			function ( Container $container ): Dashboard {
				return new Dashboard( $container->get( AnalyticsService::class ) );
			}
		);

		$this->container->set(
			Menu::class,
			function ( Container $container ): Menu {
				return new Menu( $container->get( Dashboard::class ) );
			}
		);

		$this->container->set(
			Assets::class,
			static function (): Assets {
				return new Assets();
			}
		);

		$this->container->set(
			RestController::class,
			function ( Container $container ): RestController {
				return new RestController( $container->get( AnalyticsService::class ) );
			}
		);

		$this->container->set(
			SearchController::class,
			function ( Container $container ): SearchController {
				return new SearchController(
					$container->get( AjaxSearchService::class ),
					$container->get( AjaxSearchTracker::class )
				);
			}
		);

		$this->container->set(
			Privacy::class,
			static function (): Privacy {
				return new Privacy();
			}
		);

		$this->container->set(
			AjaxSearchForm::class,
			function ( Container $container ): AjaxSearchForm {
				return new AjaxSearchForm( $container->get( Settings::class ) );
			}
		);

		$this->container->set(
			SearchForm::class,
			function ( Container $container ): SearchForm {
				return new SearchForm( $container->get( Settings::class ) );
			}
		);

		$this->container->set(
			PopularSearches::class,
			function ( Container $container ): PopularSearches {
				return new PopularSearches(
					$container->get( AnalyticsService::class )
				);
			}
		);

		$this->container->set(
			TrendingSearches::class,
			function ( Container $container ): TrendingSearches {
				return new TrendingSearches(
					$container->get( AnalyticsService::class )
				);
			}
		);

		$this->container->set(
			Registrar::class,
			function ( Container $container ): Registrar {
				return new Registrar(
					$container->get( AjaxSearchForm::class ),
					$container->get( SearchForm::class ),
					$container->get( PopularSearches::class ),
					$container->get( TrendingSearches::class ),
					$container->get( Settings::class )
				);
			}
		);

		$this->container->set(
			WidgetManager::class,
			function ( Container $container ): WidgetManager {
				return new WidgetManager( $container->get( Settings::class ) );
			}
		);

		$this->container->set(
			BlockManager::class,
			static function (): BlockManager {
				return new BlockManager();
			}
		);

		$this->container->get( Tracker::class )->register_hooks();
		$this->container->get( RestController::class )->register_hooks();
		$this->container->get( SearchController::class )->register_hooks();
		$this->container->get( Privacy::class )->register_hooks();
		$this->container->get( Registrar::class )->register_hooks();
		$this->container->get( WidgetManager::class )->register_hooks();
		$this->container->get( BlockManager::class )->register_hooks();
		$this->container->get( Settings::class )->register_hooks();

		if ( is_admin() ) {
			$this->container->get( Menu::class )->register_hooks();
			$this->container->get( Assets::class )->register_hooks();
		}

		do_action( 'vplens_container_ready', $this->container );
	}

	/**
	 * Upgrade the schema when the plugin version changes.
	 *
	 * @return void
	 */
	private function maybe_upgrade_schema(): void {
		$current_version = (string) get_option( Constants::OPTION_PLUGIN_VERSION, '' );

		if ( Constants::VERSION === $current_version ) {
			return;
		}

		Schema::create_table();
		update_option( Constants::OPTION_SCHEMA_VERSION, Constants::VERSION, true );
		update_option( Constants::OPTION_PLUGIN_VERSION, Constants::VERSION, true );
	}


	/**
	 * Run the retention cleanup scheduled cron job.
	 *
	 * @return void
	 */
	public function run_cleanup(): void {
		$settings           = $this->container->has( Settings::class ) ? $this->container->get( Settings::class ) : new Settings();
		$analytics_settings = $settings->get_analytics_settings();
		$retention_days     = isset( $analytics_settings['search_retention_period'] ) ? absint( $analytics_settings['search_retention_period'] ) : 30;

		if ( $retention_days <= 0 ) {
			return;
		}

		$repository = $this->container->has( SearchRepository::class ) ? $this->container->get( SearchRepository::class ) : new SearchRepository();
		$repository->delete_older_than( $retention_days );
	}

	/**
	 * Output the custom CSS variables for the search form design settings.
	 *
	 * @return void
	 */
	public function output_design_css_variables(): void {
		$settings_helper = $this->container->has( Settings::class ) ? $this->container->get( Settings::class ) : new Settings();
		$design          = $settings_helper->get_design_settings();

		// Convert dropdown shadow setting to box-shadow value
		$shadow_val = 'none';
		if ( 'low' === $design['dropdown']['shadow'] ) {
			$shadow_val = '0 2px 8px rgba(0, 0, 0, 0.08)';
		} elseif ( 'medium' === $design['dropdown']['shadow'] ) {
			$shadow_val = '0 8px 30px rgba(0, 0, 0, 0.12)';
		} elseif ( 'high' === $design['dropdown']['shadow'] ) {
			$shadow_val = '0 15px 50px rgba(0, 0, 0, 0.18)';
		}

		// Ensure layout gap defaults to 0 if not set
		$gap_val = isset( $design['layout']['gap'] ) ? absint( $design['layout']['gap'] ) . 'px' : '12px';

		?>
		<style id="vplens-design-vars">
			:root {
				--vplens-input-bg: <?php echo esc_attr( $design['input']['bg_color'] ); ?>;
				--vplens-input-text: <?php echo esc_attr( $design['input']['text_color'] ); ?>;
				--vplens-input-placeholder: <?php echo esc_attr( $design['input']['placeholder_color'] ); ?>;
				--vplens-input-border-color: <?php echo esc_attr( $design['input']['border_color'] ); ?>;
				--vplens-input-border-width: <?php echo esc_attr( (string) $design['input']['border_width'] ); ?>px;
				--vplens-input-border-radius: <?php echo esc_attr( (string) $design['input']['border_radius'] ); ?>px;
				--vplens-input-height: <?php echo esc_attr( (string) $design['input']['height'] ); ?>px;
				--vplens-input-font-size: <?php echo esc_attr( (string) $design['input']['font_size'] ); ?>px;
				--vplens-input-padding: <?php echo esc_attr( (string) $design['input']['padding'] ); ?>px;

				--vplens-button-bg: <?php echo esc_attr( $design['button']['bg_color'] ); ?>;
				--vplens-button-hover-bg: <?php echo esc_attr( $design['button']['hover_bg_color'] ); ?>;
				--vplens-button-text: <?php echo esc_attr( $design['button']['text_color'] ); ?>;
				--vplens-button-width: <?php echo esc_attr( (string) $design['button']['width'] ); ?>;
				--vplens-button-height: <?php echo esc_attr( (string) $design['button']['height'] ); ?>px;
				--vplens-button-border-radius: <?php echo esc_attr( (string) $design['button']['border_radius'] ); ?>px;
				--vplens-button-font-size: <?php echo esc_attr( (string) $design['button']['font_size'] ); ?>px;

				--vplens-icon-color: <?php echo esc_attr( $design['icon']['color'] ); ?>;
				--vplens-icon-size: <?php echo esc_attr( (string) $design['icon']['size'] ); ?>px;

				--vplens-layout-width: <?php echo esc_attr( (string) $design['layout']['width'] ); ?>px;
				--vplens-layout-gap: <?php echo esc_attr( $gap_val ); ?>;

				--vplens-dropdown-bg: <?php echo esc_attr( $design['dropdown']['bg_color'] ); ?>;
				--vplens-dropdown-border-color: <?php echo esc_attr( $design['dropdown']['border_color'] ); ?>;
				--vplens-dropdown-border-radius: <?php echo esc_attr( (string) $design['dropdown']['border_radius'] ); ?>px;
				--vplens-dropdown-shadow: <?php echo esc_attr( $shadow_val ); ?>;
				--vplens-dropdown-max-height: <?php echo esc_attr( (string) $design['dropdown']['max_height'] ); ?>px;
				--vplens-dropdown-result-spacing: <?php echo esc_attr( (string) $design['dropdown']['result_spacing'] ); ?>px;
				--vplens-dropdown-result-padding: <?php echo esc_attr( (string) $design['dropdown']['result_padding'] ); ?>px;

				--vplens-product-image-size: <?php echo esc_attr( (string) $design['product']['image_size'] ); ?>px;
				--vplens-product-title-color: <?php echo esc_attr( $design['product']['title_color'] ); ?>;
				--vplens-product-title-font-size: <?php echo esc_attr( (string) $design['product']['title_font_size'] ); ?>px;
				--vplens-product-price-color: <?php echo esc_attr( $design['product']['price_color'] ); ?>;
				--vplens-product-sku-color: <?php echo esc_attr( $design['product']['sku_color'] ); ?>;
				--vplens-product-category-color: <?php echo esc_attr( $design['product']['category_color'] ); ?>;
				--vplens-product-stock-color: <?php echo esc_attr( $design['product']['stock_color'] ); ?>;

				--vplens-hover-bg: <?php echo esc_attr( $design['hover']['bg_color'] ); ?>;
				--vplens-hover-text: <?php echo esc_attr( $design['hover']['text_color'] ); ?>;

				--vplens-badge-sale-bg: <?php echo esc_attr( $design['badges']['sale_bg'] ); ?>;
				--vplens-badge-sale-text: <?php echo esc_attr( $design['badges']['sale_text'] ); ?>;
				--vplens-badge-featured-bg: <?php echo esc_attr( $design['badges']['featured_bg'] ); ?>;
				--vplens-badge-featured-text: <?php echo esc_attr( $design['badges']['featured_text'] ); ?>;

				--vplens-no-results-color: <?php echo esc_attr( $design['no_results']['text_color'] ); ?>;
				--vplens-no-results-font-size: <?php echo esc_attr( (string) $design['no_results']['font_size'] ); ?>px;

				--vplens-view-all-text: <?php echo esc_attr( $design['view_all']['text_color'] ); ?>;
				--vplens-view-all-bg: <?php echo esc_attr( $design['view_all']['bg_color'] ); ?>;
				--vplens-view-all-hover: <?php echo esc_attr( $design['view_all']['hover_color'] ); ?>;
			}
		</style>
		<?php
	}
}
