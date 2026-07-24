=== Better Search - Relevant search results for WordPress ===
Contributors: webberzone, Ajay
Tags: search, Better Search, related search, relevant search, relevance
Donate link: https://wzn.io/donate-wz
Stable tag: 4.3.2
Requires at least: 6.6
Tested up to: 7.0
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

= 4.4.0 =

*In development*

* [Pro] Added "Did you mean" spelling suggestions for zero-result searches, with Suggest and Auto-correct modes and an optional enchant fallback.

= 4.3.2 =

*Release Date - 12 July 2026*

* Enhancements:
	* Search term highlighting now keeps double-quoted phrases intact: searching for `"richard harding"` highlights the whole phrase instead of its individual words. Mixed queries (e.g. `"richard harding" smith`) highlight the phrase plus the remaining words, and phrases match across whitespace variations, including non-breaking spaces.
	* Terms excluded with the `-` operator are no longer highlighted, since they cannot appear in the results.
	* Added client-side JavaScript highlighting for cached pages. When a full-page cache (e.g. LiteSpeed Cache, WP Super Cache) serves a cached response, PHP-based highlighting is skipped. The new script reads `document.referrer` in the browser and applies the same highlighting logic client-side, covering singular post/page views where "Highlight followed links" is enabled.

* Bug fixes:
	* Fixed "Highlight followed links" silently not working when the site's Home URL scheme differs from the scheme visitors use (e.g. an `http://` Home URL on a site served over `https://`, common behind SSL-terminating proxies).

= 4.3.1 =

*Release Date - 8 July 2026*

* Bug fixes:
	* Fixed phrase search: double-quoted terms (e.g. `"WordPress"` or `"WordPress plugin"`) now always perform a phrase search by automatically enabling boolean mode when quoted terms are detected.
	* Fixed LIKE search stripping hyphens from compound terms (e.g. `b-26` was incorrectly searched as `b26`). Internal hyphens are now preserved; only leading/trailing hyphens used as boolean operators are stripped.
	* [Pro] Fixed: short search terms (below the FULLTEXT minimum character threshold) now target the custom table columns (`ct.title`, `ct.content`, `ct.excerpt`) instead of `wp_posts` when custom tables are enabled. Previously the LIKE fallback always searched against `wp_posts` even with a fully indexed custom table.
	* Fixed SQL syntax error when search query contains only negative terms.
	* Fixed exclusion prefix handling and SQL precedence for negative search terms.

* Enhancements:
	* [Pro] Removed a redundant `excerpt LIKE` clause from search queries when the excerpt is already covered by the FULLTEXT `MATCH` clause, reducing unnecessary query overhead.
	* [Pro] The excerpt column is now only pulled into the FULLTEXT `MATCH` clause (native or custom tables) when the excerpt weight is greater than zero or "Search Excerpt" is explicitly enabled, instead of unconditionally.

= 4.3.0 =

*Release Date - 3 May 2026*

Read more in the [Better Search Pro 4.3.0 release post](https://webberzone.com/announcements/better-search-pro-v4-3-0/).

* Features:
	* [Pro] New: WP-CLI support with comprehensive command-line interface (search, cache, db, stats, settings, tables, status, stopwords commands).
	* [Pro] Dashboard chart drill-down: click any bar in the daily searches chart to view the popular searches for that day.
	* [Pro] New InnoDB conversion tool: convert the custom table engine with automatic FULLTEXT index recreation.
	* [Pro] Scheduled reconciliation cron: a twicedaily job automatically syncs any published posts missing from the custom search index table.
	* [Pro] New exclusion options: Exclude Front page and Exclude Posts page settings to optionally remove these pages from search results.
	* [Pro] Network dashboard with popular searches chart and statistics table for multisite networks, accessible from the network admin menu.

* Enhancements:
	* [Pro] Multisite admin select-all checkboxes and post-copy URL cleanup are now handled by an external JavaScript file (via `wp_enqueue_script`) instead of inline `<script>` blocks — improves compatibility with strict Content Security Policies.
	* [Pro] Copy-to-clipboard buttons on the tools and custom tables pages are now initialized automatically; no per-block inline script needed.
	* [Pro] Improved short-term (≤3 character) LIKE searches to score full-word matches higher and order results by relevance.
	* [Pro] Refactored fuzzy query shaping so `Query_Modifier` owns score construction and request shaping, with `Fuzzy_Search` acting as the fuzzy scoring service.
	* [Pro] Rewrote soundex function, removed multisite LIMIT cap, and added content scoring for fuzzy search.
	* [Pro] Added filters for fuzzy search truncation parameters.
	* [Pro] Centralized exclusion term parsing logic in Helpers class.
	* [Pro] Custom tables search now supports a FULLTEXT toggle, with improved LIKE-only relevance scoring when FULLTEXT is disabled.
	* [Pro] Improved multisite search query composition: correctly unwraps fuzzy subqueries before UNION assembly and strips only top-level ORDER BY clauses, preventing malformed SQL.
	* [Pro] LIKE term matching in custom tables search now uses an EXISTS subquery to avoid unbounded JOINs when the terms table is not already in scope.
	* [Pro] Database check results are now cached within a request, reducing redundant `SHOW TABLES` queries on pages that check table status multiple times.
	* [Pro] Dashboard popular searches query result is now cached within a request to avoid repeated database hits.
	* Refactored Media Handler with a strategy-based thumbnail resolution chain; now also supports ACF Image fields (Image Array, Image ID, Image URL) and plain text URL fields.
	* Hardened search sanitization and boolean mode validation for more consistent results.
	* Escaped output in settings forms for improved security.

* Bug fixes:
	* [Pro] Fixed localized admin script data keys: removed erroneous `.strings.` nesting that caused the cache-clear confirmation and error dialogs to display `undefined`.
	* Fixed spinner alignment inside action buttons (now displays inline rather than floating).
	* [Pro] Fixed fuzzy LIKE query SQL issues that could generate duplicate `ID` fields in wrapped sub-queries.
	* [Pro] Fixed fuzzy search bypassing FULLTEXT exclusions.
	* [Pro] Fixed inconsistent indentation and table alias qualification in multisite query composition.
	* [Pro] Disabled fuzzy search when boolean operators are present to prevent conflicts.
	* Fixed duplicate search query being executed on every non-seamless search page load.
	* Fixed relevance percentages on paginated search results by stabilizing topscore handling across pages, while reducing unnecessary topscore queries when minimum relevance filtering is not in use.
	* Fixed placeholder attribute escaping in text field rendering.

For previous changelog entries, please refer to the separate changelog.txt file or [Github Releases page](https://github.com/WebberZone/better-search/releases)

== Upgrade Notice ==

= 4.3.2 =
Search highlighting now preserves quoted phrases and skips excluded terms, and adds client-side highlighting for cached pages. Also fixes "Highlight followed links" silently failing behind SSL-terminating proxies.
