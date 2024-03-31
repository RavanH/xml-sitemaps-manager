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
	 * Plugin intitialization.
	 * Must run before priority 10 for the wp_sitemaps_add_provider filter to work.
	 *
	 * @since 0.3
	 */
	public static function front() {
		self::maybe_upgrade();

		// Load only on front and when backward compatibility action exists.
		if ( ! is_admin() || ! has_action( 'init', 'xmlsm_init' ) ) {
			return;
		}

		/*
		 * Make sure sitemaps are enabled.
		 */
		if ( ! get_option( 'xmlsm_sitemaps_enabled', true ) ) {
			// Disable all sitemaps.
			add_action( 'wp_sitemaps_enabled', '__return_false' );

			// And skip the rest.
			return;
		}

		/**
		 * XML Sitemaps Manager: Fixes
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
			global $wp_version;

			// Include pluggable functions.
			include __DIR__ . '/pluggable.php';

			add_action( 'parse_request', 'wp_sitemaps_loaded' );

			if ( version_compare( $wp_version, '6.1', '<' ) ) {
				add_filter( 'wp_sitemaps_posts_query_args', array( 'XMLSitemapsManager\Fixes', 'posts_query_args' ) );
			}
			if ( version_compare( $wp_version, '6.0', '<' ) ) {
				add_filter( 'wp_sitemaps_taxonomies_query_args', array( 'XMLSitemapsManager\Fixes', 'taxonomies_query_args' ) );
			}
		}

		// Maximum URLs per sitemap.
		add_filter( 'wp_sitemaps_max_urls', array( 'XMLSitemapsManager\Core', 'max_urls' ), 10, 2 );
		// Exclude sitemap providers.
		add_filter( 'wp_sitemaps_add_provider', array( 'XMLSitemapsManager\Core', 'exclude_providers' ), 10, 2 );
		// Exclude post types. TODO Fix.
		add_filter( 'wp_sitemaps_post_types', array( 'XMLSitemapsManager\Core', 'exclude_post_types' ) );
		// Exclude taxonomies. TODO Fix.
		add_filter( 'wp_sitemaps_taxonomies', array( 'XMLSitemapsManager\Core', 'exclude_taxonomies' ) );
		// Filter stylesheet.
		add_filter( 'wp_sitemaps_stylesheet_css', array( 'XMLSitemapsManager\Core', 'stylesheet' ) );

		/**
		 * Add sitemaps.
		 */

		// TODO.

		/**
		 * Add lastmod.
		 */
		if ( get_option( 'xmlsm_lastmod' ) ) {
			// Add lastmod to the index.
			add_filter( 'wp_sitemaps_index_entry', array( 'XMLSitemapsManager\Lastmod', 'index_entry' ), 10, 4 );
			add_filter( 'wp_sitemaps_posts_query_args', array( 'XMLSitemapsManager\Lastmod', 'posts_query_args' ) );
			// To post entries.
			add_filter( 'wp_sitemaps_posts_entry', array( 'XMLSitemapsManager\Lastmod', 'posts_entry' ), 10, 3 );
			add_filter( 'wp_sitemaps_posts_show_on_front_entry', array( 'XMLSitemapsManager\Lastmod', 'posts_show_on_front_entry' ) );
			// To term entries.
			add_filter( 'wp_sitemaps_taxonomies_entry', array( 'XMLSitemapsManager\Lastmod', 'taxonomies_entry' ), 10, 4 );
			add_action( 'transition_post_status', array( 'XMLSitemapsManager\Lastmod', 'update_term_modified_meta' ), 10, 3 );
			add_filter( 'wp_sitemaps_taxonomies_query_args', array( 'XMLSitemapsManager\Lastmod', 'taxonomies_query_args' ) );
			// To user entries.
			add_filter( 'wp_sitemaps_users_entry', array( 'XMLSitemapsManager\Lastmod', 'users_entry' ), 10, 2 );
			add_action( 'transition_post_status', array( 'XMLSitemapsManager\Lastmod', 'update_user_modified_meta' ), 10, 3 );
			add_filter( 'wp_sitemaps_users_query_args', array( 'XMLSitemapsManager\Lastmod', 'users_query_args' ) );
			// Compatibility.
			if ( function_exists( 'pll_languages_list' ) ) {
				add_filter( 'xmlsm_index_entry_subtype', array( 'XMLSitemapsManager\Compat\Polylang', 'index_entry_subtype' ) );
				add_filter( 'xmlsm_lastmod_user_meta_key', array( 'XMLSitemapsManager\Compat\Polylang', 'lastmod_meta_key' ), 10, 2 );
				add_filter( 'xmlsm_lastmod_index_entry', array( 'XMLSitemapsManager\Compat\Polylang', 'lastmod_index_entry' ), 10, 3 );
			}
		}

		// Usage info for debugging.
		if ( WP_DEBUG ) {
			include_once __DIR__ . '/debugging.php';
		}
	}

	/**
	 * Plugin admin intitialization.
	 *
	 * @since 0.3
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
		// Compatibility.
		if ( function_exists( 'pll_languages_list' ) ) {
			add_action( 'xmlsm_clear_lastmod_meta', array( 'XMLSitemapsManager\Compat\Polylang', 'clear_lastmod_meta' ) );
		}
		Admin::tools_actions();

		/**
		 * Plugin action links.
		 */
		add_filter( 'plugin_action_links_' . XMLSM_BASENAME, array( 'XMLSitemapsManager\Admin', 'add_action_link' ) );
		add_filter( 'plugin_row_meta', array( 'XMLSitemapsManager\Admin', 'plugin_meta_links' ), 10, 2 );
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
