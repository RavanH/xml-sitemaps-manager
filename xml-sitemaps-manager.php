<?php
/*
Plugin Name: XML Sitemaps Manager
Plugin URI: https://status301.net/wordpress-plugins/xml-sitemaps-manager/
Description: Fix some bugs and add new options to manage the WordPress core XML Sitemaps. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemaps%20Manager">tip</a></strong> for continued development and support. Thanks :)
Text Domain: xml-sitemaps-manager
Version: 0.3-alpha1
Requires at least: 5.5
Requires PHP: 5.6
Author: RavanH
Author URI: https://status301.net/
*/

defined( 'WPINC' ) || die;

define( 'WPSM_VERSION', '0.2' );
define( 'WPSM_DIR', dirname(__FILE__) );
define( 'WPSM_BASENAME', plugin_basename(__FILE__) );

// Load WP core sitemaps manager.
require_once __DIR__ . '/includes/class.xml-sitemaps-manager.php';
new XML_Sitemaps_Manager;

if ( is_admin() ) {
    require_once __DIR__ . '/includes/class.xml-sitemaps-manager-admin.php';
	new XML_Sitemaps_Manager_Admin;
}
