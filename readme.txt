=== Better Search - Relevant search results for WordPress ===
Contributors: webberzone, Ajay
Tags: search, Better Search, related search, relevant search, relevance
Donate link: https://wzn.io/donate-wz
Stable tag: 4.4.3
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 7.4
License: GPLv2 or later

Better Search replaces the default WordPress search with a better search engine that gives contextual results sorted by relevance.

== Description ==

Supercharge your WordPress site search with __[Better Search](https://webberzone.com/plugins/better-search/)__ – a powerful replacement for the default WordPress search engine that delivers more relevant results and a richer search experience.

Better Search gives you complete control over your site’s search results. Fine-tune relevance, search across different fields and post types, track popular queries, and customise the output — all without writing a single line of code.

Make your search more intuitive and engaging with a search heatmap of popular queries, display results as users type with AJAX Live Search, and tailor the look to your theme with custom templates and styles.

Built with performance in mind, Better Search includes its own caching system and works smoothly with popular caching plugins like WP Super Cache and W3 Total Cache. It also features a profanity filter and is translation-ready for global use.

## Awesome features in Better Search:

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

* 🗄️ [Efficient Content Storage and Indexing](https://webberzone.com/support/knowledgebase/efficient-content-storage-and-indexing/)
* 🔍 [Multisite Search](https://webberzone.com/support/knowledgebase/multisite-search/)
* ✨ [Fuzzy Matches](https://webberzone.com/support/knowledgebase/fuzzy-matches/)
* 🎯 [Relevance Threshold](https://webberzone.com/support/knowledgebase/better-search-settings-search/#minimum-relevance-percentage-pro-only)
* 🔗 [Search Post Slugs](https://webberzone.com/support/knowledgebase/better-search-settings-search/#search-post-slug-pro-only)
* ⚙️ [REST API Integration](https://webberzone.com/support/knowledgebase/better-search-rest-api/)
* 🔄 [LIKE Fallback Search](https://webberzone.com/support/knowledgebase/better-search-settings-search/#enable-like-fallback-pro-only)
* ⚖️ [Advanced Relevance Weighting](https://webberzone.com/support/knowledgebase/better-search-settings-search/#post-excerpt-pro-only)

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
* [WebberZone Link Warnings](https://wordpress.org/plugins/webberzone-link-warnings/) - Add accessible warnings for external links and target="_blank" links
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

= 4.4.3 =

*Release Date - 5 September 2026*

* Improvements:
	* Improved multisite and admin performance by caching Better Search table-existence checks, network table discovery, and FULLTEXT index status checks, eliminating repeated `SHOW TABLES` and `SHOW INDEX` metadata queries while adding live health checks and safe recovery when tables change outside WordPress.
	* Reduced database work for FULLTEXT searches that include post meta or comments by using existence checks instead of row-multiplying joins.
	* Improved negative searches by separating excluded terms from the FULLTEXT match and applying them across the enabled search fields.
	* Added the `bsearch_search_meta_keys` filter to limit meta searches to selected keys.
	* Added short-lived caching for live-search responses and heatmap counts, and avoided unnecessary result-count queries for live search.
	* [Pro] Reduced memory usage during spelling-dictionary rebuilds by processing titles in batches and keeping the existing dictionary available until the replacement is ready.
	* [Pro] Prevented repeated saves of the same post from inflating spelling-dictionary frequencies.
	* [Pro] Optimized custom-table result counts by skipping relevance-score calculation when no relevance threshold is applied.

* Bug fixes:
	* Fixed negative-only searches returning no results and negative terms being ignored in natural-language FULLTEXT searches.
	* Fixed the dashboard's "Last 7 days", "Last 14 days" and "Last 30 days" tabs covering one day more than their labels, since the date range is inclusive of both endpoints.
	* [Pro] Preserved negative-term exclusions when fuzzy LIKE matching is enabled.
	* [Pro] Fixed spelling-dictionary rebuilds temporarily emptying the dictionary and ensured invalid batch sizes cannot stall a rebuild.
	* [Pro] Fixed spelling-dictionary rebuild failures when words differ only by case or accents.
	* [Pro] Fixed custom-table indexing missing taxonomy and indexed-meta changes made outside a post save (quick edit, SEO plugin primary-term changes), and moved large term refreshes to bounded background batches.

= 4.4.2 =

* Improvements:
	* Updated the Settings API to version 3.0.0, adding missing sanitizers for radio, select, wysiwyg, file, password, css, html and other field types, and preventing unregistered submitted settings from being saved raw.
	* Added the `bsearch_highlight_use_boundaries` filter to the client-side highlighter, allowing themes to enable whole-word-only matching on cached pages.

* Bug fixes:
	* Fixed stopword stripping breaking with a PHP `preg_replace()` warning, and silently leaving stopwords in place, when the translated stopword list or the `wp_search_stopwords` filter contained a `/`.
	* [Pro] Fixed the ORDER BY clause being silently rewritten to `score DESC` when full-text search was unavailable, which discarded the "Sort by date" ordering on short-term and LIKE-based searches.
	* Fixed plugin data being deleted when uninstalling one version while its paired free or Pro counterpart was active.

= 4.4.1 =

*Release Date - 20 August 2026*

* Improvements:
	* Setting defaults are now resolved from a single lightweight list instead of building every settings field, so reading an option early in the page load no longer risks loading translations too early.

* Bug fixes:
	* Fixed the settings wizard silently dropping repeater field rows on save.
	* Fixed settings not saving when submitted without a referer (e.g. via REST or WP-CLI).

= 4.4.0 =

*Release Date - 2 August 2026*

Read more in the [Better Search v4.4.0 release post](https://webberzone.com/announcements/better-search-v4-4-0/).

* Features:
	* Added a Feature Manager to toggle optional features from a new Features tab.
	* [Pro] Added "Did you mean" suggestions for zero-result searches, with Suggest and Auto-correct modes.
	* [Pro] Added search redirects with exact or contains matching and 301 or 302 status codes.
	* Added search to quickly find options across settings tabs.

* Enhancements:
	* Added the `bsearch_pre_index_content_parts` filter to modify content before it is stored in custom search tables.
	* Standardized FULLTEXT index names across database tools.
	* Updated the Settings API to version 2.10.1 and refreshed admin assets.

* Bug fixes:
	* Fixed settings page layout and field rendering issues.
	* Fixed disabled Pro settings being discarded or remaining editable in the free plugin.
	* Fixed validation preventing settings from saving when required fields were inside collapsed repeater rows.
	* [Pro] Fixed the setup wizard changing steps while custom tables were being indexed.
	* Fixed database checks not restoring the previous error display state.
	* Fixed highlighting fallback behavior for queries containing an unclosed quote.

For previous changelog entries, please refer to the separate changelog.txt file or [Github Releases page](https://github.com/WebberZone/better-search/releases)

== Upgrade Notice ==

= 4.4.3 =
Improves search and multisite performance, strengthens negative-term handling, adds configurable meta-key filtering, and fixes spelling-dictionary and dashboard issues.
