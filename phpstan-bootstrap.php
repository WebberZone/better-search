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

// TranslatePress has no official PHPStan stub package, so declare the minimal surface the
// Better Search language handler touches.
namespace {
	if ( ! class_exists( 'TRP_Translate_Press' ) ) {
		class TRP_Translate_Press {
			/**
			 * Runtime surface varies by TranslatePress version, so callers guard it.
			 *
			 * @return mixed
			 */
			public static function get_trp_instance() {
				return new self();
			}

			/**
			 * @param string $component Component name.
			 * @return object|null
			 */
			public function get_component( $component ) {
				unset( $component );
				return null;
			}
		}
	}

	if ( ! function_exists( 'trp_translate' ) ) {
		/**
		 * TranslatePress translation stub for static analysis.
		 *
		 * @param string      $content                  Content to translate.
		 * @param string|null $language                 Target language code.
		 * @param bool        $prevent_over_translation Whether to wrap the output.
		 * @return string
		 */
		function trp_translate( $content, $language = null, $prevent_over_translation = true ) {
			unset( $language, $prevent_over_translation );
			return (string) $content;
		}
	}
}
