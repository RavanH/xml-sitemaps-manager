<?php
/**
 * XML Sitemaps Manager Fixes Class.
 *
 * @package XML Sitemaps Manager
 *
 * @since 0.5
 */

namespace XMLSitemapsManager;

/**
 * Apply core sitemap fixes.
 *
 * @since 0.5
 */
class Fixes {

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
}
