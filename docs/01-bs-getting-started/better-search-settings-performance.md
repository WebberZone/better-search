---
slug: better-search-settings-performance
title: "Better Search Settings – Performance"
products: [better-search]
sections: [01-bs-getting-started]
tags: [better-search,search,settings]
status: publish
order: 0
---

[kbtoc]

The **Performance** tab in <a href="https://webberzone.com/plugins/better-search/" data-type="page" data-id="168">Better Search</a> includes options designed to optimize how the search results are queried and displayed, especially for high-traffic or large sites. This section provides options for using custom database tables, caching, and fine-tuning query performance.

## Efficient Content Storage and Indexing (ECSI)

Efficient Content Storage and Indexing (ECSI) is a <a href="https://webberzone.com/plugins/better-search/pro/" data-type="page" data-id="8486">Better Search Pro</a> feature that creates a dedicated database table optimized for related content queries. This enhances performance, particularly on sites with a large number of posts or high traffic.

To create the ECSI tables, visit the **Tools** page.

If your database does not support the required features, a compatibility message will be displayed here.

### Use Custom Tables *(Pro only)*

Use dedicated custom tables for related posts queries. This can significantly improve performance on large sites with many posts.

## Optimization

### Enable cache

Caching helps improve search performance by storing results temporarily. When enabled, Better Search uses the WordPress Transients API to cache search results for faster retrieval. It is highly recommended to have this turned on if you have enabled [Fuzzy searches](https://webberzone.com/support/knowledgebase/fuzzy-matches/) or [Multisite Search](https://webberzone.com/support/knowledgebase/multisite-search/).

### Time to cache

This setting allows you to specify the duration (in seconds) for caching search results. By default, it is set to 1 hour (`3600 seconds`). Adjust the time as needed based on your site’s traffic and content update frequency.

### Max Execution Time (Pro)

Maximum time (in milliseconds) allowed for MySQL queries to execute. Setting to 0 disables this limit. Default is 3000 (3 seconds). If a query exceeds this time, Better Search will terminate it and display no results. Setting this value too low may prevent legitimate searches from completing.
