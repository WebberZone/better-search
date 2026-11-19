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

- `--limit=<number>`: Number of results (default: 10)
- `--post-type=<types>`: Comma-separated post types (default: post)
- `--[no-]use-fulltext`: Force FULLTEXT search ON or OFF
- `--[no-]boolean-mode`: Use boolean mode for FULLTEXT ON or OFF
- `--format=<format>`: Output format: table, json, csv (default: table)
- `--verbose`: Show detailed output

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

- `--format=<format>`: Output format: table, json, csv. Default: table
- `--url=<url>`: Show status for a specific site URL (multisite only)
- `--network`: Show status for all sites in the network
- `--blog-id=<id>`: Show status for a specific blog ID (multisite only)

### Cache

Manage the search results cache.

#### Clear Cache

```bash
wp bsearch cache clear
```

Options:

- `--network`: Clear cache for all sites in the network
- `--force`: Skip confirmation
- `--dry-run`: Preview without making changes
- `--verbose`: Show detailed output

#### Warm Cache

Pre-generate search results for your most popular queries.

```bash
wp bsearch cache warm
```

Options:

- `--limit=<number>`: Number of top searches to warm (default: 50)
- `--dry-run`: Preview without making changes
- `--force`: Force cache warming even if cache exists
- `--verbose`: Show which queries were skipped because they have no results or are already cached

#### Cache Status

```bash
wp bsearch cache status
```

Options:

- `--format=<format>`: Output format: table, json, csv
- `--url=<url>`: Show cache status for a specific site URL (multisite only)
- `--network`: Show cache status for all sites in the network
- `--blog-id=<id>`: Show cache status for a specific blog ID (multisite only)

### Statistics

View, export, and clear the search statistics that Better Search records.

#### View statistics

```bash
wp bsearch stats view
wp bsearch stats view --table=daily --orderby=dp_date --limit=50
wp bsearch stats view --format=json
```

Options:

- `--limit=<number>`: Number of entries to show. Default: 20
- `--table=<table>`: Table to query: `overall` or `daily`. Default: `overall`
- `--orderby=<field>`: Sort field: `searchvar`, `cntaccess`, or `dp_date`. Default: `cntaccess`
- `--order=<direction>`: Sort order: `asc` or `desc`. Default: `desc`
- `--format=<format>`: Output format: table, json, csv. Default: table
- `--url=<url>`: Show statistics for a specific site URL (multisite only)
- `--network`: Show statistics for all sites in the network
- `--blog-id=<id>`: Show statistics for a specific blog ID (multisite only)

#### Top searches

```bash
wp bsearch stats top
wp bsearch stats top --limit=50 --network
```

Shows the most frequent search queries. It accepts `--limit` (default: 20), `--format`, `--url`, `--network`, and `--blog-id`.

#### Clear statistics

```bash
wp bsearch stats clear --force
wp bsearch stats clear --table=daily --before=2026-01-01 --dry-run
```

Options:

- `--before=<date>`: Clear entries before this date, in `YYYY-MM-DD` format
- `--table=<table>`: Table to clear: `overall`, `daily`, or `all`. Default: `all`
- `--force`: Skip confirmation
- `--dry-run`: Show what would be cleared without making changes

The overall table has no date column, so `--before` skips it with a warning and clears the daily table only.

#### Export statistics

```bash
wp bsearch stats export
wp bsearch stats export --file=stats.csv --table=daily --limit=1000
```

Writes the statistics to a CSV file.

Options:

- `--file=<path>`: Path of the export file. Default: `bsearch-stats-<timestamp>.csv`
- `--table=<table>`: Table to export: `overall` or `daily`. Default: `overall`
- `--limit=<number>`: Maximum number of entries to export. Default: all

Files can only be written to the WordPress root, the uploads directory, or the system temp directory.

### Stopwords

Manage the stopwords list from the command line.

```bash
wp bsearch stopwords list
wp bsearch stopwords add the and a an
wp bsearch stopwords remove the and
wp bsearch stopwords clear --force
```

- `list` shows the stopwords. It accepts `--format` (table, json, csv).
- `add <words>...` adds one or more words. Words that already exist are skipped.
- `remove <words>...` removes one or more words.
- `clear` removes every stopword and asks for confirmation unless you pass `--force`.

