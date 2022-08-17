=== XML Sitemaps Manager ===
Contributors: RavanH
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=WP%20Sitemaps%Manager
Tags: sitemap, xml sitemap, sitemap.xml
Requires at least: 5.5
Requires PHP: 5.6
Tested up to: 6.0
Stable tag: 0.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Options to manage the WordPress core XML Sitemaps, optimize and fix some bugs.

== Description ==

The light-weight XML Sitemaps Manager allows you to de/activate WordPress core XML sitemaps, change the maximum number of URLs per sitemap and add Last Modified dates.

= Features =

* Otions to disable the complete sitemap index or exclude certain post type sitemaps, taxonomy sitemaps or the user sitemap.
* Change the maximum number of URLs in the sitemaps.
* Add **Last Modified** dates to posts, terms, users and the first sitemap of each type in the index.
* Conditional **is_sitemap()**, [ticket](https://core.trac.wordpress.org/ticket/51543), and **is_sitemap_stylesheet()** for good measure.
* Multisite compatible: Can be network activated. On uninstallation, all sub-site options will be cleared from the database as long as not is_large_network().
* Additional fixes and improvents to the core XML Sitemap.

Fixes some core XML Sitemap bugs:
- 404 Response code on certain sitemaps, [ticket](https://core.trac.wordpress.org/ticket/51912).
- Don't set is_home() true, [ticket](https://core.trac.wordpress.org/ticket/51542).
- Don't execute main query, [ticket](https://core.trac.wordpress.org/ticket/51117).
- Ignore stickyness, [ticket](https://core.trac.wordpress.org/ticket/55633).

Improves core XML Sitemap performance by reducing the number of database queries for:
- the sitemap index by 5;
- each post type sitemap by 4;
- each taxonomy sitemap by the number of terms in that sitemap;
- each user sitemap by the number of users in that sitemap.


= Privacy / GDPR =

This plugin does not collect any user or visitor data nor set browser cookies. Using this plugin should not impact your site privacy policy in any way.

There is no data published that was not already public. There is no data actively transmitted to search engines or other third parties.


= Contribute =

If you're happy with this plugin as it is, please consider writing a quick [rating](https://wordpress.org/support/plugin/xml-sitemaps-manager/reviews/#new-post) or helping other users out on the [support forum](https://wordpress.org/support/plugin/xml-sitemaps-manager).

If you wish to help improve this plugin, you're very welcome to [translate it into your language](https://translate.wordpress.org/projects/wp-plugins/xml-sitemaps-manager/) or contribute code on [Github](https://github.com/RavanH/xml-sitemaps-manager).


= Credits =

Credits to all users actively discussing and contributing code to [Sitemap component bugs](https://core.trac.wordpress.org/query?status=accepted&status=assigned&status=closed&status=new&status=reopened&status=reviewing&component=Sitemaps&order=priority), explicitly to [@Tkama](https://core.trac.wordpress.org/ticket/51912#comment:9) for suggesting to render the sitemaps at the parse_request action hook.


== Frequently Asked Questions ==

= Where are the plugin options? =

All the plugin settings can be found on the **Settings > Reading** admin page, under **Search engine visibility**.

= Which bug fixes are included? =

A selection of community proposed fixes to reported [Sitemap component bugs](https://core.trac.wordpress.org/query?status=accepted&status=assigned&status=closed&status=new&status=reopened&status=reviewing&component=Sitemaps&order=priority) are included. Along the way, new ones might be added and resolved ones will be removed. If you are looking for a specific bug fix to be included, please ask on this plugin [Support forum](https://wordpress.org/support/plugin/xml-sitemaps-manager/) or via an Issue or Pull Request on [Github](https://github.com/RavanH/xml-sitemaps-manager).


== Upgrade Notice ==

= 0.3 =
* Improve init and admin bugfix.

= 0.2 =
* Simplified admin and bug fixes.


== Changelog ==

= 0.4 =
* FIX failing wp_sitemaps_add_provider filter

= 0.3 =
* Update some text strings
* Move class initiations to hooks plugins_loaded, init & admin_init
* FIX admin issue (strict mode), thanks @joostdekeijzer

= 0.2 =
* Simplify admin
* Fix: Textdomain xml-sitemaps-manager
* Fix: All settings empty when blog not public

= 0.1 =
* Initial release
