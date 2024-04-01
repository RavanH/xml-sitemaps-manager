<?php
/**
 * XML Sitemaps Manager
 *
 * Plugin Name:       XML Sitemaps Manager
 * Plugin URI:        https://status301.net/wordpress-plugins/xml-sitemaps-manager/
 * Description:       Fix some bugs and add new options to manage the WordPress core XML Sitemaps. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemaps%20Manager">tip</a></strong> for continued development and support. Thanks :)
 * Text Domain:       xml-sitemaps-manager
 * Version:           0.7-alpha3
 * Requires at least: 5.5
 * Requires PHP:      5.6
 * Author:            RavanH
 * Author URI:        https://status301.net/
 *
 * @package XML Sitemaps Manager
 */

defined( 'WPINC' ) || die;

define( 'XMLSM_VERSION', '0.7-alpha3' );
define( 'XMLSM_BASENAME', plugin_basename( __FILE__ ) );

require_once __DIR__ . '/includes/autoload.php';

add_action( 'init', array( 'XMLSitemapsManager\Load', 'front' ), 9 );
add_action( 'admin_init', array( 'XMLSitemapsManager\Load', 'admin' ) );

/**
 * Old pugin intitialization. Keep for backward compatibility.
 *
 * Allows to completely disable this plugin with remove_action( 'init', 'xmlsm_init', 9 ).
 *
 * @since 0.3
 */
function xmlsm_init() {}

add_action( 'init', 'xmlsm_init', 9 );
