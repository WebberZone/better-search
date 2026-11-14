---
slug: better-search-settings-general
title: "Better Search Settings: General"
products: [better-search]
sections: ["01-bs-getting-started"]
tags: [better-search, search, settings]
status: publish
order: 0
toc: true
featured_image: "https://webberzone.com/wp-content/uploads/2024/11/Better-Search-General-Settings.webp"
---

[toc]

The **General** section is the first in the [Better Search](https://webberzone.com/plugins/better-search/) settings screen. It gives you the main options for configuring Better Search on your WordPress blog.

## Enable Seamless Integration

This setting integrates Better Search completely with your theme. When enabled, the plugin ignores the `better-search-template.php` file. Search results will still be sorted by relevance without displaying the relevance percentage.

Disabling this will use the plugin’s in-built advanced search results page, which you can override by creating the `better-search-template.php` file in your theme’s folder. If you’re using FSE, you can create the block template with the name `better-search-template.html`.

## Enable REST API *(Pro only)*

> **Moved in v4.5.0.** This setting is now **REST API search integration** on the [Features tab](https://webberzone.com/support/knowledgebase/better-search-feature-manager/).

## Enable Search Tracking

This option allows Better Search to track and display popular search terms. If disabled, the plugin stops recording search queries, which will affect the heatmap and analytics features.

Tracked searches are cleaned first. A search is not recorded if it contains a banned word, if the term is longer than 100 characters, or if the tracking limit below has been reached. Banned terms that are already in the log are left out of the heatmaps.

## Search Tracking Limit per Minute

Sets the maximum number of search terms added to the popular-search log per minute across the whole site. The default is `20`, and the accepted range is 1 to 1,000,000. Searches still work after the limit is reached, but they are not logged until the next minute. Increase the value on high-traffic sites.

To change the limit in code, use the `bsearch_tracker_rate_limit_per_minute` filter:

```php
add_filter( 'bsearch_tracker_rate_limit_per_minute', function () {
    return 60;
} );
```

## Track Admin Searches

Enable this option to include search queries made by administrators in the tracking data. Disabling it will prevent admin searches from being logged.

## Track Editor User Group Searches

Similar to admin tracking, this setting logs searches performed by users in the Editor role. Disable it to exclude these searches from being tracked.

## Exclude Bots from Search Tracking

Does not add searches from known crawler and script user agents to popular searches. It is on by default.

## Stop Search Engines from Indexing Search Results Pages

This option adds a `noindex,follow` meta tag to the search results pages, preventing them from being indexed by search engines. It is highly recommended to enable this to avoid duplicate content issues.

## Number Format Count

Enable this setting to format search counts according to your site’s locale. For example, large numbers will be displayed with appropriate separators based on the regional format (e.g., `1,000` in the US or `1.000` in Europe).

## Link to Better Search Plugin Page

Adds a nofollow link to the Better Search plugin page at the bottom of the popular searches list.