Every subcommand accepts `--network` to apply to all sites in the network and `--blog-id=<id>` to target a single site (multisite only).

### Database

Inspect and repair the tracking tables and the FULLTEXT indexes.

#### Status

```bash
wp bsearch db status
wp bsearch db status --format=json
wp bsearch db status --network
```

Shows the database engine, whether the `bsearch` and `bsearch_daily` tables exist, how many entries each holds, and how many FULLTEXT indexes are installed. It accepts `--format`, `--network`, and `--blog-id`.

#### Create and recreate tables

```bash
wp bsearch db create-tables --dry-run
wp bsearch db recreate-tables --no-backup
```

`create-tables` creates any missing tracking tables. It accepts `--dry-run` and `--force`; without `--force` it stops when both tables already exist.

`recreate-tables` rebuilds the tracking tables and backs them up first. It asks for confirmation unless you pass `--force`.

Options:

- `--no-backup`: Skip creating backup tables before recreating
- `--force`: Recreate without confirmation
- `--dry-run`: Show what would be done without making changes

#### Indexes

```bash
wp bsearch db indexes status
wp bsearch db indexes create
wp bsearch db indexes recreate
wp bsearch db indexes delete --force
```

Manages the FULLTEXT indexes on the posts table and the custom search tables, and the fuzzy stored functions. `status` is the default when you give no subcommand. `create`, `recreate`, and `delete` accept `--dry-run`, and `delete` asks for confirmation unless you pass `--force`.

### Efficient Content Storage and Indexing (ECSI)

Manage the custom search tables used by [Efficient Content Storage and Indexing](https://webberzone.com/support/knowledgebase/efficient-content-storage-and-indexing/).

#### Status

```bash
wp bsearch ecsi status
wp bsearch ecsi status --format=json
wp bsearch ecsi status --network
```

Options:

- `--format=<format>`: Output format: table, json, csv. Default: table
- `--network`: Show status for all sites in the network

#### Enable and disable

```bash
wp bsearch ecsi enable
wp bsearch ecsi enable --network
wp bsearch ecsi disable --blog-id=3
```

Turns ECSI on or off. Without options, the current site is changed.

Options:

- `--network`: Apply to all sites in the network (multisite only)
- `--blog-id=<id>`: Apply to a specific site (multisite only)

#### Reindex

```bash
wp bsearch ecsi reindex
wp bsearch ecsi reindex --force
wp bsearch ecsi reindex --batch-size=50 --post-type=post,page
```

Indexes published posts into the custom tables.

Options:

- `--force`: Empty the tables before reindexing
- `--batch-size=<size>`: Posts processed per batch, from 1 to 1000. Default: 100
- `--post-type=<types>`: Comma-separated post types to index. Default: all public post types
- `--dry-run`: Show what would be indexed without making changes
- `--verbose`: Show per-post output instead of a progress bar
- `--network`: Reindex all sites in the network (multisite only)

Each post that cannot be written is reported as a warning. From Better Search Pro 4.5.3, the command finishes processing every selected site, then exits with an error when any post failed, so scripts and deployments can detect an incomplete index. The failed posts are listed under **Better Search → Tools**, where you can retry them after fixing the database problem.

#### Create and drop tables

```bash
wp bsearch ecsi create
wp bsearch ecsi create --dry-run
wp bsearch ecsi drop --force
```

`create` creates the custom tables; `--dry-run` shows what would be created. `drop` permanently deletes the custom tables and asks for confirmation unless `--force` is supplied.

### Settings

Read, change, export, and import Better Search settings.

```bash
wp bsearch settings get tracker_limit
wp bsearch settings set tracker_limit 60
wp bsearch settings export --file=bsearch-settings.json
wp bsearch settings import bsearch-settings.json --dry-run
```

`get` accepts `--format` with `table`, `json`, `csv`, or `value` (default). `set` accepts `--type` with `string`, `int`, `float`, `bool`, or `array`, and detects the type when you omit it.

Values from `set` and `import` are validated and sanitized by the same rules as the settings page. Unknown keys are rejected by `set` and skipped by `import`, which lists them in a warning.

Without `--merge`, `import` replaces the stored settings, and any setting missing from the file returns to its default. Use `--merge` to change only the settings in the file. Files can only be read from or written to the WordPress root, the uploads directory, or the system temp directory.

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
