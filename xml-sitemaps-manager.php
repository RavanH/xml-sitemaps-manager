<?php
/**
 * XML Sitemaps Manager
 *
 * Plugin Name:       XML Sitemaps Manager
 * Plugin URI:        https://status301.net/wordpress-plugins/xml-sitemaps-manager/
 * Description:       Fix some bugs and add new options to manage the WordPress core XML Sitemaps. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemaps%20Manager">tip</a></strong> for continued development and support. Thanks :)
 * Text Domain:       xml-sitemaps-manager
 * Version:           0.7-alpha5
 * Requires at least: 5.5
 * Requires PHP:      5.6
 * Author:            RavanH
 * Author URI:        https://status301.net/
 *
 * @package XML Sitemaps Manager
 */

defined( 'WPINC' ) || die;

define( 'XMLSM_VERSION', '0.7-alpha5' );
define( 'XMLSM_BASENAME', plugin_basename( __FILE__ ) );

add_action( 'init', array( 'XMLSitemapsManager\Load', 'front' ), 9 );
add_action( 'admin_init', array( 'XMLSitemapsManager\Load', 'admin' ) );

register_deactivation_hook( __FILE__, array( 'XMLSitemapsManager\Admin', 'deactivate' ) );

/**
 * XML Sitemap Manager Autoloader.
 *
 * @since 0.5
 *
 * @param string $class_name The fully-qualified class name.
 *
 * @return void
 */
\spl_autoload_register(
    function ( $class_name ) {
        // Skip this if not in our namespace.
        if ( 0 !== \strpos( $class_name, __NAMESPACE__ ) ) {
            return;
        }

        // Replace namespace separators with directory separators in the relative
        // class name, prepend with class-, append with .php, build our file path.
        $class_name = \str_replace( __NAMESPACE__, '', $class_name );
        $class_name = \strtolower( $class_name );
        $path_array = \explode( '\\', $class_name );
        $file_name  = 'class-' . \array_pop( $path_array ) . '.php';
        $file       = __DIR__ . \implode( \DIRECTORY_SEPARATOR, $path_array ) . \DIRECTORY_SEPARATOR . $file_name;

        // If the file exists, inlcude it.
        if ( \file_exists( $file ) ) {
            include $file;
        }
    }
);