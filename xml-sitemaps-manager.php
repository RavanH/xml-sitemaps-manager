<?php
/**
 * XML Sitemaps Manager
 *
 * Plugin Name:       XML Sitemaps Manager
 * Plugin URI:        https://status301.net/wordpress-plugins/xml-sitemaps-manager/
 * Description:       Fix some bugs and add new options to manage the WordPress core XML Sitemaps. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemaps%20Manager">tip</a></strong> for continued development and support. Thanks :)
 * Text Domain:       xml-sitemaps-manager
 * Version:           0.6
 * Requires at least: 5.5
 * Requires PHP:      5.6
 * Author:            RavanH
 * Author URI:        https://status301.net/
 *
 * @package XML Sitemaps Manager
 */

defined( 'WPINC' ) || die;

define( 'XMLSM_VERSION', '0.6' );
define( 'XMLSM_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Plugin intitialization.
 *
 * Must run before priority 10 for the wp_sitemaps_add_provider filter to work.
 * Can be disabled with remove_action('init','xmlsm_init',9).
 *
 * @since 0.3
 */
function xmlsm_init() {
	global $wp_version;

	// Skip this if we're in the admin.
	if ( is_admin() ) {
		return;
	}

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
		// Include pluggable functions.
		include __DIR__ . '/src/pluggable.php';

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

	// Usage info for debugging.
	if ( WP_DEBUG ) {
		include_once __DIR__ . '/src/debugging.php';
	}

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
	/**
	 * Register settings.
	 */
	XMLSitemapsManager\Admin::register_settings();

	/**
	 * Tools actions.
	 */
	// Compatibility.
	if ( function_exists( 'pll_languages_list' ) ) {
		add_action( 'xmlsm_clear_lastmod_meta', array( 'XMLSitemapsManager\Compat\Polylang', 'clear_lastmod_meta' ) );
	}
	XMLSitemapsManager\Admin::tools_actions();

	/**
	 * Plugin action links.
	 */
	add_filter( 'plugin_action_links_' . XMLSM_BASENAME, array( 'XMLSitemapsManager\Admin', 'add_action_link' ) );
	add_filter( 'plugin_row_meta', array( 'XMLSitemapsManager\Admin', 'plugin_meta_links' ), 10, 2 );
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
	if ( 0 !== version_compare( XMLSM_VERSION, $db_version ) ) {
		include_once __DIR__ . '/upgrade.php';
	}
}

add_action( 'init', 'xmlsm_maybe_upgrade', 8 );

/**
 * XML Sitemap Manager Autoloader.
 *
 * @since 0.5
 *
 * @param string $class_name The fully-qualified class name.
 *
 * @return void
 */
function xmlsm_autoloader( $class_name ) {
	// Skip this if not in our namespace.
	if ( 0 !== strpos( $class_name, 'XMLSitemapsManager' ) ) {
		return;
	}

	// Replace namespace separators with directory separators in the relative
	// class name, prepend with class-, append with .php, build our file path.
	$class_name = str_replace( 'XMLSitemapsManager', 'src', $class_name );
	$class_name = strtolower( $class_name );
	$path_array = explode( '\\', $class_name );
	$file_name  = array_pop( $path_array );
	$file_name  = 'class-' . $file_name . '.php';
	$file       = __DIR__ . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $path_array ) . DIRECTORY_SEPARATOR . $file_name;

	// If the file exists, inlcude it.
	if ( file_exists( $file ) ) {
		include $file;
	}
}

spl_autoload_register( 'xmlsm_autoloader' );
