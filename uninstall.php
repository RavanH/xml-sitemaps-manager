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
defined( '\WP_UNINSTALL_PLUGIN' ) || exit();

// Check if it is a multisite and not a large one.
if ( \is_multisite() ) {
	if ( \defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		\error_log( 'Clearing XML Sitemaps Manager settings from each site before uninstall:' );
	}

	if ( wp_is_large_network() ) {
		if ( \defined( 'WP_DEBUG' ) && WP_DEBUG ) {
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
	global $wpdb;

	/**
	 * Remove metadata.
	 */
	// Terms meta.
	$wpdb->delete( $wpdb->prefix . 'termmeta', array( 'meta_key' => 'term_modified_gmt' ) );
	// User meta.
	$wpdb->delete( $wpdb->prefix . 'usermeta', array( 'meta_key' => 'user_modified_gmt' ) );
	// TODO: add Polylang metadata removal.

	/**
	 * Remove plugin settings.
	 */
	\delete_option( 'xmlsm_version' );
	\delete_option( 'xmlsm_sitemaps_enabled' );
	\delete_option( 'xmlsm_sitemaps_fixes' );
	\delete_option( 'xmlsm_max_urls' );
	\delete_option( 'xmlsm_lastmod' );
	\delete_option( 'xmlsm_sitemap_providers' );
	\delete_option( 'xmlsm_disabled_subtypes' );

	// Kilroy was here.
	if ( \defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		if ( $_id ) {
			\error_log( 'XML Sitemaps Manager settings cleared for blog ID:' . $_id );
		} else {
			\error_log( 'XML Sitemaps Manager settings cleared on uninstall.' );
		}
	}
}
