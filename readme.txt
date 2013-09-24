=== Better Search ===
Tags: search, Better Search, related search, search results, heatmap, popular searches, top searches, relevance
Contributors: Ajay
Donate link: http://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 3.1
Tested up to: 3.7
License: GPLv2 or later


Better Search replaces the default WordPress search with a better search that gives contextual results sorted by relevance

== Description ==

The default WordPress search is limited because it gives you results by date and not by relevance.

<a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search</a> replaces the default WordPress search engine with a more powerful search engine that gives search results relevant to the title and content of the post. This means that visitors to your blog will find will find what they are looking for quicker than if you didn't have **Better Search** installed.

Custom post type support means, visitors can search through your site for more than just posts.

The plugin is packed with options to allow you to easily customise the output. You can also fine tune the results by assigning a greater weith to either the title or the content.

Better Search supports templates for perfect integration into your blog template. Template for Twenty Eleven theme included in the package.

Additionally, the plugin also tracks the searches and you to display a "search heatmap" of the most popular searches. Support for WordPress widgets will allow you to easily add this heatmap to your theme's sidebar or footer.


= Features =
* **Automatic**: Once activated, Better Search will automatic replace your default WordPress search with more relevant search results
* **Relevance**: Search results sorted by relevance automatically sorted by relevance. You can also turn off relevancy based searching, in which results are sorted by date
* **Control the results**: Fine tune results by changing the weighting of post title and post content. Turn on BOOLEAN search to override the default NATURAL LANGUAGE search of mySQL
* **Popular searches**: Find out what visitors are searching for on your blog. Display a list of popular search terms (daily and overall) on your blog in the form of a heatmap. Widget support for easy integration in your theme
* **Customisation**: Support for a template file for perfect integration into your blog template. Alternatively, just input your own CSS styles in the *Custom Styles* tab in the Settings Page. Check the FAQ for more information
* **Supports cache plugins**: Works with caching plugins like WP-Super-Cache and W3 Total Cache


== Upgrade Notice ==

= 1.3.1 =
Fixed: PHP Notices

= 1.3 =
Fixed: Security fix; new admin interface; custom post type support; BOOLEAN MODE mySQL search; 
For other changes, check out the changelog
 

== Changelog ==

= 1.3.1 =
Fixed: PHP Notices

= 1.3 =
* Modified: Revamp of admin interface of the plugin 
* Added: New option to activate BOOLEAN mode of mySQL FULLTEXT searching. <a href="https://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html" target="_blank">Check the mySQL docs for further information on this</a>
* Added: Custom post type support. Now choose what visitors are allowed to search
* Added: Links in the search heatmap are no-follow by default. You can turn this off in the Settings page
* Added: Option to make make heatmap links to open in a new window
* Added: Option to turn off the display of the heatmap on the results page
* Added: New CSS classes for heatmaps on the search results page - `heatmap_daily` and `heatmap_overall`
* Fixed: Possible cross-site request forgery issue in the Settings page

= 1.2.1 =
* Fixed: "Missing argument" error for heatmaps

= 1.2 =
* New: Updates for better search template compatibility. New template included in this release
* Modified: Relevance score is now displayed as a percentage
* Fixed: Daily search terms were not being cleared
* Modified: Default search colours for the heatmap are grey and black instead of blue and red
* Modified: Plugin will not add a link to <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search homepage</a> by default
* Modified: New WordPress widget to display the popular searches in your sidebar
* New: New template file based on Twenty Eleven WordPress theme

= 1.1.7 =
* Two new fulltext indexes added... the first step to better control on search results

= 1.1.6 = 
* Bug fixed in display of daily search heatmap

= 1.1.5 =
* Fixed a bug

= 1.1.4 = 
* Fixed a bug

= 1.1.3 =
* Critical Fix: Possible security hole

= 1.1.2 =
* Fixed: Searches not tracked when not using template

= 1.1.1 =
* Fixed: Certain search terms didn't work

= 1.1 =
* Plugin now allows use of template file. Create a file `better-search-template.php` in your themes folder.
* Pages are also included in the results. You can turn it off in options
* Drafts are no longer included in results
* WordPress Title rewritten
* Added support for localization.

= 1.0 =
* Release


== Installation ==

= WordPress install =
1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Better Search" and select "Keyword" from the dropdown

3. Find the plugin in the list (usually the first result) and click "Install Now"

= Manual install =
1. Download the plugin

2. Extract the contents of better-search.zip to wp-content/plugins/ folder. You should get a folder called better-search.

3. Activate the Plugin in WP-Admin. 

4. Goto **Settings > Better Search** to configure

5. Goto **Appearance > Widgets** to add the Popular Searches sidebar widgets to your theme

6. Optionally visit the **Custom Styles** tab to add any custom CSS styles. These are added to `wp_head` on the pages where the posts are displayed


== Screenshots ==

1. Options in WP-Admin - General options
2. Options in WP-Admin - Output options
3. Options in WP-Admin - Custom Styles

== Frequently Asked Questions ==

If your question isn't listed here, please post a comment at the <a href="hhttp://wordpress.org/support/plugin/better-search">WordPress.org support forum</a>. I monitor the forums on an ongoing basis. If you're looking for more advanced paid support, please see <a href="http://ajaydsouza.com/support/">details here</a>.

= Can I customize the output? =   

All options can be customized within the Options page in WP-Admin itself

The plugin also supports the use of template files within your theme. You can create a file called `better-search-template.php` in your theme's directory and the plugin will use it to display the results.

Take a look at http://ajaydsouza.com/wordpress/plugins/better-search/bsearch-templates for use of custom templates and template tags supported by Better Search
Additionally, I have included a simple template for WordPress Twenty Eleven theme that you can simply drop into the `twentyeleven` folder

= Can you create a search template for my theme? =

Yes I can. However, there are no guarantee on the time frame for the same. Additionally, if I am unable to access the theme for testing, e.g. with paid premium themes, I won't be able to create this template.

If you have already created a template that you would like to share with the WordPress Community, you can <a href="http://ajaydsouza.com/contact/">contact me</a> and I will add it into the package.

