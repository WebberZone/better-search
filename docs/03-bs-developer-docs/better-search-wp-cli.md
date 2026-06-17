---
slug: better-search-wp-cli
title: "Better Search Pro CLI Overview"
products: [better-search]
sections: [03-bs-developer-docs]
tags: [better-search,pro,wp-cli]
status: publish
order: 0
---

[kbtoc]

Better Search Pro CLI (BSP-CLI) was introduced in <a href="https://webberzone.com/plugins/better-search/pro/" data-type="page" data-id="8486">Better Search Pro</a> v4.3.0, allowing you to manage search operations without touching the admin dashboard. If you’re running large-scale WordPress installations or automating deployments, these commands handle everything from cache warming to database maintenance.

## About WP-CLI

WP-CLI is a powerful set of command-line tools for managing WordPress installations. You can update plugins, configure multisite installations, and much more, all without using a web browser. For more information, visit the <a href="http://wp-cli.org/" target="_blank" rel="noreferrer noopener">official WP-CLI website</a>.

## Getting Started with BSP-CLI

To begin using BSP-CLI, ensure that WP-CLI is installed and that you are running Better Search Pro 4.3.0 or later. The CLI commands are accessed through the `wp bsearch` command. For a complete list of available commands, type `wp bsearch` in your command-line interface.

## Command Structure

All commands use the `wp bsearch` namespace:

``` bash
wp bsearch <command> <subcommand> [options]
```

## Available Commands

### Search

Run search queries directly from the command line.

#### Basic Search

``` bash
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

``` bash
# Search for "WordPress" and show 20 results
wp bsearch search "WordPress" --limit=20

# Search across multiple post types
wp bsearch search "tutorial" --post-type=post,page

