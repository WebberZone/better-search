---
slug: better-search-dashboard
title: "Better Search dashboard"
products: [better-search]
sections: ["01-bs-getting-started"]
tags: [better-search, dashboard, statistics]
status: publish
order: 0
toc: true
---

[toc]

The dashboard in [Better Search](https://webberzone.com/plugins/better-search/) shows what visitors search for and which searches found nothing. Open **Better Search → Dashboard** to see it.

## Daily searches chart

The chart plots the number of searches for each day in a date range. Enter a start and end date above the chart and select **Update**. With [Better Search Pro](https://webberzone.com/plugins/better-search/pro/), click a bar to see the top searches for that day. This needs **Dashboard chart drill-down** on the [Features tab](https://webberzone.com/support/knowledgebase/better-search-feature-manager/).

## Historical searches

The tabs under **Historical searches** cover **Today**, **Yesterday**, **Last 7 days**, **Last 14 days**, **Last 30 days** and **All time**. Each tab lists the most popular searches for the period, with a link to **View all popular searches** for the full list.

## Searches with no results

Each tab ends with a **Searches with no results** section that counts the searches in that period that returned nothing. Use it to find content gaps, spelling variants and terms worth a redirect.

The free plugin stores only a daily total in the `bsearch_no_results_daily` table. It does not store the search terms.

### Terms behind the count *(Pro only)*

Better Search Pro also lists the top 20 search terms that returned no results for the period, with the number of searches for each. Each term links to the search results page on your site.

Terms are recorded from the day Pro is activated. If a period contains no-result searches from before that, the count is shown and a note explains that the terms were not recorded. Pro stores one row for each term per day in the `bsearch_no_results` table, so the table grows with the number of distinct terms rather than with traffic.

On a multisite network, the network dashboard shows the same section across the network when **Network admin dashboard & stats** is on.

## What is recorded

No-result searches use the same tracker as popular searches, so the tracking settings apply. See [General options](https://webberzone.com/support/knowledgebase/better-search-settings-general/) for **Enable search tracking**, the admin and editor options, **Exclude bots from search tracking** and the per-minute tracking limit.

The tracker checks that the results page was rendered with no results before it counts one, so the figure is not affected by cached pages or by direct calls to the tracker.

## See also

- [Better Search Heatmap options](https://webberzone.com/support/knowledgebase/better-search-settings-heatmap/)
- [Search redirects](https://webberzone.com/support/knowledgebase/search-redirects/)
- [Did you mean spelling suggestions](https://webberzone.com/support/knowledgebase/did-you-mean-spelling-suggestions/)
