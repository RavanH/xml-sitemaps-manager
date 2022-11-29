<?php
/**
 * XML_Sitemaps_Manager class
 *
 * @package XML Sitemap Manager
 * @since 0.1
 */

namespace XMLSitemapsManager;

class Core
{
	/**
	 * class Core constructor
	 *
	 * @since 0.1
	 */
	function __construct() { }

	/**
	 * Filter maximum urls per sitemap. Hooked into wp_sitemaps_max_urls filter.
	 *
	 * @since 0.1
	 *
	 * @param  int    $max_urls
	 * @param  string $object_type
	 *
	 * @return int    $max_urls
	 */
	public static function max_urls( $max_urls, $object_type = 'post' )
	{
		$max = \get_option( 'xmlsm_max_urls' );

		// Optionally split mas_urls per object type 'post', 'term' or 'user'
		if ( \is_array( $max ) && ! empty( $max[$object_type] ) ) {
			$max = $max[$object_type];
		}

		return \is_numeric( $max ) && $max > 0 ? $max : $max_urls;
	}

	/**
	 * Filter sitemap providers. Hooked into wp_sitemaps_add_provider filter.
	 *
	 * @since 0.1
	 *
	 * @param  obj       $provider
	 * @param  string    $name
	 *
	 * @return false|obj $provider or false if disabled
	 */
	public static function exclude_providers( $provider, $name )
	{
		if ( \is_admin() ) {
			return $provider;
		}

		$enabled = (array) \get_option( 'xmlsm_sitemap_providers', array() );

		return \in_array( $name, $enabled ) ? $provider : false;
	}

	/**
	 * Add lastmod to index entries.
	 * Hooked into wp_sitemaps_index_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array  $entry
	 * @param string $type
	 * @param string $subtype
	 * @param int    $page
	 *
	 * @return array $entry
	 */
	public static function lastmod_index_entry( $entry, $type, $subtype, $page )
	{
		// Skip if this is not the first sitemap. TODO make this possible for subsequent sitemaps.
		if ( $page > 1 ) {
			return $entry;
		}

		$subtype = \apply_filters( 'xmlsm_index_entry_subtype', $subtype );

		// Add lastmod.
		switch( $type ) {
			case 'post':
				$lastmod = \get_lastpostmodified( 'GMT', $subtype );
				if ( $lastmod ) {
					$entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
				}
				break;

			case 'term':
				$obj = \get_taxonomy( $subtype );
				if ( $obj ) {
					$lastmodified = array();
					foreach ( (array) $obj->object_type as $object_type ) {
						$lastmodified[] = \get_lastpostdate( 'GMT', $object_type );
					}
					sort( $lastmodified );
					$lastmodified = \array_filter( $lastmodified );
					$lastmod = \end( $lastmodified );

					if ( $lastmod ) {
						$entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
					}
				}
				break;

			case 'user':
				// Get absolute last post date.
				$lastmod = \get_lastpostdate( 'GMT', 'post' );
				if ( $lastmod ) {
					$entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
				}
				// TODO make this xmlsm_user_archive_post_type filter compatible.
				break;

			default:
				// Do nothing.
		}

		return $entry;
	}

	/**
	 * Filter post types. Hooked into wp_sitemaps_post_types filter.
	 *
	 * @since 0.1
	 *
	 * @param  array $post_types
	 *
	 * @return array $post_types
	 */
	public static function exclude_post_types( $post_types )
	{
		$disabled_subtypes = (array) \get_option( 'xmlsm_disabled_subtypes', array() );
		$exclude = \array_key_exists( 'posts', $disabled_subtypes ) ? (array) $disabled_subtypes['posts'] : array();
		foreach ( $post_types as $post_type => $object ) {
			if ( \in_array( $post_type, $exclude ) ) {
				unset( $post_types[$post_type] );
			}
		}

		return $post_types;
	}

	/**
	 * Filter post query arguments. Hooked into wp_sitemaps_posts_query_args filter.
	 *
	 * @since
	 *
	 * @param array $args
	 *
	 * @return array $args
	 */
	public static function posts_query_args( $args )
	{
		/**
		 * Allow exclusion of individual posts via meta data.
		 */
		if ( \get_option( 'xmlsm_exclude_posts' ) ) {
			// Exclude posts based on meta data.
			$args['meta_query'] = array(
				array(
					'key' => '_xml_sitemap_exclude',
					'compare' => 'NOT EXISTS'
				)
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
	 * @param array $taxonomies
	 *
	 * @return array $taxonomies
	 */
	public static function exclude_taxonomies( $taxonomies )
	{
		$disabled_subtypes = (array) \get_option( 'xmlsm_disabled_subtypes', array() );
		$exclude = \array_key_exists( 'taxonomies', $disabled_subtypes ) ? (array) $disabled_subtypes['taxonomies'] : array();
		foreach ( $taxonomies as $tax_type => $object ) {
			if ( \in_array( $tax_type, $exclude ) ) {
				unset( $taxonomies[$tax_type] );
			}
		}

		return $taxonomies;
	}

	/**
	 * Style rules for the sitemap. Hooked into wp_sitemaps_stylesheet_css filter.
	 *
	 * @since 0.6
	 */
	public static function stylesheet( $css ) {
		//$which = \get_query_var( 'sitemap-stylesheet' ); // can be 'index' or 'sitemap'

		$intro = \esc_html__( 'Managed and extended by XML Sitemaps Manager to improve performance and search engine visibility.', 'xml-sitemaps-manager' );
		$note = \esc_html__( 'Added by XML Sitemaps Manager.', 'xml-sitemaps-manager' );

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

	/**
	 * Usage info for debugging printed at the end of the sitemap.
	 *
	 * @since 0.1
	 */
	public static function usage() {
		global $wp, $wpdb, $EZSQL_ERROR;

		if ( empty( $wp->query_vars['sitemap'] ) ) {
			return;
		}

		// Get memory usage.
		$mem = \function_exists('memory_get_peak_usage') ? \round( \memory_get_peak_usage()/1024/1024, 2 ) . 'M' : false;

		// Get query errors.
		$errors = '';
		if ( \is_array( $EZSQL_ERROR ) && \count( $EZSQL_ERROR ) ) {
			$i = 1;
			foreach ( $EZSQL_ERROR AS $e ) {
				$errors .= PHP_EOL . $i . ': ' . implode( PHP_EOL, $e ) . PHP_EOL;
				$i += 1;
			}
		}
		// Get saved queries.
		$saved = '';
		if ( \defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
			$saved = $wpdb->queries;
		}

		// Get system load.
		$load = \function_exists( 'sys_getloadavg' ) ? \sys_getloadavg() : false;

		// Print debug info.
		include __DIR__ . '/views/_usage.php';
	}

}