# Get results in JSON format
wp bsearch search "mysql" --format=json
```

### Status

Show comprehensive plugin status and configuration.

``` bash
wp bsearch status
```

Options:

- `--format=<format>` — Output format: table, json, csv
- `--verbose` — Show detailed information
- `--network` — Show status for all network sites

### Cache

Manage the search results cache.

#### Clear Cache

``` bash
wp bsearch cache clear
```

Options:

- `--network` — Clear cache for all sites in the network
- `--force` — Skip confirmation
- `--dry-run` — Preview without making changes
- `--verbose` — Show detailed output

#### Warm Cache

Pre-generate search results for your most popular queries.

``` bash
wp bsearch cache warm
```

Options:

- `--limit=<number>` — Number of top searches to warm (default: 50)
- `--batch-size=<size>` — Batch size for processing (default: 100)
- `--dry-run` — Preview without making changes
- `--force` — Force cache warming even if cache exists

#### Cache Status

``` bash
wp bsearch cache status
```

Options:

- `--format=<format>` — Output format: table, json, csv

### Database (db)

Manage database tables and FULLTEXT indexes.

#### Database Status

``` bash
wp bsearch db status
```

Options:

- `--format=<format>` — Output format: table, json, csv
- `--network` — Show status for all network sites
- `--blog-id=<id>` — Specific blog ID (multisite only)

#### Create Tables

Creates the Better Search tables used for tracking. *These are not the custom table (<a href="https://webberzone.com/support/knowledgebase/efficient-content-storage-and-indexing/" data-type="wz_knowledgebase" data-id="8902">ECSI</a>) tables.*

``` bash
wp bsearch db create-tables
```

Options:

- `--dry-run` — Preview without creating
- `--force` — Force creation even if tables exist

#### Recreate Tables

``` bash
wp bsearch db recreate-tables
```

Options:

- `--backup` — Create backup tables (default: true)
- `--no-backup` — Skip backup creation
- `--force` — Skip confirmation
- `--dry-run` — Preview without making changes

#### Manage FULLTEXT Indexes

Check status:

``` bash
wp bsearch db indexes status
```

Create indexes:

``` bash
wp bsearch db indexes create
```

Delete indexes:

``` bash
wp bsearch db indexes delete --force
```

Recreate indexes:

``` bash
wp bsearch db indexes recreate
```

### Statistics (stats)

View and manage search query statistics.

#### View Statistics

``` bash
wp bsearch stats view
```

Options:

- `--limit=<number>` — Number of entries (default: 20)
- `--table=<table>` — Table to query: overall, daily (default: overall)
- `--orderby=<field>` — Order by: searchvar, cntaccess, dp_date (default: cntaccess)
- `--order=<direction>` — Sort order: asc, desc (default: desc)
- `--format=<format>` — Output format: table, json, csv

#### Top Searches

``` bash
wp bsearch stats top --limit=50
```

#### Clear Statistics

``` bash
wp bsearch stats clear --force
```

Options:

- `--before=<date>` — Clear entries before date (YYYY-MM-DD)
- `--table=<table>` — Which table: overall, daily, all (default: all)
- `--force` — Skip confirmation
- `--dry-run` — Preview without clearing

#### Export Statistics

``` bash
wp bsearch stats export
```

Options:

- `--file=<path>` — Export file path (default: bsearch-stats-{timestamp}.csv)
- `--table=<table>` — Table to export: overall, daily
- `--limit=<number>` — Maximum entries to export (default: all)

### Settings

Manage plugin settings from the command line.

#### Export Settings

``` bash
wp bsearch settings export
```

Options:

- `--file=<path>` — Export file path (default: bsearch-settings-{timestamp}.json)

#### Import Settings

``` bash
wp bsearch settings import settings.json
```

Options:

- `--merge` — Merge with existing settings instead of replacing
- `--dry-run` — Preview without importing

#### Get Setting

``` bash
wp bsearch settings get seamless
```

Options:

- `--format=<format>` — Output format: value, table, json, csv (default: value)

#### Set Setting

``` bash
wp bsearch settings set seamless true --type=bool
```

Options:

- `--type=<type>` — Value type: string, int, bool, array (default: auto-detect)

### Custom Tables (ECSI)

Manage custom search index tables. <a href="https://webberzone.com/support/knowledgebase/efficient-content-storage-and-indexing/" data-type="wz_knowledgebase" data-id="8902">Learn more about ECSI</a>.

#### Create Custom Tables

``` bash
wp bsearch ecsi create
```

Options:

- `--dry-run` — Preview without creating
- `--verbose` — Show detailed output

#### Drop Custom Tables

``` bash
wp bsearch ecsi drop --force
```

#### Tables Status

``` bash
wp bsearch ecsi status
```

Options:

- `--format=<format>` — Output format: table, json, csv
- `--network` — Show status for all network sites

#### Reindex Posts

``` bash
wp bsearch ecsi reindex
```

Options:

- `--force` — Force reindex (truncate tables first)
- `--batch-size=<size>` — Batch size for processing (default: 100)
- `--post-type=<types>` — Comma-separated post types (default: all public types)
- `--dry-run` — Preview without reindexing

#### Manage Custom Table Indexes

Create indexes:

``` bash
wp bsearch ecsi indexes create
```

Delete indexes:

``` bash
wp bsearch ecsi indexes delete --force
```

Recreate indexes:

``` bash
wp bsearch ecsi indexes recreate
```

Check status:

``` bash
wp bsearch ecsi indexes status
```

### Stopwords

Manage search stopwords. These are words excluded from search queries.

#### Add Stopwords

``` bash
wp bsearch stopwords add the and a an
```

Options:

- `--network` — Add stopwords for all sites in the network
- `--blog-id=<id>` — Specific blog ID (multisite only)

#### Remove Stopwords

``` bash
wp bsearch stopwords remove the and
```

Options:

- `--network` — Remove stopwords for all sites in the network
- `--blog-id=<id>` — Specific blog ID (multisite only)

#### List Stopwords

``` bash
wp bsearch stopwords list
```

Options:

- `--format=<format>` — Output format: table, json, csv
- `--network` — List stopwords for all sites in the network
- `--blog-id=<id>` — Specific blog ID (multisite only)

#### Clear Stopwords

``` bash
wp bsearch stopwords clear
```

Options:

- `--force` — Skip confirmation prompt
- `--network` — Clear stopwords for all sites in the network
- `--blog-id=<id>` — Specific blog ID (multisite only)

## Common Workflows

### Initial Setup

``` bash
# Create database tables
wp bsearch db create-tables

# Create FULLTEXT indexes
wp bsearch db indexes create

