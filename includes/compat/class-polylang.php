<?php
/**
 * WP Sitemaps Manager Polylang compatibility module.
 *
 * @package WP Sitemaps Manager
 *
 * @since 0.4
 */

namespace XMLSitemapsManager\Compat;

/**
 * Polylang class.
 *
 * @since 0.6
 */
class Polylang extends Plugin {
	/**
	 * Polylang compat module front end actions and filters.
	 *
	 * @since 0.7
	 */
	public static function front() {
		// Lastmod module filters.
		if ( \get_option( 'xmlsm_lastmod' ) ) {
			\add_filter( 'xmlsm_index_entry_subtype', array( __CLASS__, 'index_entry_subtype' ) );
			\add_filter( 'xmlsm_lastmod_user_meta_key', array( __CLASS__, 'lastmod_meta_key' ), 10, 2 );
			\add_filter( 'xmlsm_lastmod_index_entry', array( __CLASS__, 'lastmod_index_entry' ), 10, 3 );
		}
	}

	/**
	 * Polylang compat module admin actions and filters.
	 *
	 * @since 0.7
	 */
	public static function admin() {
		// Clear lastmod metadata action.
		\add_action( 'xmlsm_clear_lastmod_meta', array( __CLASS__, 'clear_lastmod_meta' ) );
	}

	/**
	 * Filter subtype to fix issue with Lastmod in sitemap index.
	 *
	 * @since 0.4
	 *
	 * @param  string $subtype Subtype.
	 *
	 * @return string $subtype
	 */
	public static function index_entry_subtype( $subtype ) {
		// Check if a language was added in $subtype.
		$pattern = '#(' . \implode( '|', \pll_languages_list( array( 'fields' => 'slug' ) ) ) . ')$#';
		if ( \preg_match( $pattern, $subtype, $matches ) && ! empty( $matches[1] ) ) {
			$subtype = \preg_replace( '#(-?' . $matches[1] . ')$#', '', $subtype );
			// TODO find alternatives for get_lastpostmodified and get_lastpostdate that account for languages.
		}
		return $subtype;
	}

	/**
	 * Filter lastmod metadata key to add language.
	 *
	 * @since 0.6
	 *
	 * @param  string $meta_key Meta key.
	 * @param  obj    $post     Post object.
	 *
	 * @return string $meta_key
	 */
	public static function lastmod_meta_key( $meta_key, $post = false ) {
		// Get post or the current language.
		if ( \is_object( $post ) ) {
			$lang = \pll_get_post_language( $post->ID );
		} else {
			$lang = \pll_current_language();
		}

		if ( \pll_default_language() !== $lang ) {
			$meta_key .= '_' . $lang;
		}

		return $meta_key;
	}

	/**
	 * Override default get_lastpostdate().
	 *
	 * @since 0.6
	 *
	 * @param  string $lastmod   Lastmod date. Defaults to null.
	 * @param  array  $entry     Entry data array.
	 * @param  string $post_type Post type.
	 *
	 * @return string $meta_key
	 */
	public static function lastmod_index_entry( $lastmod, $entry, $post_type = 'post' ) {
		$languages = \pll_languages_list( array( 'fields' => 'slug' ) );
		$default   = \pll_default_language();
		unset( $languages[ $default ] );

		$url_parts = \wp_parse_url( $entry['loc'] );

		// Try to identify language from index entry URL.
		foreach ( $languages as $language ) {
			if (
				0 === \strpos( $url_parts['path'], '/' . $language . '/' ) ||
				( ! empty( $url_parts['query'] ) && \str_contains( $url_parts['query'], 'lang=' . $language ) )
			) {
				// Got one!
				$found = $language;
				break;
			}
		}

		$args = array(
			'post_status'            => 'publish',
			'orderby'                => 'modified',
			'posts_per_page'         => 1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'update_cache'           => false,
			'lang'                   => isset( $found ) ? $found : $default,
		);

		// Get last post based on language and sitemap post type.
		$posts = \get_posts( array_merge( array( 'post_type' => $post_type ), $args ) );

		$lastmod = \get_post_field( 'post_modified_gmt', $posts[0] );

		/*
		 * Calculate for one exception: the homepage as blog page.
		 */
		if ( 'page' === $post_type && 'posts' === \get_option( 'show_on_front' ) ) {
			// Get last published post.
			$home_post_type = \apply_filters( 'xmlsm_home_post_type', 'post' );

			// Get last post based on language and homepage post type.
			$posts = \get_posts( array_merge( array( 'post_type' => $home_post_type ), $args ) );

			$home_lastmod = \get_post_field( 'post_modified_gmt', $posts[0] );

			if ( $home_lastmod && $home_lastmod > $lastmod ) {
				$lastmod = $home_lastmod;
			}
		}

		return $lastmod ? $lastmod : null;
	}

	/**
	 * Clear custom lastmod meta keys.
	 *
	 * @since 0.6
	 *
	 * @return void
	 */
	public static function clear_lastmod_meta() {
		global $wpdb;

		$languages = \pll_languages_list( array( 'fields' => 'slug' ) );
		$default   = \pll_default_language();
		unset( $languages[ $default ] );

		/**
		 * Remove metadata.
		 */
		foreach ( $languages as $lang ) {
			// User meta.
			\delete_metadata( 'user', 0, 'user_modified_gmt_' . $lang, '', true );
		}
	}
}
