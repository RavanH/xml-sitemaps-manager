<?php

add_filter(
	'xmlsm_index_entry_subtype',
	function( $subtype ) {
		// Check if a language was added in $subtype.
		$pattern = '#(' . implode( '|', pll_languages_list( array( 'fields' => 'slug' ) ) ) . ')$#';
		if ( preg_match( $pattern, $subtype, $matches ) && ! empty( $matches[1] ) ) {
			$subtype = preg_replace( '#(-?' . $matches[1] . ')$#', '', $subtype );
			// TODO find alternatives for get_lastpostmodified and get_lastpostdate that account for languages.
		}
		return $subtype;
	}
);
