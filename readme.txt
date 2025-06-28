=== Better Search - Relevant search results for WordPress ===
Contributors: webberzone, Ajay
Tags: search, Better Search, related search, relevant search, relevance
Donate link: https://ajaydsouza.com/donate/
Stable tag: 4.1.4
Requires at least: 6.5
Tested up to: 6.8
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

= 4.1.4 =

* Bug fixes:
	* Fixed Freemius initialization code in the free version.

= 4.1.3 =

* Modifications:
	* Moved Freemius SDK to vendor folder.
	* Relevance score in the template is hidden when there is no score for the post.

* Bug fixes:
	* Fix the minimum characters length setting.
	* Fixed DOMNodeInserted deprecated error in Live Search.
	* Fixed edge case issue where settings are not initialized when using multisite resulting in type errors.

= 4.1.2 =

* Features:
	* Added SQL injection detection for search terms.
	* New setting to set the minimum characters required for a fulltext search.

* Enhancements:
	* Live Search enhancements:
		* Added caching to improve performance.
		* Improved accessibility and keyboard navigation.

* Bug fixes:
	* Fixed an issue where post score was not being displayed.
	* Better Search form post type correctly uses `post_type` instead of `post_types` for better compatibility.
	* Fixed issue with search results not loading properly when Fuzzy Search is enabled and the search term is less than four characters.
	* Fixed issue with ordering by date didn't work when using Fuzzy Search.

= 4.1.1 =

* Bug fixes:
	* Fixed an issue where shortcode attributes were not properly sanitized.

= 4.1.0 =

Release post: [https://webberzone.com/announcements/better-search-v4-1-0/](https://webberzone.com/announcements/better-search-v4-1-0/)

* Features:
	* Better Search now loads globally and can be accessed using `better_search()`.

* Modifications:
	* Query improvements for better compatibility with Better Search Pro.
	* Improved accessibility of live search results particularly better keyboard navigation.
	* [Pro] Multisite search now respects the settings of each site.
	* [Pro] Better Fuzzy search compatibility with multisite search.
	* [Pro] Better compatibility with PolyLang when using Multisite search. However, PolyLang must be set up exactly the same on all sites of the multisite setup.

* Bug fixes:
	* [Pro] Fixed issues with permalinks and titles not loading properly when Multisite search is enabled.
	* [Pro] Fixed Multisite search when Seamless Mode is off.
	* [Pro] Fixed bugs with Fuzzy Search query in some cases.

For previous changelog entries, please refer to the separate changelog.txt file or [Github Releases page](https://github.com/WebberZone/better-search/releases)

== Upgrade Notice ==

= 4.1.4 =
Bug fixes; Check the changelog for details.
