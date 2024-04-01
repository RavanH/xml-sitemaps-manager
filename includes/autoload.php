<?php
/**
 * XML Sitemaps Manager plugable functions.
 *
 * @package XML Sitemaps Manager
 *
 * @since 0.7
 */

/**
 * XML Sitemap Manager Autoloader.
 *
 * @since 0.5
 *
 * @param string $class_name The fully-qualified class name.
 *
 * @return void
 */
function xmlsm_autoloader( $class_name ) {
	// Skip this if not in our namespace.
	if ( 0 !== strpos( $class_name, 'XMLSitemapsManager' ) ) {
		return;
	}

	// Replace namespace separators with directory separators in the relative
	// class name, prepend with class-, append with .php, build our file path.
	$class_name = str_replace( 'XMLSitemapsManager', '', $class_name );
	$class_name = strtolower( $class_name );
	$path_array = explode( '\\', $class_name );
	$file_name  = 'class-' . array_pop( $path_array ) . '.php';
	$file       = __DIR__ . implode( DIRECTORY_SEPARATOR, $path_array ) . DIRECTORY_SEPARATOR . $file_name;

	// If the file exists, inlcude it.
	if ( file_exists( $file ) ) {
		include $file;
	}
}

spl_autoload_register( 'xmlsm_autoloader' );
