<?php
/**
 * WP Sitemaps Manager uninstallation.
 *
 * @since 0.1
 */

// Exit if uninstall not called from WordPress.
defined('WP_UNINSTALL_PLUGIN') || exit();

global $wpdb;

// Check if it is a multisite and not a large one.
if ( is_multisite() ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'Clearing XML Sitemaps Manager settings from each site before uninstall:');
	}
	$field = 'blog_id';
	$table = $wpdb->prefix.'blogs';
	$blog_ids = $wpdb->get_col("SELECT {$field} FROM {$table}");
	if ( count( $blog_ids ) > 10000 ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Aborting multisite uninstall. Too many sites in your network.');
		}
		xmlsm_uninstall();
		return;
	}
	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		xmlsm_uninstall( $blog_id);
	}
	restore_current_blog();
} else {
	xmlsm_uninstall();
}


/**
 * Remove plugin data.
 *
 * @since 0.1
 */
function xmlsm_uninstall( $blog_id = false ) {
	global $wpdb;

	/**
	 * Remove metadata.
	 */
	// Terms meta.
	$wpdb->delete( $wpdb->prefix.'termmeta', array( 'meta_key' => 'term_modified_gmt' ) );
	// User meta.
	$wpdb->delete( $wpdb->prefix.'usermeta', array( 'meta_key' => 'user_modified_gmt' ) );

	/**
	 * Remove plugin settings.
	 */
	delete_option('xmlsm_version');
	delete_option('xmlsm_sitemaps_enabled');
	delete_option('xmlsm_sitemaps_fixes');
	delete_option('xmlsm_max_urls');
	delete_option('xmlsm_lastmod');
	delete_option('xmlsm_sitemap_providers');
	delete_option('xmlsm_disabled_subtypes');

	// Kilroy was here
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		if ( $blog_id )
			error_log( 'XML Sitemaps Manager settings cleared for blog ID:' . $blog_id );
		else
			error_log( 'XML Sitemaps Manager settings cleared on uninstall.' );
	}
}
