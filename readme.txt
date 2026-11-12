=== Better Search - Relevant search results for WordPress ===
Contributors: webberzone, Ajay
Tags: search, Better Search, related search, relevant search, relevance
Donate link: https://wzn.io/donate-wz
Stable tag: 4.5.4
Requires at least: 6.9
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

= Multilingual sites =

Better Search supports WPML and Polylang and detects TranslatePress's current language when caching search results. Caches are separated by language so results from one language are not reused in another.

TranslatePress translates the displayed results with the rest of the page; it does not create a separate search index of translated text. Better Search Pro's core WordPress REST search responses and live-search suggestions are translated using the active TranslatePress language.

Search-term highlighting can prevent TranslatePress from translating a complete title because it splits the text with markup. Disable search-term highlighting if translated titles remain in the default language.

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

= 4.6.0 =

Release date: 14 November 2026

**Added**

* Count of searches that returned no results on the dashboard for each period. Only the number of searches is stored, in a new `bsearch_no_results_daily` table.
* [Pro] Search terms that returned no results on the dashboard: the top 20 for each period, for a single day from the chart, and across the network. Terms are recorded from this version onward in a new `bsearch_no_results` table.
* [Pro] 16 Abilities API abilities for cache, database, custom index, statistics, settings and stopwords. All require `manage_options`; those that delete or overwrite data require `confirm`.
* [Pro] **Daily cap for anonymous visitors** setting for AI answers, keeping the rest of the daily request cap for logged-in users. Off by default.

**Changed**

* [Pro] `wp bsearch settings set` and `wp bsearch settings import` validated and sanitized values like the settings page and rejected unknown keys. An import without `--merge` reset settings missing from the file to their defaults.
* [Pro] Limited anonymous AI answers to a quarter of the daily request cap in any one hour. Use `bsearch_ai_hourly_share` to change it.

**Security**

* Hardened search tracking: terms are cleaned and checked against banned words, the tracking setting is obeyed, terms over 100 characters are rejected and known bots are excluded by default.
* Limited search tracking to 20 tracked searches a minute per site by default. Searches still run beyond the limit but are not logged. Use the `tracker_limit` setting or `bsearch_tracker_rate_limit_per_minute` to change it.
* Excluded banned terms already in the log from heatmaps.
* [Pro] Excluded banned terms already in the log from fuzzy search suggestions.

**Fixed**

* Backslashes in Custom CSS and other settings, such as icon font codes, were removed when settings were saved and on the front end.
* Quotes in Custom CSS were converted to HTML entities in the styles added for the Search Box widget.
* Importing a settings file containing only some settings deleted the settings missing from the file.
* Settings imports were not sanitized like settings saved from the settings page, and invalid files were accepted.
* Live search messages could not be translated because they used the wrong text domain.

= Earlier versions =

For the changelog of earlier versions, please refer to the [releases page on GitHub](https://github.com/WebberZone/better-search/releases).

== Upgrade Notice ==

= 4.6.0 =
Adds a count of searches with no results to the dashboard, and in Pro the terms behind it, new Abilities API tools and a daily AI cap for anonymous visitors. Search tracking is also hardened and rate limited. No action needed on existing sites.
