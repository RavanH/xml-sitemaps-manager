<?php
$xmlsm_options = array(
	'xmlsm_sitemaps_enabled'   => true,
	'xmlsm_sitemaps_fixes'     => true,
	'xmlsm_max_urls'           => '',
	'xmlsm_lastmod'            => false,
	'xmlsm_sitemap_providers'  => array( 'posts', 'taxonomies', 'users' ),
	'xmlsm_disabled_subtypes'  => '',
);

if ( $db_version ) {
	xmlsm_upgrade( $db_version, $xmlsm_options );
} else {
	xmlsm_install( $xmlsm_options );
}

update_option( 'xmlsm_version', WPSM_VERSION );

/**
 * Set up default plugin data.
 *
 * @since 0.1
 */
function xmlsm_install( $options ) {
	// Make sure to start with fresh defaults.
	foreach ( $options as $option => $default ) {
		delete_option( $option );
		add_option( $option, $default );
	}

	// Kilroy was here.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'WP Sitemaps Manager version '.WPSM_VERSION.' installed.' );
	}
}

/**
 * Upgrade plugin data.
 *
 * @since 0.1
 */
function xmlsm_upgrade( $db_version, $options ) {
	if ( version_compare( '0.2', $db_version, '>=' ) ) {
		// Max urls option.
		$max_urls = get_option( 'xmlsm_sitemaps_max_urls', '' );
		if ( is_array( $max_urls ) && ! empty( $max_urls['post'] ) ) {
			$max_urls = $max_urls['post'];
		} else {
			$max_urls = '';
		}
		add_option( 'xmlsm_max_urls', $max_urls );
		delete_option( 'xmlsm_sitemaps_max_urls' );

		// Lastmod option.
		$lastmod = get_option( 'xmlsm_sitemaps_lastmod' );
		if ( is_array( $lastmod ) && ! empty( $lastmod ) ) {
			$lastmod = true;
		}
		add_option( 'xmlsm_lastmod', $lastmod );
		delete_option( 'xmlsm_sitemaps_lastmod' );
	}

	// Fill in missing options.
	foreach ( $options as $option => $default ) {
		add_option( $option, $default );
	}

	if ( defined('WP_DEBUG') && WP_DEBUG ) {
		error_log( 'WP Sitemaps Manager upgraded from '.$db_version.' to '.WPSM_VERSION );
	}
}
