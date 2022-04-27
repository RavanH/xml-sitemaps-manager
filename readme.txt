=== WordPress XML Sitemaps Manager ===
Contributors: RavanH
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=WP%20Sitemaps%Manager
Tags: sitemap, xml sitemap, sitemap.xml, Google, Yahoo, Bing, Yandex, Baidu, seo, image sitemap
Requires at least: 5.5
Requires PHP: 5.6
Tested up to: 5.9
Stable tag: 0.1

Fix some bugs and add new options to manage the WordPress core XML Sitemaps.

== Description ==






**Fixes**

* 404 Response code on certain sitemaps @see https://core.trac.wordpress.org/ticket/51912
* Don't set is_home() true @see https://core.trac.wordpress.org/ticket/51542
* Don't execute main query @see https://core.trac.wordpress.org/ticket/51117
* Ignore stickyness @see https://core.trac.wordpress.org/ticket/55633

**Improvements**

* Reduces 4 database queries for post type sitemap requests.
* Reduces 5 database queries for the sitemap index request.
* Reduces N database queries for taxonomy sitemap requests, where N is the number of terms in that taxonomy.
* Reduces 12 database queries from user sitemap requests.

**Additional features**

* is_sitemap() conditional tag @see https://core.trac.wordpress.org/ticket/51543
* is_sitemap_stylesheet() conditional tag for good measure.


= Privacy / GDPR =

This plugin does not collect any user or visitor data nor set browser cookies. Using this plugin should not impact your site privacy policy in any way.

**Data that is published**

An XML Sitemap index, referencing other sitemaps containing your web site's public post URLs of selected post types that are already public, along with their last modification date and associated image URLs, and any selected public archive URLs.

An author sitemap can be included, which will contain links to author archive pages. These urls contain author/user slugs, and the author archives can contain author bio information. If you wish to keep this out of public domain, then deactivate the author sitemap and use an SEO plugin to add noindex headers.

**Data that is transmitted**

Data actively transmitted to search engines is your sitemap location and time of publication. This happens upon each post publication when at least one of the Ping options on Settings > Writing is enabled. In this case, the selected search engines are alerted of the location and updated state of your sitemap.


= Contribute =

If you're happy with this plugin as it is, please consider writing a quick [rating](https://wordpress.org/support/plugin/wp-sitemaps-manager/review/#new-post) or helping other users out on the [support forum](https://wordpress.org/support/plugin/wp-sitemaps-manager).

If you wish to help build this plugin, you're very welcome to [translate it into your language](https://translate.wordpress.org/projects/wp-plugins/wp-sitemaps-manager/) or contribute code on [Github](https://github.com/RavanH/wp-sitemaps-manager/).

= Credits =



== Frequently Asked Questions ==

= Where are the options? =



== Upgrade Notice ==

= 0.1 =



== Changelog ==

= 0.1 =
* initial release
