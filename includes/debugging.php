<?php
/**
 * XML Sitemaps Manager Debugging.
 *
 * @package XML Sitemaps Manager
 *
 * @since 0.1
 */

namespace XMLSitemapsManager;

/**
 * Usage info for debugging printed at the end of the sitemap.
 *
 * @since 0.1
 */
function usage() {
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
			$errors .= PHP_EOL . $i . ': ' . \implode( PHP_EOL, $e ) . PHP_EOL;
			++$i;
		}
	}
	// Get saved queries.
	$saved = \defined( '\SAVEQUERIES' ) && \SAVEQUERIES ? $wpdb->queries : '';

	// Get system load.
	$load = \function_exists( 'sys_getloadavg' ) ? \sys_getloadavg() : false;

	// Print debug info.
	?>
<!-- WordPress Query Variables: <?php echo \esc_xml( \print_r( $wp->query_vars, true ) ); ?> -->
<!-- Queries executed: <?php echo \esc_xml( \get_num_queries() ); ?> | Peak memory usage: <?php echo $mem ? \esc_xml( $mem ) : 'Not availabe.'; ?> | Memory limit: <?php echo \esc_xml( \ini_get( 'memory_limit' ) ); ?> -->
<!-- Average system load during the last minute: <?php echo $load ? \esc_xml( $load[0] ) : 'Not available.'; ?> -->
<!-- Query errors: <?php echo ! empty( $errors ) ? \esc_xml( $errors ) : 'None encountered.'; ?> -->
<!-- Queries: <?php echo ! empty( $saved ) ? \esc_xml( \print_r( $saved, true ) ) : 'Set SAVEQUERIES to show saved database queries here.'; ?> -->
	<?php
}

\add_action( 'shutdown', 'XMLSitemapsManager\usage' );
