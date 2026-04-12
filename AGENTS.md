# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## Plugin Overview

Better Search (v4.3.0) is a free WordPress plugin that replaces the default WordPress search with a FULLTEXT-powered, relevance-ranked search engine. It tracks popular search queries and displays a search heatmap.

Namespace: `WebberZone\Better_Search`. Prefix: `bsearch`. Requires WordPress 6.6+, PHP 7.4+.

**This is the free version.** The companion premium plugin (`better-search-pro`) is a separate repository. Activating one auto-deactivates the other. Both share the same text domain (`better-search`) and settings key (`bsearch_settings`), so settings are preserved when switching.

There is no `includes/pro/` directory in this repository.

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
npm run lint:js         # ESLint (covers includes/frontend/blocks/src/)
npm run lint:css        # Stylelint (same scope)
npm run format          # Prettier format (same scope)
```

## Architecture

### Entry Point
`better-search.php` defines constants, loads Freemius (`load-freemius.php`), registers the custom PSR-4 autoloader (`includes/autoloader.php`), then calls `\WebberZone\Better_Search\load()` on `plugins_loaded`. It also requires several legacy-style global files directly (not autoloaded): `options-api.php`, `class-better-search-core-query.php`, `class-better-search-query.php`, `functions.php`, `general-template.php`, `heatmap.php`.

### Mutual Exclusion
Both the free and pro plugins include a `bsearch_deactivate_other_instances()` function (in-file, not autoloaded) that auto-deactivates the other when either is activated. They share the same text domain (`better-search`) and settings key (`bsearch_settings`).

### Core Components
- **`includes/class-main.php`** — Singleton (`Main::get_instance()`). Instantiates all subsystems; admin initialized on `init` hook. In the free plugin, the `$pro` property is always `null`.
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

## Key Patterns

- **Legacy globals** — Several core files are `require_once`'d rather than autoloaded; the global `$bsearch_settings` is set at load time and accessible everywhere, but prefer `bsearch_get_option()` over direct access.
- **FULLTEXT indexes** — `Db::create_fulltext_indexes()` is called on activation to add FULLTEXT indexes to `wp_posts(post_title, post_content)`. Both InnoDB and MyISAM variants are handled.
- **Seamless mode** — When enabled, Better Search intercepts the native WordPress search query (via `pre_get_posts`) rather than requiring a separate template. Controlled by the `seamless` setting.
- **No pro directory** — This is the free version. There is no `includes/pro/` directory and no pro feature gating. The pro version is a separate plugin (`better-search-pro`); only one can be active at a time.
