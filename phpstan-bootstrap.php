<?php
/**
 * PHPStan bootstrap file for Better Search.
 *
 * @package WebberZone\Better_Search
 */

namespace {
	if ( ! defined( 'BETTER_SEARCH_VERSION' ) ) {
		define( 'BETTER_SEARCH_VERSION', '0.0.0' );
	}

	if ( ! defined( 'BETTER_SEARCH_PLUGIN_FILE' ) ) {
		define( 'BETTER_SEARCH_PLUGIN_FILE', '' );
	}

	if ( ! defined( 'BETTER_SEARCH_PLUGIN_DIR' ) ) {
		define( 'BETTER_SEARCH_PLUGIN_DIR', '' );
	}

	if ( ! defined( 'BETTER_SEARCH_PLUGIN_URL' ) ) {
		define( 'BETTER_SEARCH_PLUGIN_URL', '' );
	}

	if ( ! defined( 'BETTER_SEARCH_MAX_WORDS' ) ) {
		define( 'BETTER_SEARCH_MAX_WORDS', 100 );
	}

	if ( ! defined( 'DB_NAME' ) ) {
		define( 'DB_NAME', '' );
	}

	if ( ! class_exists( 'PLL_Frontend_Filters' ) ) {
		class PLL_Frontend_Filters {} // phpcs:ignore
	}
}

// When running on the free plugin (includes/pro/ removed by sync), define Pro class stubs
// so PHPStan can resolve the ?Pro\Pro $pro property and any shared code that accesses
// pro properties (e.g. ->pro->custom_tables, ->pro->network_dashboard).
namespace WebberZone\Better_Search\Pro\Custom_Tables {
	if ( ! is_dir( __DIR__ . '/includes/pro' ) ) {
		class Table_Manager { // phpcs:ignore
			public static string $db_version_option = ''; // phpcs:ignore
			/** @return int|float */
			public function get_indexing_percentage( int $blog_id = 0 ) { return 0; } // phpcs:ignore
			public function get_content_count( int $blog_id = 0 ): int { return 0; } // phpcs:ignore
			public function get_post_count( int $blog_id = 0 ): int { return 0; } // phpcs:ignore
			public function drop_tables(): void {} // phpcs:ignore
		}
		class Custom_Tables_Admin { // phpcs:ignore
			public \WebberZone\Better_Search\Pro\Custom_Tables\Table_Manager $table_manager; // phpcs:ignore
			/** @return array<mixed>|false */
			public function get_reindex_state() { return false; } // phpcs:ignore
		}
		class Custom_Tables { // phpcs:ignore
			public \WebberZone\Better_Search\Pro\Custom_Tables\Custom_Tables_Admin $admin; // phpcs:ignore
		}
	}
}

namespace WebberZone\Better_Search\Pro\Network {
	if ( ! is_dir( __DIR__ . '/includes/pro' ) ) {
		class Dashboard { // phpcs:ignore
			public function render_page(): void {} // phpcs:ignore
		}
	}
}

namespace WebberZone\Better_Search\Pro {
	if ( ! is_dir( __DIR__ . '/includes/pro' ) ) {
		class Pro { // phpcs:ignore
			public ?\WebberZone\Better_Search\Pro\Custom_Tables\Custom_Tables $custom_tables = null; // phpcs:ignore
			public ?\WebberZone\Better_Search\Pro\Network\Dashboard $network_dashboard = null; // phpcs:ignore
		}
	}
}