# Verify setup
wp bsearch status
```

### Multisite Setup

``` bash
# Check status for all sites
wp bsearch db status --network

# Clear cache across the network
wp bsearch cache clear --network --force
```

### Performance Optimization

``` bash
# Warm cache for top 100 searches
wp bsearch cache warm --limit=100

# Enable custom tables
wp bsearch settings set use_custom_tables true --type=bool

# Reindex posts into custom tables
wp bsearch ecsi reindex
```

### Maintenance

``` bash
# Clear old statistics
wp bsearch stats clear --before=2024-01-01

# Recreate FULLTEXT indexes
wp bsearch db indexes recreate

# Clear and warm cache
wp bsearch cache clear --force
wp bsearch cache warm
```

### Backup and Migration

``` bash
# Export settings
wp bsearch settings export --file=backup-settings.json

# Export statistics
wp bsearch stats export --file=backup-stats.csv

# Import on new site
wp bsearch settings import backup-settings.json
```

## Multisite Support

Most commands work across multisite networks. Commands that use multisite iteration (such as `cache clear`, `db status`, `ecsi status`, `stats view`, and `stopwords`) support these options:

- `--network` — Execute across all sites in the network
- `--blog-id=<id>` — Target specific site by ID
- `--url=<url>` — Target specific site by URL

Commands like `db create-tables`, `db recreate-tables`, `cache warm`, and `ecsi reindex` run against the current site only. Use WP-CLI’s global `--url` parameter to target a specific site.

### Multisite Examples

``` bash
# Clear cache network-wide
wp bsearch cache clear --network

# Check status for specific site
wp bsearch status --blog-id=2

# Reindex specific site
wp bsearch ecsi reindex --url=https://example.com
```

## Output Formats

Most commands support three output formats:

- `table` — Human-readable table (default)
- `json` — JSON format for scripting
- `csv` — CSV format for spreadsheets

### Format Examples

``` bash
# JSON output for scripting
wp bsearch stats top --format=json | jq '.[] | .["Search Term"]'

# CSV output for Excel
wp bsearch stats view --format=csv > statistics.csv
```

## Error Handling

Commands use standard WP-CLI exit codes:

- `0` — Success
- `1` — Error

### Common Errors

#### Plugin not active

``` bash
Error: Better Search plugin is not active.
```

Activate Better Search Pro first.

#### Database connection failed

``` bash
Error: Database connection failed.
```

Check your database credentials in `wp-config.php`.

#### Operation in progress

``` bash
Error: Operation 'reindex' already in progress. Use --force to override.
```

Wait for the current operation to finish or use `--force` to override the lock file.

## Best Practices

### Use Dry Run

I always preview destructive operations first:

``` bash
wp bsearch stats clear --dry-run
wp bsearch ecsi reindex --dry-run
```

### Batch Processing

On large sites, reduce batch sizes to avoid memory issues:

``` bash
wp bsearch ecsi reindex --batch-size=50
```

### Lock Files

Long-running operations create lock files in your temp directory to prevent concurrent execution. These clean up automatically on completion or error.

### Verbose Mode

Use `--verbose` when troubleshooting:

``` bash
wp bsearch cache warm --verbose
```

## Scripting Examples

### Daily Cache Warm

``` bash
#!/bin/bash
# warm-cache.sh
wp bsearch cache warm --limit=100 --force
```

### Weekly Statistics Export

``` bash
#!/bin/bash
# export-stats.sh
DATE=$(date +%Y-%m-%d)
wp bsearch stats export --file="stats-${DATE}.csv"
```

### Multisite Maintenance

``` bash
#!/bin/bash
# multisite-maintenance.sh
wp bsearch cache clear --network --force
wp bsearch stats clear --before=$(date -v-90d +%Y-%m-%d) --force
# Note: Use date -d '90 days ago' on GNU/Linux instead of -v-90d.
```

## Troubleshooting

### Check Plugin Status

``` bash
wp bsearch status --verbose
```

### Verify Database Tables

``` bash
wp bsearch db status
```

### Test Search Functionality

``` bash
wp bsearch search "test" --verbose
```

### Clear All Caches

``` bash
wp bsearch cache clear --force
wp cache flush
```
