# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## Plugin Overview

Better Search Pro (v4.3.0) is the premium version of Better Search. It replaces the default WordPress search with a FULLTEXT-powered, relevance-ranked search engine, and adds pro-only features such as fuzzy search, custom index tables, multisite search, and more. It also tracks popular search queries and displays a search heatmap.

Namespace: `WebberZone\Better_Search`. Prefix: `bsearch`. Requires WordPress 6.6+, PHP 7.4+.

**This is the pro version.** Activating Better Search Pro auto-deactivates the free Better Search plugin (and vice versa). Both plugins share the same text domain (`better-search`) and settings key (`bsearch_settings`) so settings are preserved when switching between them.

Pro-only code lives exclusively in `includes/pro/`, declared as `@fs_premium_only /includes/pro/` in the plugin header. All files outside `includes/pro/` are identical to the free version.

Constants defined in `better-search.php`: `BETTER_SEARCH_VERSION` (4.3.0), `BETTER_SEARCH_PLUGIN_DIR`, `BETTER_SEARCH_PLUGIN_URL`, `BETTER_SEARCH_PLUGIN_FILE`, `BETTER_SEARCH_DB_VERSION` (2.0), `BETTER_SEARCH_DEFAULT_THUMBNAIL_URL`.

Settings are stored as a single `bsearch_settings` array in `wp_options`. Access via `bsearch_get_option($key)` / `bsearch_get_settings()`. The global `$bsearch_settings` is populated at plugin load.

## Commands

### PHP
```bash
composer phpcs          # Lint PHP (WordPress coding standards)
composer phpcbf         # Auto-fix PHP code style
composer phpstan        # Static analysis
composer phpcompat      # Check PHP 7.4–8.5 compatibility
composer test           # Run all checks (phpcs + phpcompat + phpstan)
composer zip            # Create distribution zip in build/
```

### JavaScript/CSS
```bash
npm run build           # Build free blocks (src: includes/frontend/blocks/src/, output: includes/frontend/blocks/build/)
npm start               # Watch free blocks
npm run build:assets    # Minify CSS/JS, generate RTL CSS via build-assets.js
npm run lint:js         # ESLint (covers both includes/pro/blocks/src/ and includes/frontend/blocks/src/)
npm run lint:css        # Stylelint (same scope)
npm run format          # Prettier format (same scope)
```

Note: There is no `build:pro` or `start:pro` npm script — there are no Gutenberg blocks in `includes/pro/`. The pro JS lives in `includes/pro/custom-tables/admin/js/` (plain JS, already built: `reindex.js` / `reindex.min.js`).

## Architecture

### Entry Point
`better-search.php` defines constants, loads Freemius (`load-freemius.php`), registers the custom PSR-4 autoloader (`includes/autoloader.php`), then calls `\WebberZone\Better_Search\load()` on `plugins_loaded`. It also requires several legacy-style global files directly (not autoloaded): `options-api.php`, `class-better-search-core-query.php`, `class-better-search-query.php`, `functions.php`, `general-template.php`, `heatmap.php`.

The `Pro\Pro` class is instantiated inside `Main::init()` only when `bsearch_freemius()->is__premium_only()` is true (Freemius handles the gating via the `@fs_premium_only` header directive).

### Mutual Exclusion
Both the free and pro plugins include a `bsearch_deactivate_other_instances()` function (in-file, not autoloaded) that auto-deactivates the other when either is activated. They share the same text domain (`better-search`) and settings key (`bsearch_settings`).

### Core Components
- **`includes/class-main.php`** — Singleton (`Main::get_instance()`). Instantiates all subsystems; admin initialized on `init` hook. Has a `?Pro\Pro $pro` property set when the pro license is active.
- **`includes/class-hook-loader.php`** — Registers plugin-wide hooks (`init`, `widgets_init`).
- **`includes/class-tracker.php`** — Tracks search queries via AJAX and `parse_request`; stores results in `bsearch` / `bsearch_daily` DB tables.
- **`includes/class-db.php`** — Static class managing the `bsearch` / `bsearch_daily` search-tracking tables and FULLTEXT indexes on `wp_posts`.

