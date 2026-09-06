---
slug: enable-live-search
title: "Enable AJAX Live Search"
products: [better-search]
sections: ["02-bs-advanced"]
tags: [better-search, live-search, search]
status: publish
order: 0
---

## What is Live Search?

Live search, often called Ajax search, dynamically updates search results as users type into the search bar. This provides instant feedback, allowing users to find what they’re looking for without needing to submit the form or reload the page.

## Why Enable Live Search?

Enabling live search improves the user experience by:

- **Speeding up searches**: Results appear instantly as users type.
- **Reducing friction**: No need to click a button or wait for a page to reload.
- **Increasing engagement**: Users can quickly refine their queries based on immediate results.

It benefits content-heavy sites where users frequently search for specific posts, products, or pages.

## How to Enable Live Search

[Better Search](https://webberzone.com/plugins/better-search/) includes live search in both the free and Pro versions. Follow these steps:

1. Go to **Better Search → Settings** in your WordPress admin.
2. Open the **Features** tab and turn on **Live search**.
3. Save your changes.

Once enabled, Better Search automatically takes over your search forms and applies live Ajax functionality. You don't need to add extra code or scripts.

## Performance and limits

Live search fires on every keystroke, so Better Search keeps each request cheap.

- **Responses are cached.** A successful live search response is stored in a transient for five minutes, keyed on the query, the site locale, and the blog ID. Repeat searches for the same term are served from that cache without touching the database.
- **Short queries are rejected on the server.** A query shorter than the **Minimum characters** setting returns an empty response immediately. That setting is Pro only; on the free plugin the floor is three characters. This check used to run only in the browser, so a crafted request could still run a full search.
- **Queries are capped at 128 characters.** Anything longer is truncated before the search runs.
- **No result count is calculated.** Live search asks only for the posts it will show, skipping the extra count query a normal search performs.

Change the cache duration with the `bsearch_live_search_cache_time` filter. Return `0` to disable live search caching.

```php
// Cache live search responses for one minute instead of five.
add_filter( 'bsearch_live_search_cache_time', fn() => MINUTE_IN_SECONDS );
```
