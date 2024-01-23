# Better Search - Supercharge your WordPress search results

[![Better Search WordPress Plugin](https://raw.github.com/ajaydsouza/better-search/master/wporg-assets/banner-1544x500.png)](https://webberzone.com/plugins/better-search/)

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/better-search.svg?style=flat-square)](https://wordpress.org/plugins/better-search/)
[![License](https://img.shields.io/badge/license-GPL_v2%2B-orange.svg?style=flat-square)](https://opensource.org/licenses/GPL-2.0)
[![WordPress Tested](https://img.shields.io/wordpress/v/better-search.svg?style=flat-square)](https://wordpress.org/plugins/better-search/)
[![Required PHP](https://img.shields.io/wordpress/plugin/required-php/better-search?style=flat-square)](https://wordpress.org/plugins/better-search/)
[![Active installs](https://img.shields.io/wordpress/plugin/installs/better-search?style=flat-square)](https://wordpress.org/plugins/better-search/)

__Requires:__ 5.9

__Tested up to:__ 6.4

__License:__ [GPL-2.0+](https://www.gnu.org/licenses/gpl-2.0.html)

__Plugin page:__ [Better Search](https://webberzone.com/plugins/better-search/) | [WordPress.org plugin page](https://wordpress.org/plugins/better-search/)

Better Search replaces the default WordPress search with a better search engine that gives contextual results sorted by relevance

## Description

Are you looking for a way to improve your WordPress site search and make it easier for your visitors to find what they need? If so, you need __[Better Search](https://webberzone.com/plugins/better-search/)__, the plugin that replaces the default WordPress search engine with a more powerful and relevant one.

**Better Search** is not just a simple search plugin. It is a complete solution that gives you full control over your site search results. You can customize the output, fine tune the relevance, search within different fields and post types, track the popular searches, and much more.

With __Better Search__, you can make your site search more user-friendly and engaging. You can display a "search heatmap" of the most popular searches on your site, either as a widget or a shortcode. You can also use your own template file and CSS styles to match your theme perfectly.

**Better Search** has its own caching system and is also compatible with caching plugins like WP Super Cache and W3 Total Cache, so you don't have to worry about performance issues. It also has a profanity filter that lets you block unwanted words from search queries. And it is translation ready, so you can use it in any language.

Here are some of the main features of __Better Search__:

* __Automatic__: Just activate the plugin and enjoy better search results right away
* __Seamless integration__: No need to edit any code or create custom search templates
* __Relevance__: Sort the results by relevance or date, and assign different weights to title and content
* __Control the results__: Search within title, content, excerpt, meta fields, authors, tags and other taxonomies and comments
* __Popular searches__: Show a heatmap of the most popular searches on your site, either as a widget or a shortcode
* __Customisation__: Use your own template file and CSS styles for the ultimate look and feel
* __Supports cache plugins__: Works seamlessly with caching plugins like WP-Super-Cache and W3 Total Cache
* __Profanity filter__: Filter out any words that you don't want to appear in search queries
* __Translation ready__: Use the plugin in any language

If you want to take your site search to the next level, download __Better Search__ today and see the difference for yourself.

## mySQL FULLTEXT indices

On activation, the plugin creates three mySQL FULLTEXT indices (or indexes) in the `*_posts` table. These are for `post_content`, `post_title` and `(post_title,post_content)`. If you’re running a multisite installation, then this is created for each of the blogs on activation. All these indices occupy space in your mySQL database but are essential for the plugin to run.

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

Check out the [FAQ on the plugin page](https://wordpress.org/plugins/better-search/faq/) for a detailed list of questions and answers

If your question isn't listed there, please create a new post in the [WordPress.org support forum](https://wordpress.org/support/plugin/better-search). I monitor the forums regularly. If you're looking for more advanced *paid* support, please see [details here](https://webberzone.com/support/).
