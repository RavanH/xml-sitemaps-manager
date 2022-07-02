<?php
/**
 * XML Sitemaps Manager: Fixes
 *
 * Patch bugs:
 * - 404 response code on certain sitemaps. @see https://core.trac.wordpress.org/ticket/51912
 * - don't set is_home() true. @see https://core.trac.wordpress.org/ticket/51542
 * - don't execute main query. @see https://core.trac.wordpress.org/ticket/51117
 * - ignore stickyness. @see https://core.trac.wordpress.org/ticket/55633
 *
 * Add features:
 * - is_sitemap() conditional tag. @see https://core.trac.wordpress.org/ticket/51543
 * - is_sitemap_stylesheet() conditional tag for good measure.
 *
 * Improve performance:
 * - Shave off 4 database queries from post type sitemap requests.
 * - Shave off 5 database queries from the sitemap index request.
 * - Shave off N database queries from taxonomy sitemap requests, where N is the number of terms in that taxonomy.
 * - Shave off 12 database queries from user sitemap requests.
 *
 * @package XML Sitemaps Manager
 * @since 1.0
 */

if ( ! defined( 'WPINC' ) ) die;

/**
 * Remove sticky posts from the first posts sitemap.
 * This patch should not be needed after WP 6.1 release.
 *
 * @see https://core.trac.wordpress.org/ticket/55633
 *
 * @param array[] $args Query Arguments
 *
 * @return array[]
 */
function xmlsm_posts_query_args( $args ) {
	// Ignore stickyness.
	$args['ignore_sticky_posts'] = true;

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'xmlsm_posts_query_args' );

/**
 * Reduce DB queries by fetching WP_Term objects, not an array of IDs.
 * This patch should not be needed after WP 6.0 release.
 *
 * @see https://core.trac.wordpress.org/ticket/55239
 * @see https://core.trac.wordpress.org/changeset/52834
 *
 * @param array[] $args Query Arguments
 *
 * @return array[]
 */
function xmlsm_taxonomies_query_args( $args ) {
	// Set the taxonomy query 'fields' argument back to 'all' as originally intended.
	$args['fields'] = 'all';

	return $args;
}
add_filter( 'wp_sitemaps_taxonomies_query_args', 'xmlsm_taxonomies_query_args' );

if ( ! function_exists( 'wp_sitemaps_loaded' ) ) :
	/**
	 * Loads the WordPress XML Sitemap Server
	 *
	 * @see https://core.trac.wordpress.org/ticket/51912
	 *
	 * @since 1.0
	 *
	 * @param  WP       $wp       Current WordPress environment instance.
	 * @global WP_Query	$wp_query WordPress Query.
	 *
	 * @return void
	 */
	function wp_sitemaps_loaded( $wp ) {
		global $wp_query;

		/**
		 * Whether this is a Sitemap Request.
		 *
		 * @see https://core.trac.wordpress.org/ticket/51543
		 * @since 1.0
		 * @var bool
		 */
		$wp_query->is_sitemap = ! empty( $wp->query_vars['sitemap'] );

		/**
		 * Whether this is a Sitemap Stylesheet Request.
		 *
		 * @since 1.0
		 * @var bool
		 */
		$wp_query->is_sitemap_stylesheet = ! empty( $wp->query_vars['sitemap-stylesheet'] );

		if ( ! is_sitemap() && ! is_sitemap_stylesheet() ) {
			return;
		}

		// Prepare query variables.
		$query_vars = $wp_query->query_vars;
		$wp_query->query_vars = $wp->query_vars;

		// Render the sitemap.
		wp_sitemaps_get_server()->render_sitemaps();

		// Still here? Then it was an invalid sitemap request after all. Undo everything and carry on...
		$wp_query->is_sitemap = false;
		$wp_query->is_sitemap_stylesheet = false;
		$wp_query->query_vars = $query_vars;
	}
	add_action( 'parse_request', 'wp_sitemaps_loaded' );
endif;

if ( ! function_exists( 'is_sitemap' ) ) :
	/**
	 * Determines whether the query is for the sitemap.
	 *
	 * For more information on this and similar theme functions, check out
	 * the {@link https://developer.wordpress.org/themes/basics/conditional-tags/
	 * Conditional Tags} article in the Theme Developer Handbook.
	 *
	 * @see https://core.trac.wordpress.org/ticket/51543
	 *
	 * @since 1.0
	 *
	 * @global WP_Query $wp_query WordPress Query object.
	 *
	 * @return bool Whether the query is for the sitemap.
	 */
	function is_sitemap() {
		global $wp_query;

		if ( ! isset( $wp_query ) ) {
			_doing_it_wrong( __FUNCTION__, translate( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' );
			return false;
		}

		return property_exists( $wp_query, 'is_sitemap' ) ? $wp_query->is_sitemap : false;
	}
endif;

if ( ! function_exists( 'is_sitemap_stylesheet' ) ) :
	/**
	 * Determines whether the query is for the sitemap stylesheet.
	 *
	 * For more information on this and similar theme functions, check out
	 * the {@link https://developer.wordpress.org/themes/basics/conditional-tags/
	 * Conditional Tags} article in the Theme Developer Handbook.
	 *
	 * @since 1.0
	 *
	 * @global WP_Query $wp_query WordPress Query object.
	 *
	 * @return bool Whether the query is for the sitemap stylesheet.
	 */
	function is_sitemap_stylesheet() {
		global $wp_query;

		if ( ! isset( $wp_query ) ) {
			_doing_it_wrong( __FUNCTION__, translate( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' );
			return false;
		}

		return property_exists( $wp_query, 'is_sitemap_stylesheet' ) ? $wp_query->is_sitemap_stylesheet : false;
	}
endif;
