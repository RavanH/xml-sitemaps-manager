=== XML Sitemaps Manager ===
Contributors: RavanH
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=WP%20Sitemaps%Manager
Tags: sitemap, xml sitemap, sitemap.xml
Requires at least: 5.5
Requires PHP: 5.6
Tested up to: 6.0
Stable tag: 0.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Options to manage the WordPress core XML Sitemaps, optimize and fix some bugs.

== Description ==

The light-weight XML Sitemaps Manager allows you to de/activate WordPress core XML sitemaps, change the maximum number of URLs per sitemap and add Last Modified dates.

It also contains some bugfixes and improvents to the core XML Sitemap.

**Fixes**

* 404 Response code on certain sitemaps, [ticket](https://core.trac.wordpress.org/ticket/51912).
* Don't set is_home() true, [ticket](https://core.trac.wordpress.org/ticket/51542).
* Don't execute main query, [ticket](https://core.trac.wordpress.org/ticket/51117).
* Ignore stickyness, [ticket](https://core.trac.wordpress.org/ticket/55633).

**Improvements**

Reduces the number of database queries for:
- post type sitemap by 4;
- the sitemap index by 5;
- taxonomy sitemap by the number of terms in that taxonomy;
- user sitemap requests by the number of users.

**Additional features**

* **Last Modified** dates for post types, term and user archives plus the first sitemap of each type in the index.
* Conditional **is_sitemap()**, [ticket](https://core.trac.wordpress.org/ticket/51543).
* Conditional **is_sitemap_stylesheet()** for good measure.


= Privacy / GDPR =

This plugin does not collect any user or visitor data nor set browser cookies. Using this plugin should not impact your site privacy policy in any way.

**Data that is published**

An XML Sitemap index, referencing other sitemaps containing your web site's public post URLs of selected public post types, optionally with their last modification date, and any selected public archive URLs.

**Data that is transmitted**

There is no data actively transmitted to search engines or other third parties.


= Contribute =

If you're happy with this plugin as it is, please consider writing a quick [rating](https://wordpress.org/support/plugin/xml-sitemaps-manager/review/#new-post) or helping other users out on the [support forum](https://wordpress.org/support/plugin/xml-sitemaps-manager).

If you wish to help build this plugin, you're very welcome to [translate it into your language](https://translate.wordpress.org/projects/xml-plugins/xml-sitemaps-manager/) or contribute code on [Github](https://github.com/RavanH/xml-sitemaps-manager).


= Credits =

Credits to all users actively discussing and contributing code to [Sitemap component bugs](https://core.trac.wordpress.org/query?status=accepted&status=assigned&status=closed&status=new&status=reopened&status=reviewing&component=Sitemaps&order=priority).
Explicit credits to [@Tkama](https://core.trac.wordpress.org/ticket/51912#comment:9).


== Frequently Asked Questions ==

= Where are the options? =

All the plugin settings can be found on the **Settings > Reading** admin page, under **Search engine visibility**.

= What bugfixes are included? =

A selection of community proposed fixes to [Sitemap component bugs](https://core.trac.wordpress.org/query?status=accepted&status=assigned&status=closed&status=new&status=reopened&status=reviewing&component=Sitemaps&order=priority) are included. ALong the way, others will be added, and fixed bugs will be removed. If you are looking for a specific bugfix to be included, please ask on this plugin [Support forum](https://wordpress.org/support/plugin/xml-sitemaps-manager/) or via an Issue or Pull Request on [Github](https://github.com/RavanH/xml-sitemaps-manager)


== Upgrade Notice ==

= 0.2 =

* Simplified admin and bugfixes.


== Changelog ==

= 0.2 =
* Simplify admin
* Fix: Textdomain xml-sitemaps-manager
* Fix: All settings empty when blog not public

= 0.1 =
* Initial release
