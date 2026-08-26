---
slug: better-search-wp-cli
title: "Better Search Pro CLI Overview"
products: [better-search]
sections: ["03-bs-developer-docs"]
tags: [better-search, pro, wp-cli]
status: publish
order: 0
toc: true
---

[toc]

Better Search Pro CLI (BSP-CLI) was introduced in [Better Search Pro](https://webberzone.com/plugins/better-search/pro/) v4.3.0, allowing you to manage search operations without touching the admin dashboard. If you’re running large-scale WordPress installations or automating deployments, these commands handle everything from cache warming to database maintenance.

## About WP-CLI

WP-CLI is a set of command-line tools for managing WordPress installations. You can update plugins, configure multisite installations, and much more, all without using a web browser. For more information, visit the <a href="http://wp-cli.org/" target="_blank" rel="noreferrer noopener">official WP-CLI website</a>.

## Getting Started with BSP-CLI

To begin using BSP-CLI, ensure that WP-CLI is installed and that you are running Better Search Pro 4.3.0 or later. The CLI commands are accessed through the `wp bsearch` command. For a complete list of available commands, type `wp bsearch` in your command-line interface.

## Command Structure

All commands use the `wp bsearch` namespace:

```bash
wp bsearch <command> <subcommand> [[options]]
```

## Available Commands

### Search

Run search queries directly from the command line.

#### Basic Search

```bash
wp bsearch search "search term"
```

#### Options

- `--limit=<number>` — Number of results (default: 10)
- `--post-type=<types>` — Comma-separated post types (default: post)
- `--[[no-]]use-fulltext` — Force FULLTEXT search ON or OFF
- `--[[no-]]boolean-mode` — Use boolean mode for FULLTEXT ON or OFF
- `--format=<format>` — Output format: table, json, csv (default: table)
- `--verbose` — Show detailed output

#### Examples

```bash
# Search for "WordPress" and show 20 results
wp bsearch search "WordPress" --limit=20

# Search across multiple post types
wp bsearch search "tutorial" --post-type=post,page

# Get results in JSON format
wp bsearch search "mysql" --format=json
```

### Status

Show comprehensive plugin status and configuration.

```bash
wp bsearch status
```

Options:

- `--format=<format>` — Output format: table, json, csv
- `--verbose` — Show detailed information
- `--network` — Show status for all network sites

### Cache

Manage the search results cache.

#### Clear Cache

```bash
wp bsearch cache clear
```

Options:

- `--network` — Clear cache for all sites in the network
- `--force` — Skip confirmation
- `--dry-run` — Preview without making changes
- `--verbose` — Show detailed output

#### Warm Cache

Pre-generate search results for your most popular queries.

```bash
wp bsearch cache warm
```

Options:

- `--limit=<number>` — Number of top searches to warm (default: 50)
- `--batch-size=<size>` — Batch size for processing (default: 100)
- `--dry-run` — Preview without making changes
- `--force` — Force cache warming even if cache exists

#### Cache Status

```bash
wp bsearch cache status
```

Options:

- `--format=<format>` — Output format: table, json, csv
