# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## Plugin Overview

Better Search (v4.3.2) replaces the default WordPress search with a FULLTEXT-powered, relevance-ranked search engine. It also tracks popular search queries and displays a search heatmap. This is the **free** version; a premium version (Better Search Pro) exists as a separate plugin.

Namespace: `WebberZone\Better_Search`. Prefix: `bsearch`. Requires WordPress 6.6+, PHP 7.4+.

webberzone.com: <https://webberzone.com/plugins/better-search/>

Activating Better Search auto-deactivates Better Search Pro (and vice versa). Both plugins share the same text domain (`better-search`) and settings key (`bsearch_settings`) so settings are preserved when switching between them.

Constants defined in `better-search.php`: `BETTER_SEARCH_VERSION` (4.3.2), `BETTER_SEARCH_PLUGIN_DIR`, `BETTER_SEARCH_PLUGIN_URL`, `BETTER_SEARCH_PLUGIN_FILE`, `BETTER_SEARCH_DB_VERSION` (2.0), `BETTER_SEARCH_DEFAULT_THUMBNAIL_URL`.

Settings are stored as a single `bsearch_settings` array in `wp_options`. Access via `bsearch_get_option($key)` / `bsearch_get_settings()`. The global `$bsearch_settings` is populated at plugin load.

## Commands

### PHP

```bash
composer phpcs          # Lint PHP (WordPress coding standards)
composer phpcbf         # Auto-fix PHP code style
composer phpstan        # Static analysis
composer phpcompat      # Check PHP 7.4–8.5 compatibility
composer test           # Run all checks (phpcs + phpcompat + phpstan)
composer zip            # Create distribution zip via build-zip.sh
```

### JavaScript/CSS

```bash
npm run build           # Build blocks (src: includes/frontend/blocks/src/, output: includes/frontend/blocks/build/)
npm start               # Watch blocks
npm run build:assets    # Minify CSS/JS, generate RTL CSS via build-assets.js
npm run lint:js         # ESLint (covers includes/pro/blocks/src/ and includes/frontend/blocks/src/)
npm run lint:css        # Stylelint (same scope)
npm run format          # Prettier format (same scope)
```

Note: The `lint:js`, `lint:css`, and `format` scripts reference `includes/pro/blocks/src/`, which does not exist in this free repository. The `includes/frontend/blocks/build/` directory is currently empty.

## Architecture

### Entry Point

`better-search.php` defines constants, loads Freemius (`load-freemius.php`), registers the custom PSR-4 autoloader (`includes/autoloader.php`), then calls `\WebberZone\Better_Search\load()` on `plugins_loaded`. It also requires several legacy-style global files directly (not autoloaded): `options-api.php`, `class-better-search-core-query.php`, `class-better-search-query.php`, `functions.php`, `general-template.php`, `heatmap.php`.

Freemius is initialised with `is_premium => false` (free version). The `bsearch_freemius()` function returns the Freemius SDK instance, used for opt-in/upgrade prompts. The `Main` class declares a `?Pro\Pro $pro` property (defaulting to `null`) which is never set in the free version — pro code lives in the separate Better Search Pro plugin.

### Mutual Exclusion

Both the free and pro plugins include a `bsearch_deactivate_other_instances()` function (in-file, not autoloaded) that auto-deactivates the other when either is activated. They share the same text domain (`better-search`) and settings key (`bsearch_settings`).

### Autoloader

`includes/autoloader.php` registers a custom PSR-4-style autoloader for the `WebberZone\Better_Search` namespace. Class names are lowercased, underscores converted to dashes, and the last segment is prefixed with `class-`. Example: `WebberZone\Better_Search\Frontend\Display` → `includes/frontend/class-display.php`.

### Core Components

- **`includes/class-main.php`** — Singleton (`Main::get_instance()`). Instantiates all subsystems; admin initialized on `init` hook. Declares a `?Pro\Pro $pro` property (always `null` in the free version).
- **`includes/class-hook-loader.php`** — Registers plugin-wide hooks (`init`, `widgets_init`); registers widgets and image sizes.
- **`includes/class-tracker.php`** — Tracks search queries via AJAX (`wp_ajax_bsearch_tracker`) and `parse_request`; stores results in `bsearch` / `bsearch_daily` DB tables.
- **`includes/class-db.php`** — Static class managing the `bsearch` / `bsearch_daily` search-tracking tables and FULLTEXT indexes on `wp_posts`.

