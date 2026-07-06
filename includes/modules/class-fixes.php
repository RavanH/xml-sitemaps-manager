<?php
/**
 * XML Sitemaps Manager Fixes Module.
 *
 * @package XML Sitemaps Manager
 *
 * @since 0.5
 */

namespace XMLSitemapsManager\Modules;

/**
 * Apply core sitemap fixes.
 *
 * @since 0.5
 */
class Fixes {
	/**
	 * Page for posts id.
	 * @var null|int
	 */
	private static $page_for_posts = null;

	/**
	 * Load fixes module hooks.
	 */
	public static function load() {
		global $wp_version;

		// Include pluggable functions.
		include \dirname( __DIR__ ) . '/pluggable.php';

		// Make sitemap load early.
		\add_action( 'parse_request', 'wp_sitemaps_loaded' );

		if ( \version_compare( $wp_version, '6.1', '<' ) ) {
			\add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'posts_query_args' ) );
		}
		if ( \version_compare( $wp_version, '6.0', '<' ) ) {
			\add_filter( 'wp_sitemaps_taxonomies_query_args', array( __CLASS__, 'taxonomies_query_args' ) );
		}

		if ( \get_option( 'page_for_posts' ) ) {
			self::$page_for_posts = (int) \get_option( 'page_for_posts' );
			\add_filter( 'wp_sitemaps_posts_entry', array( __CLASS__, 'posts_entry' ), 10, 3 );
		}
	}

	/**
	 * Remove sticky posts from the first posts sitemap.
	 * This patch should not be needed after WP 6.1 release.
	 *
	 * @see https://core.trac.wordpress.org/ticket/55633
	 *
	 * @param array[] $args Query Arguments.
	 *
	 * @return array[]
	 */
	public static function posts_query_args( $args ) {
		// Ignore stickyness.
		$args['ignore_sticky_posts'] = true;

		return $args;
	}

	/**
	 * Reduce DB queries by fetching WP_Term objects, not an array of IDs.
	 * This patch should not be needed after WP 6.0 release.
	 *
	 * @see https://core.trac.wordpress.org/ticket/55239
	 * @see https://core.trac.wordpress.org/changeset/52834
	 *
	 * @param array[] $args Query Arguments.
	 *
	 * @return array[]
	 */
	public static function taxonomies_query_args( $args ) {
		// Set the taxonomy query 'fields' argument back to 'all' as originally intended.
		$args['fields'] = 'all';

		return $args;
	}

	/**
	 * Overwrite blog page lastmod.
	 * Hooked into wp_sitemaps_posts_entry filter.
	 *
	 * @since 0.8
	 *
	 * @param array  $entry       Sitemap entry.
	 * @param object $post_object Post object.
	 * @param string $post_type   Post type.
	 *
	 * @return array $entry
	 */
	public static function posts_entry( $entry, $post_object, $post_type ) {
		if ( 'page' === $post_type && $post_object->ID === self::$page_for_posts ) {
			$blog_post_type = \apply_filters( 'xmlsm_blog_page_post_type', 'post' );
			$last_post_date = \get_lastpostdate( 'gmt', $blog_post_type );
			if ( $last_post_date ) {
				$entry['lastmod'] = \wp_date( DATE_W3C, \strtotime( $last_post_date ) );
			}
		}

		return $entry;
	}
}
