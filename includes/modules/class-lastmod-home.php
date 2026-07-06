<?php
/**
 * XML Sitemaps Manager Lastmod for homepage Module.
 *
 * @package XML Sitemaps Manager
 *
 * @since 0.8
 */

namespace XMLSitemapsManager\Modules;

/**
 * Add lastmod to the sitemap.
 *
 * @since 0.8
 */
class Lastmod_Home {
	/**
	 * Page on front id.
	 * @var null|int
	 */
	private static $page_on_front = null;

	/**
	 * Load lastmod module actions and filters.
	 */
	public static function load() {
		if ( 'page' === \get_option( 'show_on_front' ) ) {
			self::$page_on_front = (int) \get_option( 'page_on_front' );
			\add_filter( 'wp_sitemaps_posts_entry', array( __CLASS__, 'posts_entry' ), 10, 3 );
		} else {
			\add_filter( 'wp_sitemaps_posts_show_on_front_entry', array( __CLASS__, 'posts_show_on_front_entry' ) );
		}
	}

	/**
	 * Overwrite homepage lastmod.
	 * Hooked into wp_sitemaps_posts_entry filter.
	 *
	 * @since 0.1
	 *
	 * @param array  $entry       Sitemap entry.
	 * @param object $post_object Post object.
	 * @param string $post_type   Post type.
	 *
	 * @return array $entry
	 */
	public static function posts_entry( $entry, $post_object, $post_type ) {
		if ( 'page' === $post_type && $post_object->ID === self::$page_on_front ) {
			$home_post_type   = \apply_filters( 'xmlsm_front_page_post_type', 'post' );
			$last_post_date   = \get_lastpostdate( 'gmt', $home_post_type );
			if ( $last_post_date ) {
				$entry['lastmod'] = \wp_date( DATE_W3C, \strtotime( $last_post_date ) );
			}
		}

		return $entry;
	}

	/**
	 * Add lastmod to posts show on front entry.
	 * Hooked into wp_sitemaps_posts_show_on_front_entry filter.
	 *
	 * Overrides lastmod in WP 6.5+ with last post date instead of last modified date.
	 *
	 * @since 0.1
	 *
	 * @param array $entry Sitemap entry.
	 *
	 * @return array $entry
	 */
	public static function posts_show_on_front_entry( $entry ) {
		// Get last published post.
		$post_type = \apply_filters( 'xmlsm_blog_page_post_type', 'post' );
		$lastmod   = \get_lastpostdate( 'gmt', $post_type );

		// Add lastmod.
		if ( $lastmod ) {
			$entry['lastmod'] = \wp_date( DATE_W3C, \strtotime( $lastmod ) );
		}

		return $entry;
	}
}