### Query Engine
- **`Better_Search_Core_Query`** (`includes/class-better-search-core-query.php`) — Extends `WP_Query`. Builds FULLTEXT SQL with configurable title/content weighting, boolean mode, seamless mode, and custom-table support. Lives outside the autoloaded namespace (global class, required directly).
- **`Better_Search_Query`** (`includes/class-better-search-query.php`) — Thin wrapper around `Better_Search_Core_Query` for template use.

### Frontend (`includes/frontend/`)
- **`Display`** — Renders search results HTML.
- **`Live_Search`** — AJAX live search (enqueues `better-search-live-search.js`).
- **`Template_Handler`** — Loads theme template overrides from `templates/` directory.
- **`Shortcodes`** — `[better_search]` shortcode.
- **`Styles_Handler`** / **`Language_Handler`** — CSS enqueue and i18n for JS.
- **`Media_Handler`** — Thumbnail resolution (same priority-chain strategy as other WebberZone plugins; subclass and override `get_option()` for custom options functions).
- **Widgets** — `Search_Box` and `Search_Heatmap` widgets in `includes/frontend/widgets/`.
- **Block Patterns** — `includes/frontend/block-patterns/` (search-form, search-results, query-loop patterns).

### Admin (`includes/admin/`)
- **`Settings`** — Settings page (`bsearch_options_page`); tabs for General, Search, Output, Heatmap, etc.
- **`Dashboard`** / **`Dashboard_Widgets`** — Search statistics dashboard page. Dashboard tabs support custom CSS classes, hide attributes, and are extensible via the `bsearch_admin_dashboard_tabs` filter.
- **`Statistics`** / **`Statistics_Table`** — Search query log table.
- **`Tools_Page`** — Utility actions (reindex, reset stats, etc.).
- **`Settings_Wizard`** — Guided setup wizard.
- **`Upgrader`** — Handles DB/settings migrations on version bump.
- **`Admin_Banner`** — Promotional banner notices.
- **`Admin_Notices`** / **`Admin_Notices_API`** — Dismissible admin notices system.

### Utilities (`includes/util/`)
- **`Cache`** — Caches query output per search term.
- **`Helpers`** — Shared helper functions.
- **`Hook_Registry`** — Static registry tracking all registered actions/filters.

### Heatmap
`includes/heatmap.php` contains procedural functions for rendering the search heatmap (list of popular queries). Also exposed as a widget and shortcode.

### Pro Components (`includes/pro/`) [PRO ONLY]

- **`Pro`** (`class-pro.php`) — Top-level pro orchestrator, instantiated as `Main::$pro`. Wires together all pro subsystems and registers additional hooks on `better_search_query_*` filters. Also handles minimum relevance threshold (`set_min_relevance`), LIKE fallback when FULLTEXT returns 0 results (`like_fallback_search`), slug search (`add_custom_clauses`), front/posts page exclusion (`exclude_special_pages` via `bsearch_exclude_post_ids` filter), and REST API search integration.

- **`Query_Modifier`** (`class-query-modifier.php`) — Extends the core query via filter hooks (`better_search_query_posts_fields`, `_join`, `_where_match`, `_groupby`, `_orderby_clauses`). Adds: custom table JOIN, cornerstone posts pinning (`the_posts` filter), max execution time hint, and additional `ORDER BY` clause control.

- **`Fuzzy_Search`** (`class-fuzzy-search.php`) — Adds a fuzzy/phonetic scoring layer on top of FULLTEXT results via `bsearch_posts_match_field` and `better_search_query_posts_clauses` filters. Requires a dedicated FULLTEXT index; shows an admin notice if the index is missing.

- **`Multisite_Search`** (`class-multisite-search.php`) — Cross-site search across multiple blogs in a WordPress Multisite network. Uses `Custom_Tables\Posts_Search` to query across sites.

