<?php
/**
 * Loads Freemius SDK.
 *
 * @package WebberZone\Better_Search
 */

namespace WebberZone\Better_Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Initialize Freemius SDK.
 */
function bsearch_freemius() {
	global $bsearch_freemius;
	if ( ! isset( $bsearch_freemius ) ) {
		if ( ! defined( 'WP_FS__PRODUCT_17020_MULTISITE' ) ) {
			define( 'WP_FS__PRODUCT_17020_MULTISITE', true );
		}
		// Include Freemius SDK.
		require_once __DIR__ . '/vendor/freemius/start.php';
		$bsearch_freemius = \fs_dynamic_init(
			array(
				'id'             => '17020',
				'slug'           => 'better-search',
				'premium_slug'   => 'better-search-pro',
				'type'           => 'plugin',
				'public_key'     => 'pk_40525301bca835d9836ec4d946693',
				'is_premium'     => false,
				'premium_suffix' => 'Pro',
				'has_addons'     => false,
				'has_paid_plans' => true,
				'menu'           => array(
					'slug'    => 'bsearch_dashboard',
					'contact' => false,
					'support' => false,
					'network' => true,
				),
				'is_live'        => true,
			)
		);
	}
	$bsearch_freemius->add_filter( 'plugin_icon', __NAMESPACE__ . '\\bsearch_freemius_get_plugin_icon' );
	$bsearch_freemius->add_filter( 'after_uninstall', __NAMESPACE__ . '\\bsearch_freemius_uninstall' );
	return $bsearch_freemius;
}

/**
 * Get the plugin icon.
 *
 * @return string
 */
function bsearch_freemius_get_plugin_icon() {
	return __DIR__ . '/includes/admin/images/bsearch-icon.png';
}

/**
 * Uninstall the plugin.
 */
function bsearch_freemius_uninstall() {
	require_once __DIR__ . '/uninstaller.php';
}

// Init Freemius.
bsearch_freemius();
// Signal that SDK was initiated.
do_action( 'bsearch_freemius_loaded' );
