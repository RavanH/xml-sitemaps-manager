<?php
/**
 * XML Sitemaps Manager Debugging.
 *
 * @package XML Sitemaps Manager
 *
 * @since 0.1
 */

/**
 * Usage info for debugging printed at the end of the sitemap.
 *
 * @since 0.1
 */
function xmlsm_usage() {
	global $wp, $wpdb, $EZSQL_ERROR;

	if ( empty( $wp->query_vars['sitemap'] ) ) {
		return;
	}

	// Get memory usage.
	$mem = \function_exists( 'memory_get_peak_usage' ) ? \round( \memory_get_peak_usage() / 1024 / 1024, 2 ) . 'M' : false;

	// Get query errors.
	$errors = '';
	if ( \is_array( $EZSQL_ERROR ) && \count( $EZSQL_ERROR ) ) {
		$i = 1;
		foreach ( $EZSQL_ERROR as $e ) {
			$errors .= PHP_EOL . $i . ': ' . implode( PHP_EOL, $e ) . PHP_EOL;
			++$i;
		}
	}
	// Get saved queries.
	$saved = \defined( '\SAVEQUERIES' ) && \SAVEQUERIES ? $wpdb->queries : '';

	// Get system load.
	$load = \function_exists( 'sys_getloadavg' ) ? \sys_getloadavg() : false;

	// Print debug info.
	include __DIR__ . '/views/_usage.php';
}

add_action( 'shutdown', 'xmlsm_usage' );
