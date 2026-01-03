# Better Search - Supercharge your WordPress search results

[![Better Search WordPress Plugin](https://raw.github.com/ajaydsouza/better-search/master/wporg-assets/banner-1544x500.png)](https://webberzone.com/plugins/better-search/)

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/better-search.svg?style=flat-square)](https://wordpress.org/plugins/better-search/)
[![License](https://img.shields.io/badge/license-GPL_v2%2B-orange.svg?style=flat-square)](https://opensource.org/licenses/GPL-2.0)
[![WordPress Tested](https://img.shields.io/wordpress/v/better-search.svg?style=flat-square)](https://wordpress.org/plugins/better-search/)
[![Required PHP](https://img.shields.io/wordpress/plugin/required-php/better-search?style=flat-square)](https://wordpress.org/plugins/better-search/)
[![Active installs](https://img.shields.io/wordpress/plugin/installs/better-search?style=flat-square)](https://wordpress.org/plugins/better-search/)

__Requires:__ 6.6

__Tested up to:__ 6.9

__License:__ [GPL-2.0+](https://www.gnu.org/licenses/gpl-2.0.html)

__Plugin page:__ [Better Search](https://webberzone.com/plugins/better-search/) | [WordPress.org plugin page](https://wordpress.org/plugins/better-search/)

Better Search replaces the default WordPress search with a better search engine that gives contextual results sorted by relevance

## Description

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

## Screenshots

![General Options](https://raw.github.com/ajaydsouza/better-search/master/wporg-assets/screenshot-1.png)
*Better Search settings page - General Options.*

For more screenshots visit the [WordPress.org screenshots page](https://wordpress.org/plugins/better-search/screenshots/)

## Installation

### WordPress install (the easy way)

1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Better Search"

3. Find the plugin in the list (usually the first result) and click "Install Now"

### Manual install

1. Download the plugin

2. Extract the contents of better-search.zip to wp-content/plugins/ folder. You should get a folder called better-search.

3. Activate the Plugin in WP-Admin.

4. Go to __Better Search__ to configure

Alternatively, search for __Better Search__ from Plugins &raquo; Add New within your WordPress admin.

## Frequently Asked Questions

Check out the [FAQ on the plugin page](https://wordpress.org/plugins/better-search/faq/) for a detailed list of questions and answers.

If your question isn't listed there, please create a new post in the [WordPress.org support forum](https://wordpress.org/support/plugin/better-search). If you're looking for more advanced *paid* support, please see [details here](https://webberzone.com/support/). Paid users receive support via email.

## How can I report security bugs?

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/better-search)
