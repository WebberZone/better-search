=== Better Search - Relevant search results for WordPress ===
Contributors: webberzone, Ajay
Tags: search, Better Search, related search, relevant search, search results, contextual search, heatmap, popular searches, top searches, relevance
Donate link: https://ajaydsouza.com/donate/
Stable tag: 3.2.2
Requires at least: 5.9
Tested up to: 6.4
Requires PHP: 7.4
License: GPLv2 or later

Better Search replaces the default WordPress search with a better search engine that gives contextual results sorted by relevance

== Description ==

Are you looking for a way to improve your WordPress site search and make it easier for your visitors to find what they need? If so, you need **[Better Search](https://webberzone.com/plugins/better-search/)**, the plugin that replaces the default WordPress search engine with a more powerful and relevant one.

**Better Search** is not just a simple search plugin. It is a complete solution that gives you full control over your site search results. You can customize the output, fine tune the relevance, search within different fields and post types, track the popular searches, and much more.

With **Better Search**, you can make your site search more user-friendly and engaging. You can display a "search heatmap" of the most popular searches on your site, either as a widget or a shortcode. You can also use your own template file and CSS styles to match your theme perfectly.

**Better Search** has its own caching system and is also compatible with caching plugins like WP Super Cache and W3 Total Cache, so you don't have to worry about performance issues. It also has a profanity filter that lets you block unwanted words from search queries. And it is translation ready, so you can use it in any language.

Here are some of the main features of **Better Search**:

* **Automatic**: Just activate the plugin and enjoy better search results right away
* **Seamless integration**: No need to edit any code or create custom search templates
* **Relevance**: Sort the results by relevance or date, and assign different weights to title and content
* **Control the results**: Search within title, content, excerpt, meta fields, authors, tags and other taxonomies and comments
* **Popular searches**: Show a heatmap of the most popular searches on your site, either as a widget or a shortcode
* **Customisation**: Use your own template file and CSS styles for the ultimate look and feel
* **Supports cache plugins**: Works seamlessly with caching plugins like WP-Super-Cache and W3 Total Cache
* **Profanity filter**: Filter out any words that you don't want to appear in search queries
* **Translation ready**: Use the plugin in any language

If you want to take your site search to the next level, download **Better Search** today and see the difference for yourself.


= Contribute =
Better Search is also available on [Github](https://github.com/WebberZone/better-search)
So, if you've got some cool feature that you'd like to implement into the plugin or a bug you've been able to fix, consider forking the project and sending me a pull request.

= Plugins by WebberZone =

Better Search is one of the many plugins developed by WebberZone. Check out our other plugins:

* [Top 10](https://wordpress.org/plugins/top-10/) - Track daily and total visits on your blog posts and display the popular and trending posts
* [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) - The ultimate snippet manager for WordPress to create and manage custom HTML, CSS or JS code snippets
* [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) - Create a knowledge base or FAQ section on your WordPress site
* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) - Display related posts on your WordPress blog and feed
* [Auto-Close](https://wordpress.org/plugins/autoclose/) - Automatically close comments, pingbacks and trackbacks and manage revisions on your WordPress site


== Screenshots ==

1. Options in WP-Admin - General options
2. Options in WP-Admin - Search results options
3. Options in WP-Admin - Heatmap options
4. Options in WP-Admin - Custom styles
5. Options in WP-Admin - Tools
6. Better Search widget
7. Better Search Popular Searches table in Admin


== Installation ==

= WordPress install =
1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Better Search"

3. Find the plugin in the list (usually the first result) and click "Install Now"

= Manual install =
1. Download the plugin

2. Extract the contents of better-search.zip to wp-content/plugins/ folder. You should get a folder called better-search.

3. Activate the Plugin in WP-Admin.

4. Goto **Settings > Better Search** to configure

5. Goto **Appearance > Widgets** to add the Popular Searches sidebar widgets to your theme

6. Optionally visit the **Custom Styles** tab to add any custom CSS styles. These are added to `wp_head` on the pages where the posts are displayed


== Frequently Asked Questions ==

If your question isn't listed there, please create a new post in the [WordPress.org support forum](https://wordpress.org/support/plugin/better-search). I monitor the forums regularly. If you're looking for more advanced _paid_ support, please see [details here](https://webberzone.com/support/).

= Can I customize the output? =

Better Search has a huge set of options that help you customize the output or fine tune the results without leaving the comfort of your WordPress site. Goto **Settings > Better Search** to configure.

The plugin also supports the use of template files within your theme. You can create a file called `better-search-template.php` in your theme's directory and the plugin will use it to display the results.

= My search words are getting filtered or *How does the profanity filter work* =

Better Search includes a very cool profanity filter using the script from [Banbuilder](https://github.com/snipe/banbuilder). You can customize which list of words you want to filter out from the Better Search settings page. Find the setting called "Filter these words:". The plugin will automatically strip out both partial and complete references of these words.
You can turn the filter off by emptying the list.

Know of a better profanity filter? Suggest one in the [forums](https://wordpress.org/support/plugin/better-search).


== Changelog ==

= 3.2.2 =

* Enhancements:
	* Use `get_match_sql` instead of `score` in the order by clause
	* Support `meta_query` argument
	* New filter: `better_search_query_date_query`

* Bug fixes:
	* Checkbox in admin page always showed as modified

= 3.2.1 =

Release post: [https://webberzone.com/blog/better-search-v3-2-0/](https://webberzone.com/blog/better-search-v3-2-0/)

* Bug fixes:
	* Make stopwords an array by [@ezific](https://github.com/ezific)
	* Fix bsearch_extract_locations by [@mjsterling](https://github.com/mjsterling)

= 3.2.0 =

* Enhancements/modifications:
	* Only highlight whole words
	* Censor character has been modified to be a blank phrase instead of a space. Additionally multiple spaces will be replaced by a single space.
	* Description of the taxonomy is also searched

* Bug fixes:
	* mySQL error was generated if there were + signs with banned words in BOOLEAN mode
	* Fixed `bsearch_form` shortcode incorrect parameters
	* Queries with apostrophe gave errors
	* PHP 8.1 compatibility
	* Security fix when clearing cache

= 3.1.0 =

Release post: [https://webberzone.com/blog/better-search-v3-1-0/](https://webberzone.com/blog/better-search-v3-1-0/)

* Features:
	* New filter `bsearch_template_query_args` in the default template to modify arguments array passed to `Better_Search_Query`
	* New option **Highlight followed links**: if enabled, the plugin will highlight the search terms on posts/pages when visits them from the search results page
	* `the_bsearch_excerpt()` will now show the relevant part of the excerpt where applicable by default. You can override the arguments by filtering `the_bsearch_excerpt_args`

* Enhancements/modifications:
	* New argument `show_post_types` for `get_bsearch_form()` set as default to false. Arguments can be modified by overriding the filter `bsearch_form_args`
	* Highlighting now uses the referer and no longer requires a special query variable

* Bug fixes:
	* Post thumbnail size was ignored by `the_bsearch_post_thumbnail`
	* HTML entities were displayed instead of being processed
	* Show credit displayed twice
	* `post_type` arguments were incorrectly processed when empty
	* PHP error in `Better_Search::set_topscore()`
	* Resetting settings caused an infinite loop

= 3.0.3 =

Release post: [https://webberzone.com/blog/better-search-v3-0-0/](https://webberzone.com/blog/better-search-v3-0-0/)

* Bug fixes:
	* With seamless mode OFF, by default, all post types were being pulled even those not selected in the settings page
	* With seamless mode ON, caching incorrectly gave the same set of results
	* Exclude categories didn't save properly if the field is blanked out

= 3.0.2 =

* Enhancements/modifications:
	* New options to disable the display of relevance, post type, author, post date and taxonomies list on the custom search results page

* Bug fixes:
	* Fixed excerpt_length setting not used in the default template
	* All required attributes are now passed to the shortcodes

= 3.0.1 =

* Enhancements/modifications:
	* New option to disable the automatic addition of the stylesheet
	* Custom search results page now displays `Sort by` and `Sorted by`
	* New constant `BETTER_SEARCH_VERSION` that is used to enqueue scripts and styles

* Bug fixes:
	* If no `excerpt_length` is passed, the plugin will use the default value from the settings page
	* Better_Search is only initiated if it is a search results page
	* Custom results page excerpt is now highlighted

= 3.0.0 =

* Features:
	* New classes Better_Search_Query and Better_Search. The latter is a wrapper for WP_Query and brings all the power of WP_Query to Better Search. The former is the core class that filters the required functions as well as replaces the older seamless mode implementation
	* New settings to search taxonomies, comments, excerpt, meta, authors and comments. These will all return the posts corresponding where the search term is found in the above fields
	* New parameter `bydate` if set to true will sort posts by date
	* Advanced Search form now displays the select dropdown of post types that allows to search a specific post type
	* Highlight search terms on pages referred from the search results page
	* New stylesheet which is enqueued on search results page and singular posts/pages

* Enhancements/modifications:
	* `hellip` wrapped in `bsearch_hellip` span tag for easy access
	* Transients will be deleted when the plugin is removed

* Bug fixes:
	* Resetting settings caused issues for the default styles and color fields
	* Uninstalling didn't delete the option key from database.

* Deprecated:
	* PHP 5.x support has been dropped and attempting to install the plugin on these installs will throw errors!
	* Multitude of functions have been deprecated. Check the deprecated.php file for the full list. Deprecated functions will also throw up a warning
	* Aggressive Search mode has been removed

== Upgrade Notice ==

= 3.2.2 =
Check the Changelog or release post on https://webberzone.com for complete details
