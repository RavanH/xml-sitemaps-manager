<?php
/**
 * Filter subtype to fix issue with Lastmod in sitemap index.
 *
 * @since 0.4
 *
 * @param  string $subtype
 *
 * @return string $subtype
 */
function xmlsm_polylang_index_entry_subtype( $subtype ) {
	// Check if a language was added in $subtype.
	$pattern = '#(' . implode( '|', pll_languages_list( array( 'fields' => 'slug' ) ) ) . ')$#';
	if ( preg_match( $pattern, $subtype, $matches ) && ! empty( $matches[1] ) ) {
		$subtype = preg_replace( '#(-?' . $matches[1] . ')$#', '', $subtype );
		// TODO find alternatives for get_lastpostmodified and get_lastpostdate that account for languages.
	}
	return $subtype;
}

/**
 * Filter lastmod metadata key to add language.
 *
 * @since 0.6
 *
 * @param  string $meta_key
 *
 * @return string $meta_key
 */
function xmlsm_polylang_lastmod_meta_key( $meta_key, $post = false ) {
	// Get post or the current language.
	if ( is_object( $post ) ) {
		$lang = pll_get_post_language( $post->ID );
	} else {
		$lang = pll_current_language();
	}

	if ( $lang != pll_default_language() ) {
		$meta_key .= '_' . $lang;
	}

	return $meta_key;
}

/**
 * Override default get_lastpostdate().
 *
 * @since 0.6
 *
 * @param  string $meta_key
 *
 * @return string $meta_key
 */
function xmlsm_polylang_lastmod_index_entry( $lastmod, $entry, $post_type = 'post' ) {
	$languages = pll_languages_list( array( 'fields' => 'slug' ) );
	$default = pll_default_language();
	unset( $languages[$default] );

	$url_parts = parse_url( $entry['loc'] );

	// Try to identify language from index entry URL.
	foreach ( $languages as $lang ) {
		if (
			0 === strpos( $url_parts['path'], '/'.$lang.'/' ) ||
			0 === strpos( $url_parts['host'], $lang.'.' ) ||
			( ! empty( $url_parts['query'] ) && str_contains( $url_parts['query'], 'lang='.$lang ) )
		) {
			// Got one!
			$found = $lang;
			break;
		}
	}

	// Get last post based on language.
	$posts = get_posts ( array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'update_cache' => false,
		'lang' => isset( $found ) ? $found : $default
	) );

	return ! empty( $posts ) ? get_post_field( 'post_date_gmt', $posts[0] ) : null;
}

/**
 * Clear custom lastmod meta keys.
 *
 * @since 0.6
 *
 * @param  void
 * @return void
 */
function xmlsm_polylang_clear_lastmod_meta() {
	global $wpdb;

	$languages = pll_languages_list( array( 'fields' => 'slug' ) );
	$default = pll_default_language();
	unset( $languages[$default] );

	/**
	 * Remove metadata.
	 */
	foreach ( $languages as $lang ) {
		// User meta.
		$wpdb->delete( $wpdb->prefix.'usermeta', array( 'meta_key' => 'user_modified_gmt_'.$lang ) );
	}

}