- **`Custom_Tables\Custom_Tables`** (`custom-tables/class-custom-tables.php`) — Manages a dedicated search index table separate from `wp_posts`, enabling faster queries on large sites. Composed of:
  - `Table_Manager` — Creates/manages the custom DB table schema (defaults to InnoDB). Includes `get_table_engine()` and `convert_to_innodb()` methods for engine management, with automatic FULLTEXT index recreation after conversion.
  - `Sync_Manager` — Keeps the custom table in sync with `wp_posts` (on save/delete hooks). Includes a scheduled reconciliation cron job (twicedaily) that auto-syncs any published posts missing from the index.
  - `Custom_Tables_Admin` — Admin UI with reindex action, InnoDB conversion tool (shows current engine status with conversion form), and enqueues `reindex.js` for AJAX reindexing progress.
  - `Posts_Search` — Executes search queries against the custom table.

- **`Admin`** (`class-admin.php`) — Pro-specific admin additions (extra settings sections, tools). Includes dashboard chart drill-down feature: click a bar in the daily searches chart to view top 20 popular searches for that day (`bsearch_get_day_searches` AJAX action). Enqueues `includes/pro/js/chart-interactions.js` for Chart.js click/hover/tooltip handling.

- **`Network`** (`network/`) — Multisite network admin pages. `Dashboard` extends the base `Admin\Dashboard` and registers a network-admin dashboard page. `Statistics` reuses `Statistics_Table` to display cross-network search stats under the network admin menu.

- **`CLI`** (`cli/`) — WP-CLI integration. `CLI_Manager` registers all subcommands under the `bsearch` top-level command. Subcommands: `search`, `cache`, `db`, `stats`, `settings`, `tables`, `status`, `stopwords`. All extend `Base_Command`.

### Pro Settings
Pro settings are added to the existing `bsearch_settings` option. The `Pro\Admin` class registers additional fields into the shared settings page tabs. No separate options key.

## Key Patterns

- **Pro directory** — All pro-exclusive code lives in `includes/pro/`. The `Main` class has a `?Pro\Pro $pro` property that is set only when the pro license is active (Freemius gating).
- **Legacy globals** — Several core files are `require_once`'d rather than autoloaded; the global `$bsearch_settings` is set at load time and accessible everywhere, but prefer `bsearch_get_option()` over direct access.
- **FULLTEXT indexes** — `Db::create_fulltext_indexes()` is called on activation to add FULLTEXT indexes to `wp_posts(post_title, post_content)`. Both InnoDB and MyISAM variants are handled.
- **Seamless mode** — When enabled, Better Search intercepts the native WordPress search query (via `pre_get_posts`) rather than requiring a separate template. Controlled by the `seamless` setting.

## Free vs Pro Feature Comparison

| Feature | Free | Pro |
|---|---|---|
| FULLTEXT search | Yes | Yes |
| Fuzzy/phonetic matching | No | Yes (`Fuzzy_Search`) |
| Minimum relevance threshold | No | Yes (`Pro::set_min_relevance`) |
| LIKE fallback when FULLTEXT returns 0 results | No | Yes (`Pro::like_fallback_search`) |
| Search post slug | No | Yes (`Pro::add_custom_clauses`) |
| Cornerstone/pinned posts | No | Yes (`Query_Modifier`) |
| Custom DB index table | No | Yes (`Custom_Tables`) |
| Multisite cross-site search | No | Yes (`Multisite_Search`) |
| REST API search integration | No | Yes (`Pro` REST hooks) |
| Max SQL execution time hint | No | Yes (`Query_Modifier`) |
| Exclude front/posts page | No | Yes (`Pro::exclude_special_pages`) |
| Dashboard chart drill-down | No | Yes (`Pro\Admin` AJAX) |
| InnoDB conversion tool | No | Yes (`Table_Manager`) |
| Scheduled index reconciliation | No | Yes (`Sync_Manager` cron) |
| WP-CLI commands | No | Yes (`CLI_Manager`, 8 subcommands) |
| Network admin dashboard & stats | No | Yes (`Network\Dashboard`, `Network\Statistics`) |
