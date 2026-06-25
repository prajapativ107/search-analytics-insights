<?php
/**
 * Plugin autoloader.
 *
 * @package SearchAnalyticsInsights
 */

namespace SearchAnalyticsInsights\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Lightweight PSR-4 style autoloader for the plugin namespace.
 */
final class Autoloader {
	private const NAMESPACE_PREFIX = 'SearchAnalyticsInsights\\';

	/**
	 * Register the autoloader.
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register( array( self::class, 'autoload' ) );
	}

	/**
	 * Load a class file if it belongs to this plugin namespace.
	 *
	 * @param string $class Class name.
	 *
	 * @return void
	 */
	public static function autoload( string $class ): void {
		if ( 0 !== strpos( $class, self::NAMESPACE_PREFIX ) ) {
			return;
		}

		$relative_class = substr( $class, strlen( self::NAMESPACE_PREFIX ) );
		$relative_path  = str_replace( '\\', '/', $relative_class );
		$file_path      = SEARCH_ANALYTICS_INSIGHTS_PATH . 'includes/' . $relative_path . '.php';

		if ( is_readable( $file_path ) ) {
			require_once $file_path;
		}
	}
}
