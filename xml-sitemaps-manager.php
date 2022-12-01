<?php
/*
Plugin Name: XML Sitemaps Manager
Plugin URI: https://status301.net/wordpress-plugins/xml-sitemaps-manager/
Description: Fix some bugs and add new options to manage the WordPress core XML Sitemaps. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemaps%20Manager">tip</a></strong> for continued development and support. Thanks :)
Text Domain: xml-sitemaps-manager
Version: 0.6-alpha3
Requires at least: 5.5
Requires PHP: 5.6
Author: RavanH
Author URI: https://status301.net/
*/

defined( 'WPINC' ) || die;

define( 'WPSM_VERSION', '0.6-alpha3' );

/**
 * Plugin intitialization.
 *
 * Must run before priority 10 for the wp_sitemaps_add_provider filter to work.
 * Can be disabled with remove_action('init','xmlsm_init',9).
 *
 * @since 0.3
 */
function xmlsm_init() {
	// Skip this if we're in the admin.
	if ( is_admin() ) {
		return;
	}

	if ( get_option( 'xmlsm_sitemaps_enabled', true ) ) {

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
		 *   @see https://core.trac.wordpress.org/ticket/55239 (pre-6.0)
		 * - Shave off 12 database queries from user sitemap requests.
		 *
		 * @package XML Sitemaps Manager
		 * @since 1.0
		 */
		if ( get_option( 'xmlsm_sitemaps_fixes', true ) ) {
			// Include pluggable functions.
			include __DIR__ . '/src/pluggable.php';

			if ( ! function_exists( 'pll_languages_list' ) ) {
				add_action( 'parse_request', 'wp_sitemaps_loaded' );
			}
			global $wp_version;
			if ( version_compare( $wp_version, '6.1', '<' ) ) {
				add_filter( 'wp_sitemaps_posts_query_args', array( '\XMLSitemapsManager\Fixes', 'posts_query_args' ) );
			}
			if ( version_compare( $wp_version, '6.0', '<' ) ) {
				add_filter( 'wp_sitemaps_taxonomies_query_args', array( '\XMLSitemapsManager\Fixes', 'taxonomies_query_args' ) );
			}
		}

		// Maximum URLs per sitemap.
		add_filter( 'wp_sitemaps_max_urls',       array( '\XMLSitemapsManager\Core', 'max_urls' ),          10, 2 );
		// Exclude sitemap providers.
		add_filter( 'wp_sitemaps_add_provider',   array( '\XMLSitemapsManager\Core', 'exclude_providers' ), 10, 2 );
		// Exclude post types. TODO Fix
		add_filter( 'wp_sitemaps_post_types',     array( '\XMLSitemapsManager\Core', 'exclude_post_types' )       );
		// Exclude taxonomies. TODO Fix
		add_filter( 'wp_sitemaps_taxonomies',     array( '\XMLSitemapsManager\Core', 'exclude_taxonomies' )       );
		// Filter stylesheet.
		add_filter( 'wp_sitemaps_stylesheet_css', array( '\XMLSitemapsManager\Core', 'stylesheet' )               );

		// Usage info for debugging.
		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			add_action( 'shutdown', array( '\XMLSitemapsManager\Core', 'usage' ) );
		}

		/**
		 * Add sitemaps.
		 */
		// TODO
		// Maybe add dedicated Media sitemap if image tags are (still) not possible OR completely replace the renderer?

		// TODO
		// add custom post type root pages?
		// Maybe with wp_sitemaps_posts_pre_url_list (replacing the whole posts provider url_list);
			/* $post_type_archive_url = get_post_type_archive_link( $post_type );
			if ( $post_type_archive_url ) {
				$url_list[] = array( 'loc' => $post_type_archive_url );
			}*/

		/**
		 * Add lastmod.
		 */
		if ( get_option( 'xmlsm_lastmod' ) ) {
			// Add lastmod to the index.
			add_filter( 'wp_sitemaps_index_entry',               array( '\XMLSitemapsManager\Lastmod', 'index_entry' ),               10, 4 );
			// To post entries.
			add_filter( 'wp_sitemaps_posts_entry',               array( '\XMLSitemapsManager\Lastmod', 'posts_entry' ),               10, 3 );
			add_filter( 'wp_sitemaps_posts_show_on_front_entry', array( '\XMLSitemapsManager\Lastmod', 'posts_show_on_front_entry' )        );
			add_filter( 'wp_sitemaps_posts_query_args',          array( '\XMLSitemapsManager\Lastmod', 'posts_query_args' )                 );
			// To term entries.
			add_filter( 'wp_sitemaps_taxonomies_entry',          array( '\XMLSitemapsManager\Lastmod', 'taxonomies_entry' ),          10, 4 );
			add_action( 'transition_post_status',                array( '\XMLSitemapsManager\Lastmod', 'update_term_modified_meta' ), 10, 3 );
			add_filter( 'wp_sitemaps_taxonomies_query_args',     array( '\XMLSitemapsManager\Lastmod', 'taxonomies_query_args' )            );
			// To user entries.
			add_filter( 'wp_sitemaps_users_entry',               array( '\XMLSitemapsManager\Lastmod', 'users_entry' ),               10, 2 );
			add_action( 'transition_post_status',                array( '\XMLSitemapsManager\Lastmod', 'update_user_modified_meta' ), 10, 3 );
			add_filter( 'wp_sitemaps_users_query_args',          array( '\XMLSitemapsManager\Lastmod', 'users_query_args' )                 );
			// Compatibility.
			if ( function_exists( 'pll_languages_list' ) ) {
				include_once __DIR__ . '/src/compat/polylang.php';
				add_filter( 'xmlsm_index_entry_subtype',   'xmlsm_polylang_index_entry_subtype'        );
				add_filter( 'xmlsm_lastmod_user_meta_key', 'xmlsm_polylang_lastmod_meta_key',    10, 2 );
				add_filter( 'xmlsm_lastmod_index_entry',   'xmlsm_polylang_lastmod_index_entry', 10, 3 );
			}
		}

	} else {

		// Disable all sitemaps.
		add_action( 'wp_sitemaps_enabled', '__return_false' );

	}
}
add_action( 'init', 'xmlsm_init', 9 );

