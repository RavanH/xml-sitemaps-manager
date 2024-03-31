<?php
/**
 * WP Sitemaps Manager upgrade.
 *
 * @package WP Sitemaps Manager
 *
 * @since 0.2
 */

namespace XMLSitemapsManager;

defined( '\WPINC' ) || die;

const DEFAULTS = array(
	'xmlsm_sitemaps_enabled'  => true,
	'xmlsm_sitemaps_fixes'    => true,
	'xmlsm_max_urls'          => '',
	'xmlsm_lastmod'           => false,
	'xmlsm_sitemap_providers' => array( 'posts', 'taxonomies', 'users' ),
	'xmlsm_disabled_subtypes' => '',
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
\update_option( 'xmlsm_version', \XMLSM_VERSION );

/**
 * Clear metadata.
 */
// Terms meta.
delete_metadata( 'term', 0, 'term_modified_gmt', '', true );
// User meta.
delete_metadata( 'user', 0, 'user_modified_gmt', '', true );

// Kilroy was here.
if ( \defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	if ( '0' === $db_version ) {
		\error_log( 'WP Sitemaps Manager version ' . \XMLSM_VERSION . ' installed.' );
	} else {
		\error_log( 'WP Sitemaps Manager upgraded from ' . $db_version . ' to ' . \XMLSM_VERSION );
	}
}
