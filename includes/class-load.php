<?php
/**
 * XML Sitemaps Manager Core class
 *
 * @package XML Sitemap Manager
 * @since 0.1
 */

namespace XMLSitemapsManager;

/**
 * Sitemap core class.
 *
 * @since 0.1
 */
class Load {

	/**
	 * Plugin front end intitialization.
	 * Must run before priority 10 for the wp_sitemaps_add_provider filter to work.
	 *
	 * @since 0.7
	 */
	public static function front() {
		// Maybe upgrade.
		self::maybe_upgrade();

		// Load only on front and when backward compatibility action exists.
		if ( is_admin() || ! has_action( 'init', 'xmlsm_init' ) ) {
			return;
		}

		/*
		 * Make sure sitemaps are enabled. If not, then...
		 */
		if ( ! get_option( 'xmlsm_sitemaps_enabled', true ) ) {
			// Disable all sitemaps.
			add_action( 'wp_sitemaps_enabled', '__return_false' );

			// And abort.
			return;
		}

		/*
		 * Load Core Module.
		 */
		Modules\Core::load();

		/*
		 * Load Fixes Module if activated.
		 *
		 * Patch bugs:
		 * - 404 response code on certain sitemaps. @see https://core.trac.wordpress.org/ticket/51912
		 * - don't set is_home() true. @see https://core.trac.wordpress.org/ticket/51542
		 * - don't execute main query. @see https://core.trac.wordpress.org/ticket/51117
		 * - ignore stickyness. @see https://core.trac.wordpress.org/ticket/55633 (pre-6.1)
		 *
		 * Add features:
		 * - is_sitemap() conditional tag. @see https://core.trac.wordpress.org/ticket/51543
		 * - is_sitemap_stylesheet() conditional tag for good measure.
		 *
		 * Improve performance:
		 * - Shave off 4 database queries from post type sitemap requests.
		 * - Shave off 5 database queries from the sitemap index request.
		 * - Shave off N database queries from taxonomy sitemap requests, where N is the number of terms in that taxonomy.
		 *   See https://core.trac.wordpress.org/ticket/55239 (pre-6.0)
		 * - Shave off 12 database queries from user sitemap requests.
		 *
		 * @package XML Sitemaps Manager
		 * @since 1.0
		 */
		if ( get_option( 'xmlsm_sitemaps_fixes', true ) ) {
			Modules\Fixes::load();
		}

		/*
		 * Load Lastmod Module if activated.
		 */
		if ( get_option( 'xmlsm_lastmod' ) ) {
			Modules\Lastmod::load();
		}

		/*
		 * Compatibility.
		 */
		if ( function_exists( 'pll_languages_list' ) ) {
			Compat\Polylang::front();
		}

		/*
		 * Usage info for debugging.
		 */
		if ( WP_DEBUG && WP_DEBUG_DISPLAY ) {
			include_once __DIR__ . '/debugging.php';
		}
	}

	/**
	 * Plugin admin intitialization.
	 *
	 * @since 0.7
	 */
	public static function admin() {
		// Load only when backward compatibility action exists.
		if ( ! has_action( 'init', 'xmlsm_init' ) ) {
			return;
		}

		/**
		 * Register settings.
		 */
		Admin::register_settings();

		/**
		 * Tools actions.
		 */
		Admin::tools_actions();

		/**
		 * Plugin action links.
		 */
		add_filter( 'plugin_action_links_' . XMLSM_BASENAME, array( __NAMESPACE__ . '\Admin', 'add_action_link' ) );
		add_filter( 'plugin_row_meta', array( __NAMESPACE__ . '\Admin', 'plugin_meta_links' ), 10, 2 );

		/*
		 * Compatibility.
		 */
		if ( function_exists( 'pll_languages_list' ) ) {
			Compat\Polylang::admin();
		}
	}

	/**
	 * Plugin updater.
	 *
	 * @since 0.3
	 */
	public static function maybe_upgrade() {
		$db_version = get_option( 'xmlsm_version', '0' );

		// Maybe upgrade or install.
		if ( 0 !== version_compare( XMLSM_VERSION, $db_version ) ) {
			include_once __DIR__ . '/upgrade.php';
		}
	}
}
