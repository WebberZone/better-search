=== Better Search - Relevant search results for WordPress ===
Contributors: webberzone, Ajay
Tags: search, Better Search, related search, relevant search, relevance
Donate link: https://ajaydsouza.com/donate/
Stable tag: 4.0.3
Requires at least: 6.3
Tested up to: 6.7
Requires PHP: 7.4
License: GPLv2 or later

Better Search replaces the default WordPress search with a better search engine that gives contextual results sorted by relevance.

== Description ==

Are you looking for a way to improve your WordPress site search and make it easier for visitors to find what they need? If so, you need **[Better Search](https://webberzone.com/plugins/better-search/)**, the plugin that replaces the default WordPress search engine with a more powerful and relevant one.

**Better Search** is more than just a simple search plugin. This complete solution gives you full control over your site search results. You can customize the output, fine-tune the relevance, search within different fields and post types, track popular searches, and much more.

With **Better Search**, you can make your site search more user-friendly and engaging. You can display a "search heatmap" of the most popular searches on your site, either as a widget or a shortcode. You can also use your template file and CSS styles to match your theme perfectly.

**Better Search** has a caching system and is compatible with caching plugins like WP Super Cache and W3 Total Cache, so you don't have to worry about performance issues. It also has a profanity filter that blocks unwanted words from search queries. It is translation-ready so that you can use it in any language.

Here are some of the main features of **Better Search**:

* **Automatic**: Activate the plugin and enjoy better search results right away
* **Seamless integration**: No need to edit any code or create custom search templates
* **Relevance**: Sort the results by relevance or date and assign different weights to the title and content
* **Control the results**: Search within title, content, excerpt, meta fields, authors, tags and other taxonomies and comments
* **Popular searches**: Show a heatmap of the most popular searches on your site, either as a widget or a shortcode
* **AJAX Live Search**: Show search results as you type in any search form on your site
* **Customization**: Use your template file and CSS styles for the ultimate look and feel
* **Supports cache plugins**: Works seamlessly with caching plugins like WP-Super-Cache and W3 Total Cache
* **Profanity filter**: Filter out any words that you don't want to appear in search queries
* **Translation ready**: Use the plugin in any language

If you want to improve your site search, download **Better Search** today and experience the difference!

= Features in Better Search Pro =

[__Better Search Pro__](https://webberzone.com/plugins/better-search/pro/) is the plugin's premium version that offers even more features and functionality. With __Better Search Pro__, you can:

* [__Multisite search__](https://webberzone.com/support/knowledgebase/multisite-search/): Allow network admins to select specific sites for cross-network searches.
* [__Fuzzy search__](https://webberzone.com/support/knowledgebase/fuzzy-matches/): Find results even if the search term is misspelt.
* [__Relevance threshold__](https://webberzone.com/support/knowledgebase/better-search-settings-search/#minimum-relevance-percentage-pro-only): Only show search results above a certain relevance threshold.
* [__Search the post slug__](https://webberzone.com/support/knowledgebase/better-search-settings-search/#search-post-slug-pro-only): Include the post slug in the search results.
* [__REST API__](https://webberzone.com/support/knowledgebase/better-search-rest-api/): Allow the REST API to utilize the Better Search when enabled.

= mySQL FULLTEXT indices =

The plugin creates three mySQL FULLTEXT indices (or indexes) in the `*_posts` table on activation. These are for `post_content`, `post_title`, and `(post_title, post_content)`. If you're running a multisite installation, these are created for each of the blogs on activation. All these indices occupy space in your mySQL database but are essential for running the plugin.

= Contribute =
Better Search is also available on [Github](https://github.com/WebberZone/better-search)
So, if you've got some cool feature you'd like to implement into the plugin or a bug you've been able to fix, consider forking the project and sending me a pull request.

= Plugins by WebberZone =

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

= Can I customize the output? =

Better Search has a vast set of options that help you customize the output or fine-tune the results without leaving the comfort of your WordPress site. To configure, go to **Settings > Better Search**.

The plugin also supports the use of template files within your theme. You can create a file called `better-search-template.php` in your theme's directory, and the plugin will display the results.

= My search words are getting filtered or *How does the profanity filter work* =

Better Search includes a very cool profanity filter using the script from [Banbuilder](https://github.com/snipe/banbuilder). You can customize which list of words you want to filter out from the Better Search settings page. Find the setting called "Filter these words:". The plugin will automatically strip out partial and complete references to these words.
You can turn the filter off by emptying the list.

Do you know of a better profanity filter? Suggest one in the [forums](https://wordpress.org/support/plugin/better-search).

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/better-search)


== Changelog ==

= 4.0.3 =

Release post: [https://webberzone.com/announcements/better-search-v4-enhance-wordpress-search-with-pro-features/](https://webberzone.com/announcements/better-search-v4-enhance-wordpress-search-with-pro-features/)

* Modifications:
	* Added font color (#000) to `.bsearch_highlight` class.
	* Only highlight words > 2 characters.

* Bug fixes:
	* Fixed a bug where post titles in search results were not highlighted properly.
	* Fix for highlighting when Seamless Mode is off.
	* Words with double or single quotes are properly highlighted.
	* Quotes are properly displayed in heatmap.

= 4.0.2 =

* Modifications:
	* Improved search term highlighting in post titles when accessed from the search results page, preventing conflicts with shortcodes calling `the_title` filter.
	* Deleting the free plugin should retain the settings for the Pro plugin.
	* Added WPML support for Settings' strings: Heatmap title for overall and daily searches.
	* A new button has been added to the Tools page to manually create the Better Search tables.
	* An admin notice is now shown when the Better Search tables are missing.

* Bug fixes:
	* Fixed the issue where database tables were not created in a new installation.

= 4.0.1 =

* Modifications:
	* Setting: "Filter whole words only" set to _true_ by default.

* Bug fixes:
	* Corrected the database upgrade notice that was incorrectly displayed for new users.
	* Fixed an issue where the search term was not highlighted in the post content when following a /search/ link.
	* Resolved an issue where the search term was incorrectly highlighted in the images' alt text.
	* Seamless mode didn't load correctly.

= 4.0.0 =

* Features:
	* New live search feature that shows search results as you type.
	* [Pro] New setting to enable and set the fuzzy search level. Once enabled, the plugin will attempt to find results if the search term is misspelt. This only works effectively for English words.
	* [Pro] Multisite search feature allowing network admins to select specific sites for cross-network searches. Sites not selected will function independently.
	* [Pro] New setting to only show search results above a certain relevance threshold.
	* [Pro] New setting to search the post slug.
	* [Pro] New setting *Enable REST API* allows the REST API to utilize the Better Search when enabled. [Read this knowledge base article for more information about how Better Search enhances the Search endpoint](https://webberzone.com/support/knowledgebase/better-search-rest-api/).

* Enhancements/Modifications:
	* Renamed `Better_Search` to `Better_Search_Core_Query`. Each of the methods now removes the filter from itself. It will also automatically parse wp_query parameters.
	* Updated `Better_Search_Core_Query` filters to use the class instead of `WP_Query`.
	* Display admin notice if any FULLTEXT index is missing and **Enable mySQL FULLTEXT searching** is enabled. This is only shown to admins and cannot be dismissed until the indexes are created.
	* Better Search results page now works with Block Templates. You can enable Seamless mode to use your theme's search template.
	* [Pro] Added a new button to create the indexes and display the index status on the settings page under the **Search tab for Enable mySQL FULLTEXT searching**.

* Bug fixes:
	* Quotes in search terms should work correctly now.

For previous changelog entries, please refer to the separate changelog.txt file or [Github Releases page](https://github.com/WebberZone/better-search/releases)

== Upgrade Notice ==

= 4.0.3 =
Bug fixes, Better Search Pro launched!
