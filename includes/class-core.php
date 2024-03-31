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
class Core {

	/**
	 * Filter maximum urls per sitemap. Hooked into wp_sitemaps_max_urls filter.
	 *
	 * @since 0.1
	 *
	 * @param  int    $max_urls    Maximum numer of URLs.
	 * @param  string $object_type Object type.
	 *
	 * @return int    $max_urls
	 */
	public static function max_urls( $max_urls, $object_type = 'post' ) {
		$max = \get_option( 'xmlsm_max_urls' );

		// Optionally split mas_urls per object type 'post', 'term' or 'user'.
		if ( \is_array( $max ) && ! empty( $max[ $object_type ] ) ) {
			$max = $max[ $object_type ];
		}

		return \is_numeric( $max ) && $max > 0 ? $max : $max_urls;
	}

	/**
	 * Filter sitemap providers. Hooked into wp_sitemaps_add_provider filter.
	 *
	 * @since 0.1
	 *
	 * @param  obj    $provider Sitemap provider.
	 * @param  string $name     Sitemap name.
	 *
	 * @return false|obj $provider or false if disabled
	 */
	public static function exclude_providers( $provider, $name ) {
		$enabled = (array) \get_option( 'xmlsm_sitemap_providers', array() );

		return \in_array( $name, $enabled, true ) ? $provider : false;
	}

	/**
	 * Filter post types. Hooked into wp_sitemaps_post_types filter.
	 *
	 * @since 0.1
	 *
	 * @param  array $post_types Post types array.
	 *
	 * @return array $post_types
	 */
	public static function exclude_post_types( $post_types ) {
		$disabled_subtypes = (array) \get_option( 'xmlsm_disabled_subtypes', array() );
		$exclude           = \array_key_exists( 'posts', $disabled_subtypes ) ? (array) $disabled_subtypes['posts'] : array();

		foreach ( $post_types as $post_type => $object ) {
			if ( \in_array( $post_type, $exclude, true ) ) {
				unset( $post_types[ $post_type ] );
			}
		}

		return $post_types;
	}

	/**
	 * Filter post query arguments. Hooked into wp_sitemaps_posts_query_args filter.
	 *
	 * @since 0.1
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array $args
	 */
	public static function posts_query_args( $args ) {
		/**
		 * Allow exclusion of individual posts via meta data.
		 */
		if ( \get_option( 'xmlsm_exclude_posts' ) ) {
			// Exclude posts based on meta data.
			$args['meta_query'] = array(
				array(
					'key'     => '_xml_sitemap_exclude',
					'compare' => 'NOT EXISTS',
				),
			);
			// Update meta cache in one query instead of many. Maybe not needed? TODO test.
			$args['update_post_meta_cache'] = true;
		}

		return $args;
	}

	/**
	 * Maybe exclude taxonomies. Hooked into wp_sitemaps_taxonomies filter.
	 *
	 * @since 0.1
	 *
	 * @param array $taxonomies Taxonomies array.
	 *
	 * @return array $taxonomies
	 */
	public static function exclude_taxonomies( $taxonomies ) {
		$disabled_subtypes = (array) \get_option( 'xmlsm_disabled_subtypes', array() );
		$exclude           = \array_key_exists( 'taxonomies', $disabled_subtypes ) ? (array) $disabled_subtypes['taxonomies'] : array();

		foreach ( $taxonomies as $tax_type => $object ) {
			if ( \in_array( $tax_type, $exclude, true ) ) {
				unset( $taxonomies[ $tax_type ] );
			}
		}

		return $taxonomies;
	}

	/**
	 * Style rules for the sitemap. Hooked into wp_sitemaps_stylesheet_css filter.
	 *
	 * @since 0.6
	 *
	 * @param string $css Style rules.
	 *
	 * @return string $css
	 */
	public static function stylesheet( $css ) {
		// If we need rules for sitemap type then use $which = \get_query_var( 'sitemap-stylesheet' ); can be 'index' or 'sitemap'.

		$intro = \esc_html__( 'Managed and extended by XML Sitemaps Manager to improve performance and search engine visibility.', 'xml-sitemaps-manager' );
		$note  = \esc_html__( 'Added by XML Sitemaps Manager.', 'xml-sitemaps-manager' );

		$css .= <<<EOF
		/* Style rules added by XML Sitemaps Manager */

		#sitemap {
			max-width: unset;
		}

		#sitemap__header h1 + p::after {
			content: " {$intro}";
		}

		#sitemap__table {
			border-width: 0 0 1px 0;
		}

		#sitemap__table tr th {
			background: #444;
			color: white;
		}

		#sitemap__table tr td.lastmod {
			white-space: nowrap;
		}

EOF;

		// Return if Lastmod is not activated.
		if ( ! \get_option( 'xmlsm_lastmod' ) ) {
			return $css;
		}

		$note = \esc_html__( 'Added by XML Sitemaps Manager.', 'xml-sitemaps-manager' );

		$css .= <<<EOF
		#sitemap__table tr th.lastmod::after {
			content: "*";
		}

		#sitemap::after {
			content: "*) {$note}";
			display: block;
			margin: 1em 0;
			font-weight: 500;
			font-style: italic;
			font-size: smaller;
		}

EOF;

		return $css;
	}
}
