<?php
/*
Plugin Name: WordPress XML Sitemaps Manager
Plugin URI:
Description: Fix some bugs and add new options to manage the WordPress core XML Sitemaps. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=WP%20Sitemaps%20Manager">tip</a></strong> for continued development and support. Thanks :)
Text Domain: wp-sitemaps-manager
Requires at least: 5.5
Requires PHP: 5.6
Author: RavanH
Author URI: https://status301.net/
*/

defined( 'WPINC' ) || die;

// Load WP core sitemaps bugfixes and optimizations.
require_once dirname(__FILE__) . '/includes/wp-sitemaps-fixes.php';

// Load WP core sitemaps options.
// TODO