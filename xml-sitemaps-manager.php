<?php
/*
Plugin Name: XML Sitemaps Manager
Plugin URI:
Description: Fix some bugs and add new options to manage the WordPress core XML Sitemaps. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=WP%20Sitemaps%20Manager">tip</a></strong> for continued development and support. Thanks :)
Text Domain: wp-sitemaps-manager
Version: 0.1
Requires at least: 5.5
Requires PHP: 5.6
Author: RavanH
Author URI: https://status301.net/
*/

defined( 'WPINC' ) || die;

define( 'WPSM_VERSION', '0.1' );
define( 'WPSM_DIR', dirname(__FILE__) );
define( 'WPSM_BASENAME', plugin_basename(__FILE__) );

// Load WP core sitemaps manager.
require_once __DIR__ . '/includes/class.wp-sitemaps-manager.php';
new XML_Sitemaps_Manager;

if ( is_admin() ) {
    require_once __DIR__ . '/includes/class.wp-sitemaps-manager-admin.php';
	new XML_Sitemaps_Manager_Admin;
}
