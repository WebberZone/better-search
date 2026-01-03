=== Better Search - Relevant search results for WordPress ===
Contributors: webberzone, Ajay
Tags: search, Better Search, related search, relevant search, relevance
Donate link: https://ajaydsouza.com/donate/
Stable tag: 4.2.2
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 7.4
License: GPLv2 or later

Better Search replaces the default WordPress search with a better search engine that gives contextual results sorted by relevance.

== Description ==

Supercharge your WordPress site search with __[Better Search](https://webberzone.com/plugins/better-search/)__ – a powerful replacement for the default WordPress search engine that delivers more relevant results and a richer search experience.

Better Search gives you complete control over your site’s search results. Fine-tune relevance, search across different fields and post types, track popular queries, and customise the output — all without writing a single line of code.

Make your search more intuitive and engaging with a search heatmap of popular queries, display results as users type with AJAX Live Search, and tailor the look to your theme with custom templates and styles.

Built with performance in mind, Better Search includes its own caching system and works smoothly with popular caching plugins like WP Super Cache and W3 Total Cache. It also features a profanity filter and is translation-ready for global use.

Here are some of the main features of __Better Search__:

* __Automatic__: Just activate the plugin and enjoy better search results right away
* __Seamless integration__: No need to edit any code or create custom search templates
* __Relevance__: Sort the results by relevance or date, and assign different weights to title and content
* __Control the results__: Search within title, content, excerpt, meta fields, authors, tags and other taxonomies and comments
* __Popular searches__: Show a heatmap of the most popular searches on your site, either as a widget or a shortcode
* __AJAX Live Search__: Show search results as you type in any search form on your site
* __Customisation__: Use your own template file and CSS styles for the ultimate look and feel
* __Supports cache plugins__: Works seamlessly with caching plugins like WP-Super-Cache and W3 Total Cache
* __Profanity filter__: Filter out any words that you don't want to appear in search queries
* __Translation ready__: Use the plugin in any language

If you want to improve your site search, download Better Search today and experience the difference for yourself.

## Features in Better Search Pro

[__Better Search Pro__](https://webberzone.com/plugins/better-search/pro/) gives you even more control and performance:

* 🔍 [Multisite Search](https://webberzone.com/support/knowledgebase/multisite-search/)
* ✨ [Fuzzy Matches](https://webberzone.com/support/knowledgebase/fuzzy-matches/)
* 🎯 [Relevance Threshold](https://webberzone.com/support/knowledgebase/better-search-settings-search/#minimum-relevance-percentage-pro-only)
* 🔗 [Search Post Slugs](https://webberzone.com/support/knowledgebase/better-search-settings-search/#search-post-slug-pro-only)
* ⚙️ [REST API Integration](https://webberzone.com/support/knowledgebase/better-search-rest-api/)

## MySQL FULLTEXT indices

Better Search adds the following MySQL FULLTEXT indices to the `wp_posts` table:

* `post_content`
* `post_title`
* `(post_title, post_content)`

On multisite, these are added to each blog upon activation. These indices power the relevance-based search and are required for full functionality.

## Contribute

Better Search is also available on [Github](https://github.com/WebberZone/better-search). If you've got some cool feature you'd like to implement into the plugin or a bug you've been able to fix, consider forking the project and sending me a pull request.

## Plugins by WebberZone

Better Search is one of the many plugins developed by WebberZone. Check out our other plugins:

* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) - Display related posts on your WordPress blog and feed
* [Top 10](https://wordpress.org/plugins/top-10/) - Track daily and total visits to your blog posts and display the popular and trending posts
* [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) - Create a knowledge base or FAQ section on your WordPress site
* [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) - The ultimate snippet manager for WordPress to create and manage custom HTML, CSS or JS code snippets
* [Auto-Close](https://wordpress.org/plugins/autoclose/) - Automatically close comments, pingbacks and trackbacks and manage revisions on your WordPress site

== Screenshots ==

1. Better Search Dashboard
2. Better Search Popular Searches table in Admin
3. Better Search widget

== Installation ==

= WordPress install =
1. Navigate to Plugins within your WordPress Admin Area.

2. Click "Add new" and enter "Better Search" in the search box.

3. Find the plugin in the list (usually the first result) and click "Install Now".

= Manual install =
1. Download the plugin

2. Extract the contents of better-search.zip to wp-content/plugins/ folder. You should get a folder called better-search.

3. Activate the Plugin in WP-Admin.

4. Goto **Settings > Better Search** to configure

== Frequently Asked Questions ==

If your question has not been covered here, please create a new post in the [WordPress.org support forum](https://wordpress.org/support/plugin/better-search). I monitor the forums regularly. If you want more advanced _paid_ support, please see [details here](https://webberzone.com/support/).

= Will this work with any WordPress theme? =  
Yes! It replaces the default WordPress search and integrates with most themes out of the box.

= Does it support WooCommerce or custom post types? =  
Yes, you can enable searching in any public post type from the settings.

= How does it affect performance? =  
Better Search uses MySQL FULLTEXT indexes and includes internal caching. It also works well with external caching plugins.

= Can I customise the search results template? =  
Yes, you can override the results template by copying the file to your theme directory. More info in the [documentation](https://webberzone.com/support/knowledgebase/better-search-templates/).

= What is the Profanity Filter? =  
It filters out selected keywords from being searched. Handy for family-safe sites.

Better Search includes a very cool profanity filter using the script from [Banbuilder](https://github.com/snipe/banbuilder). You can customize which list of words you want to filter out from the Better Search settings page. Find the setting called "Filter these words:". The plugin will automatically strip out partial and complete references to these words. You can turn the filter off by emptying the list.

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/better-search)


== Changelog ==

= 4.2.2 =

* Modifications:
	* Updated Freemius SDK to v2.13.0.
	* Upgraded Settings API.
	* Improved text highlighting.
	* The Settings screen's "Create Fuzzy Search Indexes" button now deletes and recreates the Fuzzy Search functions.
	* The Cache key is now created by eliminating unnecessary variations to improve cache efficiency.
	* New WebberZone Admin banner on Better Search admin screens for quick access to admin pages.

* Bug fixes:
	* Fixed an issue where the setup wizard notice could display on the wizard page.
	* Fixed parsing of excluded category slugs.
	* Fixed a translation string in the settings form.
	* Fixed handling of `<` and `>` in boolean search mode.
	* Fixed Boolean mode didn't work in some cases even when enabled in the Settings page.

* Security:
	* Fixed a stored XSS vulnerability.

= 4.2.1  =

* Modifications:
	* Updated Freemius SDK.
	* Handle `post_type` when passed through as a query variable.
	* Added REST API support for custom post type search queries.

* Bug fixes:
	* HTML entities are now decoded in the Live Search results.
	* Phrases with double quotes are correctly handled.

= 4.2.0 =

Release post: [https://webberzone.com/announcements/better-search-v4-2-0/](https://webberzone.com/announcements/better-search-v4-2-0/)

* Features:
	* [Pro] New: Efficient Content Storage and Indexing – Custom tables implementation for better performance and query optimization while maintaining the same relevance algorithm.
	* [Pro] New: MAX_EXECUTION_TIME hint for MySQL queries.
	* [Pro] New: LIKE fallback search.
	* New: Wizard to guide users through the setup process.
	* Copy to clipboard functionality for SQL queries in the Tools page.

* Modifications:
	* Improved caching in Core Query to catch score and blog ID.
	* New function: bsearch_get_blog_option() to get a Better Search option for a specific blog.
	* New network settings/tools page.
	* [Pro] A new button to fix any collation issues has been added to the Network Admin Settings page.
	* Updated Freemius SDK.
	* Live search displays a loading state while results are being fetched.
	* Fulltext indexes are now named `wz_title_content`, `wz_title`, and `wz_content` to ensure compatibility and optimize database space, especially when using Contextual Related Posts. After updating to this version, please recreate the indexes to benefit from the changes—until then, the plugin will use the previous index names.

* Bug fix:
	* Fixed an issue where the Live Search conflicted with Mega Menu Pro.
	* Fixed an issue where activating the Pro plugin while the Free plugin was active, or vice versa, would cause a fatal error.

For previous changelog entries, please refer to the separate changelog.txt file or [Github Releases page](https://github.com/WebberZone/better-search/releases)

== Upgrade Notice ==

 = 4.2.2 =
Better Search 4.2.2 improves text highlighting, cache efficiency, and security with bug fixes for boolean search mode and XSS vulnerability.
