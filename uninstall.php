<?php
/**
 * WP Sitemaps Manager uninstallation.
 *
 * @package WP Sitemaps Manager
 *
 * @since 0.1
 */

namespace XMLSitemapsManager;

// Exit if uninstall not called from WordPress.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit();

// Check if it is a multisite and not a large one.
if ( \is_multisite() ) {
	if ( WP_DEBUG && WP_DEBUG_LOG ) {
		\error_log( 'Clearing XML Sitemaps Manager settings from each site before uninstall:' );
	}

	if ( \wp_is_large_network() ) {
		if ( WP_DEBUG && WP_DEBUG_LOG ) {
			\error_log( 'Aborting multisite uninstall. Too many sites in your network.' );
		}
		uninstall();
		return;
	}

	$_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => -1,
		)
	); // TEST number = -1..1000.

	foreach ( $_ids as $_id ) {
		\switch_to_blog( $_id );

		uninstall( $_id );

		\restore_current_blog();
	}
} else {
	uninstall();
}

/**
 * Remove plugin data.
 *
 * @since 0.1
 *
 * @param int $_id Blog ID.
 */
function uninstall( $_id = false ) {
	/**
	 * Remove metadata.
	 */
	// Already done on plugin deactivation.

	/**
	 * Remove plugin settings.
	 */
	\delete_option( 'xmlsm_version' );
	\delete_option( 'xmlsm_sitemaps_fixes' );
	\delete_option( 'xmlsm_max_urls' );
	\delete_option( 'xmlsm_lastmod' );
	\delete_option( 'xmlsm_sitemap_providers' );
	\delete_option( 'xmlsm_disabled_subtypes' );

	// Kilroy was here.
	if ( WP_DEBUG && WP_DEBUG_LOG ) {
		if ( $_id ) {
			\error_log( 'XML Sitemaps Manager settings cleared for blog ID:' . $_id );
		} else {
			\error_log( 'XML Sitemaps Manager settings cleared on uninstall.' );
		}
	}
}
