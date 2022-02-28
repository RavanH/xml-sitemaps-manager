<?php
/*
Plugin Name: XML Sitemap Manager TEST
Plugin URI: https://status301.net/wordpress-plugins/xml-sitemap-feed/
Description: Feed the hungry spiders in compliance with the XML Sitemap and Google News protocols. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feed">tip</a></strong> for continued development and support. Thanks :)
Text Domain: xml-sitemap-feed
Requires at least: 5.5
Requires PHP: 5.6
Author: RavanH
Author URI: https://status301.net/
*/

/*
CHANGE SITEMAP INDEX URL ?

ADD SITEMAP TAGS
lastmod, changefreq (?), priority, ... image?
filter wp_sitemaps_index_entry
filter wp_sitemaps_posts_entry 
filter wp_sitemaps_taxonomies_entry
filter wp_sitemaps_users_entry

EXCLUDE CERTAIN POST TYPES, TAXONOMIES

filter wp_sitemaps_post_types 
filter wp_sitemaps_taxonomies

EXCLUDE POSTS ON META VALUE

filter wp_sitemaps_posts_query_args ... add 'meta_query' => array(
       array(
           'key' => '_xmlsf_exclude',
           'value' => '1',
           'compare' => '!=',
       )
   )

EXCLUDE POSTS ON ID ? ON AUTHOR ? ON CATEGORY/TAG ? 


CHANGE MAX URL NUMBER PER SITEMAP
filter wp_sitemaps_max_urls (default 2000)

ADD SITEMAPS
action wp_sitemaps_init > register additional sitemaps (custom, news)

STYLESHEET
wp_sitemaps_stylesheet_css
wp_sitemaps_stylesheet_(index_)content
... or replace completely with wp_sitemaps_stylesheet_(index_)url
*/

if ( ! defined( 'WPINC' ) ) die;

// DISABLE ALL SITEMAPS
//add_filter( 'wp_sitemaps_enabled', '__return_false' );

// DISABLE SITEMAP PROVIDERS
//add_filter( 'wp_sitemaps_add_provider', '__return_false' );

