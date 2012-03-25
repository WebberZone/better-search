=== Better Search ===
Tags: search, Better Search, related search, search results, heatmap, popular searches, top searches
Contributors: Ajay, Mark Ghosh
Donate link: http://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 3.1
Tested up to: 3.4


Replace the default WordPress search with a contextual search with search results sorted by relevance.

== Description ==

The default WordPress search is limited because it doesn't give you results based on the title or content of the post, but by date.

<a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search</a> replaces the default WordPress search engine with a more powerful search engine. Visitors will find more relevant search results of search terms. You can fine tune results by giving the title or the content more weighting.

Better Search supports templates for perfect integration into your blog template. Template for Twenty Eleven theme included in the package.

Additionally, the plugin will track the searches and allow you present a "search heatmap" of the most popular searches. Support for WordPress widgets will allow you to easiy add this heatmap to your theme's sidebar or footer. 


= Features =
* Automatically replaces your default WordPress search with Better Search results
* Search results sorted by relevance. You can also include pages and attachments in the search results
* Fine tune results by changing the weighting of post title and post content
* Option to turn off relevancy based matching. This will sort results by date
* Display a list of popular search terms (daily and overall) on your blog in the form of a heatmap
* Support for a template file for perfect integration into your blog template
* Clean uninstall if you choose to delete the plugin from within WP-Admin
* Works with caching plugins like WP-Super-Cache and W3 Total Cache


== Upgrade Notice ==

= 1.2 =
Major release: Better template support, redesigned admin page, better WordPress widget support, etc. Please backup your database before upgrading.

== Changelog ==

= 1.2 =
* Updates for better search template compatibility. New template included in this release
* Quick links added in Plugins page in WP-Admin
* Relevance score is now displayed as a percentage
* Fixed: Daily search terms were not being cleared
* Modified: Default search colours for the heatmap are grey and black instead of blue and red
* Modified: Plugin will not add a link to <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search homepage</a> by default
* Modified: New WordPress widget to display the popular searches in your sidebar
* Added: New template file based on Twenty Eleven WordPress theme

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
* Plugin now allows use of template file. Create a file <code>better-search-template.php</code> in your themes folder.
* Pages are also included in the results. You can turn it off in options
* Drafts are no longer included in results
* WordPress Title rewritten
* Added support for localization.

= 1.0 =
* Release


== Installation ==

1. Download the plugin

2. Extract the contents of better-search.zip to wp-content/plugins/ folder. You should get a folder called better-search.

3. Activate the Plugin in WP-Admin. 

4. Goto Settings > Better Search to configure

5. Goto Appearance > Widgets to add the Popular Searches sidebar widgets to your theme

== Screenshots ==

1. Better Search options in WP-Admin


== Frequently Asked Questions ==

If your question isn't listed here, please post a comment at the <a href="http://wordpress.org/tags/better-search?forum_id=10">WordPress.org support forum</a>. I monitor the forums on an ongoing basis. If you're looking for more advanced support, please see <a href="http://ajaydsouza.com/support/">details here</a>.

= Can I customize the output? =

Several customization options are available via the Settings page in WordPress Admin. You can access this via <strong>Settings » Better Search</strong>

The plugin also supports the use of template files within your theme. You can create a file called `better-search-template.php` in your theme's directory and the plugin will use it to display the results.

Take a look at http://ajaydsouza.com/wordpress/plugins/better-search/bsearch-templates for use of custom templates and template tags supported by Better Search
Additionally, I have included a simple template for WordPress Twenty Eleven theme that you can simply drop into the `twentyeleven` folder

= Can you create a search template for my theme? =

Yes I can. However, there are no guarantee on the time frame for the same. Additionally, if I am unable to access the theme for testing, e.g. with paid premium themes, I won't be able to create this template.

If you have already created a template that you would like to share with the WordPress Community, you can <a href="http://ajaydsouza.com/contact/">contact me</a> and I will add it into the package.



