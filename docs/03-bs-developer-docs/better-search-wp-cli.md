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
wp bsearch <command> <subcommand> [options]
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
- `--[no-]use-fulltext` — Force FULLTEXT search ON or OFF
- `--[no-]boolean-mode` — Use boolean mode for FULLTEXT ON or OFF
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

### Efficient Content Storage and Indexing (ECSI)

Manage the custom search tables used by [Efficient Content Storage and Indexing](https://webberzone.com/support/knowledgebase/efficient-content-storage-and-indexing/).

#### Status

```bash
wp bsearch ecsi status
wp bsearch ecsi status --format=json
wp bsearch ecsi status --network
```

Options:

- `--format=<format>` — Output format: table, json, csv. Default: table
- `--network` — Show status for all sites in the network

#### Enable and disable

```bash
wp bsearch ecsi enable
wp bsearch ecsi enable --network
wp bsearch ecsi disable --blog-id=3
```

Turns ECSI on or off. Without options, the current site is changed.

Options:

- `--network` — Apply to all sites in the network (multisite only)
- `--blog-id=<id>` — Apply to a specific site (multisite only)

#### Reindex

```bash
wp bsearch ecsi reindex
wp bsearch ecsi reindex --force
wp bsearch ecsi reindex --batch-size=50 --post-type=post,page
```

Indexes published posts into the custom tables.

Options:

- `--force` — Empty the tables before reindexing
- `--batch-size=<size>` — Posts processed per batch, from 1 to 1000. Default: 100
- `--post-type=<types>` — Comma-separated post types to index. Default: all public post types
- `--dry-run` — Show what would be indexed without making changes
- `--verbose` — Show per-post output instead of a progress bar
- `--network` — Reindex all sites in the network (multisite only)

Each post that cannot be written is reported as a warning. From Better Search Pro 4.5.3, the command finishes processing every selected site, then exits with an error when any post failed, so scripts and deployments can detect an incomplete index. The failed posts are listed under **Better Search → Tools**, where you can retry them after fixing the database problem.

#### Create and drop tables

```bash
wp bsearch ecsi create
wp bsearch ecsi create --dry-run
wp bsearch ecsi drop --force
```

`create` creates the custom tables; `--dry-run` shows what would be created. `drop` permanently deletes the custom tables and asks for confirmation unless `--force` is supplied.

### AI Answers

Inspect and operate Better Search Pro AI Answers from WP-CLI.

#### AI Status

```bash
wp bsearch ai status
wp bsearch ai status --format=json
```

The status includes whether AI Answers is enabled, provider availability, selected and fallback providers, provider cooldown state, cached-answer count, today's provider requests, the daily cap, and the number of recorded questions.

#### Check Provider

Force a live provider availability check:

```bash
wp bsearch ai check-provider
```

#### Ask a Question

```bash
wp bsearch ai ask "How do I configure search?"
wp bsearch ai ask "How do I configure search?" --format=json
```

The command uses the configured provider chain and returns the answer, sources, cache state, and failure reason.

#### Clear the AI Cache

```bash
wp bsearch ai cache clear
```

This removes cached answers and provider state.

#### Content Gaps

```bash
wp bsearch ai gaps list
wp bsearch ai gaps list --from=2026-09-01 --to=2026-09-30 --format=json
wp bsearch ai gaps export > content-gaps.csv
wp bsearch ai gaps export --format=json
wp bsearch ai gaps clear --yes
```

The list command supports `--from`, `--to`, `--limit`, `--page`, and `--format`. Export supports CSV and JSON. Clear deletes every recorded question and prompts for confirmation unless `--yes` is supplied.
