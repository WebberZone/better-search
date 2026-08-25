---
slug: better-search-feature-manager
title: "Better Search Feature Manager"
products: [better-search]
sections: ["01-bs-getting-started"]
tags: [better-search, features, settings]
status: publish
toc: true
---

[toc]

The Feature Manager, introduced in [Better Search](https://webberzone.com/plugins/better-search/) 4.4.0, lets you turn off optional features you are not using. When a feature is turned off, its code never loads — saving memory and removing its settings from the rest of the interface.

Live search is the only feature that starts turned off. All other features are enabled by default. The Features tab appears at **Better Search → Settings**.

## Features

These features are available in the free plugin.

### Classic widgets

Registers the classic Search Box and Search Heatmap widgets. Turn this off if you only use the shortcodes or block patterns.

### Shortcodes

Registers the `[[bsearch_form]]` and `[[bsearch_heatmap]]` shortcodes.

### Block patterns

Registers the Better Search block patterns (search form, search results, query loop) for the block editor.

### Live search

Enables the live search feature on the search form, including its AJAX endpoint. Default: off.

## Pro Features

These features require [Better Search Pro](https://webberzone.com/plugins/better-search/pro/).

### Fuzzy search *(Pro only)*

Loads the fuzzy search subsystem (MySQL functions, index tools and admin notices). Use the **Fuzzy search level** setting on the Search tab to control how loosely it matches.

### "Did you mean" suggestions *(Pro only)*

Suggests a corrected search term, drawn from your own search log, when a search returns zero results. Configure the mode and thresholds on the Search tab. Default: off.

### Search redirects *(Pro only)*

Lets you send searches for chosen keywords straight to a specific post, page or URL. Configure the rules on the Redirects tab.

### Custom index tables (ECSI) *(Pro only)*

Enables the dedicated search index table subsystem, including the Tools tab reindexing and InnoDB conversion tools.

### Multisite search *(Pro only)*

Enables cross-site search across multiple blogs in a WordPress Multisite network.

### Network admin dashboard & stats *(Pro only)*

Adds the network admin dashboard and statistics pages on Multisite installs.

### Dashboard chart drill-down *(Pro only)*

Lets you click a bar in the daily searches chart to view the top searches for that day.

### WP-CLI commands *(Pro only)*

Registers the `bsearch` WP-CLI commands.

## How feature gating works

When you turn off a feature, the plugin stops loading that feature's PHP classes on the next request. Settings that belong to a turned-off feature stay visible on the settings page and in the setup wizard — they are marked as having no effect and are not saved.

Turning a feature back on restores all its settings and behavior immediately. You can change these toggles at any time.
