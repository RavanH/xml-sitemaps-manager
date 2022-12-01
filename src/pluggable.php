<?php

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

		if ( ! $wp_query->is_sitemap && ! $wp_query->is_sitemap_stylesheet ) {
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
