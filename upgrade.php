<?php

namespace XMLSitemapsManager;

defined( '\WPINC' ) || die;

CONST DEFAULTS = array(
	'xmlsm_sitemaps_enabled'   => true,
	'xmlsm_sitemaps_fixes'     => true,
	'xmlsm_max_urls'           => '',
	'xmlsm_lastmod'            => false,
	'xmlsm_sitemap_providers'  => array( 'posts', 'taxonomies', 'users' ),
	'xmlsm_disabled_subtypes'  => '',
);

/**
 * Upgrade plugin data.
 *
 * @since 0.1
 */

if ( '0' !== $db_version ) {

	// Upgrading from 0.1 or 0.2.
	if ( \version_compare( '0.2', $db_version, '>=' ) ) {
		// Max urls option.
		$max_urls = \get_option( 'xmlsm_sitemaps_max_urls', '' );
		if ( \is_array( $max_urls ) && ! empty( $max_urls['post'] ) ) {
			$max_urls = $max_urls['post'];
		} else {
			$max_urls = '';
		}
		\add_option( 'xmlsm_max_urls', $max_urls );
		\delete_option( 'xmlsm_sitemaps_max_urls' );

		// Lastmod option.
		$lastmod = \get_option( 'xmlsm_sitemaps_lastmod' );
		if ( \is_array( $lastmod ) && ! empty( $lastmod ) ) {
			$lastmod = true;
		}
		\add_option( 'xmlsm_lastmod', $lastmod );
		\delete_option( 'xmlsm_sitemaps_lastmod' );
	}

}

// Fill in missing options.
foreach ( DEFAULTS as $option => $default ) {
	\add_option( $option, $default );
}

// Update DB version.
\update_option( 'xmlsm_version', \WPSM_VERSION );

global $wpdb;

/**
 * Clear metadata.
 */
// Terms meta.
$wpdb->delete( $wpdb->prefix.'termmeta', array( 'meta_key' => 'term_modified_gmt' ) );
// User meta.
$wpdb->delete( $wpdb->prefix.'usermeta', array( 'meta_key' => 'user_modified_gmt' ) );

// Kilroy was here.
if ( \defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	if ( '0' === $db_version ) {
		\error_log( 'WP Sitemaps Manager version ' . \WPSM_VERSION . ' installed.' );
	} else {
		\error_log( 'WP Sitemaps Manager upgraded from ' . $db_version . ' to ' . \WPSM_VERSION );
	}
}
