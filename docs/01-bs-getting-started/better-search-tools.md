---
slug: better-search-tools
title: "Better Search Tools"
products: [better-search]
sections: ["01-bs-getting-started"]
tags: [better-search, tools]
status: publish
order: 0
toc: true
---

[toc]

The [Better Search](https://webberzone.com/plugins/better-search/) Tools page provides database diagnostics, cache controls, and maintenance utilities. Open **Better Search → Tools** to view it.

## Status

The Status section reports the installed and current database versions, whether the popular-search tables exist, their estimated row counts and sizes, and whether the required FULLTEXT indexes are installed. It does not change the database.

Better Search Pro also shows the custom search index and AI question-log tables when those modules are available. Use this information when troubleshooting search, indexing, or recorded-question problems.

## Clear cache

Click **Clear cache** to remove cached Better Search results. Saving the settings page also clears this cache.

## Recreate FULLTEXT index

Use **Recreate Index** when the Status section reports missing FULLTEXT indexes or search relevance appears incorrect. Large sites may take time to rebuild the indexes. The page also displays SQL statements you can run through a database administration tool if the button fails.

Back up the database before running schema changes manually.

## Create and recreate tables

**Create tables** creates the overall and daily popular-search tables when they are missing.

**Recreate Tables** rebuilds either table. Better Search renames the existing table as a backup before creating the replacement. Recreating a table is a database maintenance operation; back up the database first.

## Reset database

The reset controls delete the recorded popular-search data from the selected table while leaving the table structure intact. Use them when you want to restart overall or daily search statistics.

## Backup tables

When backup tables exist, you can restore the overall or daily popular-search table. After verifying the restored data, you can delete the backup tables from the same section.

## Export and import settings

Export downloads the current Better Search settings as JSON. Import replaces the current settings with values from a previously exported JSON file, which is useful when moving configuration between sites.