/**
 * Plugin admin intitialization.
 *
 * Can be disabled with remove_action('admin_init','xmlsm_admin_init').
 *
 * @since 0.3
 */
function xmlsm_admin_init() {
	define( 'WPSM_BASENAME', plugin_basename(__FILE__) );

	/**
	 * Register settings.
	 */
	\XMLSitemapsManager\Admin::register_settings();

	/**
	 * Tools actions.
	 */
	// Compatibility.
	if ( function_exists( 'pll_languages_list' ) ) {
		include_once __DIR__ . '/src/compat/polylang.php';
		add_action( 'xmlsm_clear_lastmod_meta',    'xmlsm_polylang_clear_lastmod_meta' );
	}
	\XMLSitemapsManager\Admin::tools_actions();

	/**
	 * Plugin action links.
	 */
	add_filter( 'plugin_action_links_' . WPSM_BASENAME, array( '\XMLSitemapsManager\Admin', 'add_action_link' )         );
	add_filter( 'plugin_row_meta',                      array( '\XMLSitemapsManager\Admin', 'plugin_meta_links' ), 10, 2);
}
add_action( 'admin_init', 'xmlsm_admin_init' );

/**
 * Plugin updater.
 *
 * @since 0.3
 */
function xmlsm_maybe_upgrade() {
	// Maybe upgrade or install.
	$db_version = get_option( 'xmlsm_version', '0' );
	if ( 0 !== version_compare( WPSM_VERSION, $db_version ) ) {
		include_once __DIR__ . '/upgrade.php';
	}
}
add_action(	'init',	'xmlsm_maybe_upgrade', 8 );

/**
 * XML Sitemap Manager Autoloader.
 *
 * @since 0.5
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
function xmlsm_autoloader( $class ) {
	// Skip this if not in our namespace.
	if ( 0 !== strpos( $class, 'XMLSitemapsManager' ) ) {
		return;
	}

    // Replace namespace separators with directory separators in the relative
    // class name, append with .php
    $class_path = str_replace( array( 'XMLSitemapsManager\\', '\\' ), array( '', '/' ), $class);

    $file =  __DIR__ . '/src/' . $class_path . '.php';
    // if the file exists, require it
    if ( file_exists( $file ) ) {
        require $file;
    }
}
spl_autoload_register( 'xmlsm_autoloader' );