### Query Engine

- **`Better_Search_Core_Query`** (`includes/class-better-search-core-query.php`) — Extends `WP_Query`. Builds FULLTEXT SQL with configurable title/content weighting, boolean mode, and seamless mode. Lives outside the autoloaded namespace (global class, required directly).
- **`Better_Search_Query`** (`includes/class-better-search-query.php`) — Extends `WP_Query`; wraps `Better_Search_Core_Query` for template use. Registers all core-query filters in the constructor, runs the query, then removes the filters.

### Frontend (`includes/frontend/`)

- **`Display`** — Renders search results HTML and highlights search terms on results pages and followed links (referer check is scheme-agnostic). `extract_highlight_terms()` keeps double-quoted phrases intact and skips `-excluded` terms; actual highlighting is done by `Helpers::highlight()`. Also enqueues a client-side highlight script for cached-page scenarios.
- **`Live_Search`** — AJAX live search (enqueues `better-search-live-search.js`).
- **`Template_Handler`** — Loads theme template overrides from `templates/` directory.
- **`Shortcodes`** — `[bsearch_heatmap]` and `[bsearch_form]` shortcodes.
- **`Styles_Handler`** / **`Language_Handler`** — CSS enqueue and i18n for JS.
- **`Media_Handler`** — Thumbnail resolution (same priority-chain strategy as other WebberZone plugins; subclass and override `get_option()` for custom options functions).
- **Widgets** — `Search_Box` and `Search_Heatmap` widgets in `includes/frontend/widgets/`.
- **Block Patterns** — `includes/frontend/block-patterns/` (search-form, search-results, query-loop patterns).

### Admin (`includes/admin/`)

- **`Settings`** — Settings page (`bsearch_options_page`); tabs: General, Performance, Search, Output, Heatmap.
- **`Dashboard`** / **`Dashboard_Widgets`** — Search statistics dashboard page (`bsearch_dashboard` top-level menu). Dashboard tabs support custom CSS classes, hide attributes, and are extensible via the `bsearch_admin_dashboard_tabs` filter.
- **`Statistics`** / **`Statistics_Table`** — Search query log table (`bsearch_popular_searches`).
- **`Tools_Page`** — Utility actions (`bsearch_tools_page`): reindex, reset stats, etc.
- **`Settings_Wizard`** — Guided setup wizard.
- **`Upgrader`** — Handles DB/settings migrations on version bump.
- **`Admin_Banner`** — Promotional banner notices (pro upgrade / donate).
- **`Admin_Notices`** / **`Admin_Notices_API`** — Dismissible admin notices system.
- **`Activator`** — Activation hook handler; creates DB tables and FULLTEXT indexes.

### Network Admin (`includes/admin/network/`)

- **`Admin`** — Multisite network admin page (`bsearch_dashboard` under network admin). Includes a settings-copy feature to propagate `bsearch_settings` from a source blog to target blogs. Renders a pro-upgrade banner when Pro is not active.
- **`Tools_Page`** — Network-level tools page.

### Utilities (`includes/util/`)

- **`Cache`** — Caches query output per search term; AJAX clear-cache handler.
- **`Helpers`** — Shared helper functions (table access, highlighting, etc.).
- **`Hook_Registry`** — Static registry tracking all registered actions/filters.

### Heatmap

`includes/heatmap.php` contains procedural functions for rendering the search heatmap (list of popular queries). Also exposed as a widget and shortcode.

## Key Patterns

- **Legacy globals** — Several core files are `require_once`'d rather than autoloaded; the global `$bsearch_settings` is set at load time and accessible everywhere, but prefer `bsearch_get_option()` over direct access.
- **FULLTEXT indexes** — `Db::create_fulltext_indexes()` is called on activation (via `Activator::activation_hook()`) to add FULLTEXT indexes to `wp_posts(post_title, post_content)`. Both InnoDB and MyISAM variants are handled.
- **Seamless mode** — When enabled, Better Search intercepts the native WordPress search query (via `pre_get_posts`) rather than requiring a separate template. Controlled by the `seamless` setting.
- **Freemius (free)** — The free version uses Freemius with `is_premium => false` for opt-in telemetry and upgrade prompts. `bsearch_freemius()->is_paying()` gates the pro-upgrade banner display.
- **Pro-ready property** — `Main::$pro` is declared as `?Pro\Pro` (nullable) but always `null` in the free version; the pro plugin sets it when active.
