---
slug: better-search-settings-general
title: "Better Search Settings &#8211; General"
products: [better-search]
sections: [01-bs-getting-started]
tags: [better-search,search,settings]
status: publish
order: 0
---

[kbtoc]

The **General** section is the first in the [Better Search](https://webberzone.com/plugins/better-search/) settings screen. It gives you the main options for configuring Better Search on your WordPress blog.

## Enable Seamless Integration

This setting integrates Better Search completely with your theme. When enabled, the plugin ignores the `better-search-template.php` file. Search results will still be sorted by relevance without displaying the relevance percentage.

Disabling this will use the plugin’s in-built advanced search results page, which you can override by creating the `better-search-template.php` file in your theme’s folder. If you’re using FSE, you can create the block template with the name `better-search-template.html`.

## Enable Live Search

Turn this on to [enable live search functionality](https://webberzone.com/support/knowledgebase/enable-live-search/). Live search dynamically displays results as users type into the search box, providing an interactive experience.

## Enable REST API *(Pro only)*

When enabled, this option integrates Better Search with the search REST API endpoint. It allows developers to fetch relevant search results programmatically via the API, offering flexibility for custom integrations. Read the [REST API documentation](https://webberzone.com/support/knowledgebase/better-search-rest-api/) on which parameters you can use.

## Enable Search Tracking

This option allows Better Search to track and display popular search terms. If disabled, the plugin stops recording search queries, which will affect the heatmap and analytics features.

## Track Admin Searches

Enable this option to include search queries made by administrators in the tracking data. Disabling it will prevent admin searches from being logged.

## Track Editor User Group Searches

Similar to admin tracking, this setting logs searches performed by users in the Editor role. Disable it to exclude these searches from being tracked.

## Stop Search Engines from Indexing Search Results Pages

This option adds a `noindex,follow` meta tag to the search results pages, preventing them from being indexed by search engines. It is highly recommended to enable this to avoid duplicate content issues.

## Number Format Count

Enable this setting to format search counts according to your site’s locale. For example, large numbers will be displayed with appropriate separators based on the regional format (e.g., `1,000` in the US or `1.000` in Europe).

## Link to Better Search Plugin Page

When enabled, this setting adds a nofollow link to the Better Search plugin page at the bottom of the popular searches list. It’s not mandatory, but it’s a nice way to support the plugin’s development.
