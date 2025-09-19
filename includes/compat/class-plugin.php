<?php
/**
 * WP Sitemaps Manager compatibility abstract class.
 *
 * @package WP Sitemaps Manager
 *
 * @since 0.7
 */

namespace XMLSitemapsManager\Compat;

/**
 * Add lastmod to the sitemap.
 *
 * @since 0.7
 */
abstract class Plugin {
	/**
	 * Abstract compat module front end actions and filters.
	 *
	 * @since 0.7
	 */
	public static function front() {}

	/**
	 * Abstract compat module admin actions and filters.
	 *
	 * @since 0.7
	 */
	public static function admin() {}
}
