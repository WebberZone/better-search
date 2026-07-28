<?php
/**
 * Feature Manager.
 *
 * Central gate that decides which optional plugin features are loaded.
 *
 * @package WebberZone\Better_Search
 */

namespace WebberZone\Better_Search;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Feature Manager class.
 *
 * Reads the raw settings option to determine whether an optional feature
 * should be instantiated. All features default to enabled - a missing
 * settings key means the feature is on, so upgrades see no change in
 * behaviour.
 *
 * This class intentionally does not use bsearch_get_option(): that function
 * falls back to the registered settings defaults, which would load the
 * admin Settings class on every request. It is safe to call at
 * plugins_loaded.
 *
 * @since 4.4.0
 */
class Feature_Manager {

	/**
	 * Cached copy of the settings option.
	 *
	 * @since 4.4.0
	 *
	 * @var array|null
	 */
	private static $settings = null;

	/**
	 * Get the map of toggleable features.
	 *
	 * @since 4.4.0
	 *
	 * @return array Associative array of feature ID => array with 'setting' and 'default' keys.
	 */
	public static function get_features(): array {
		$features = array(
			'widgets'          => array(
				'setting' => 'enable_widgets',
				'default' => true,
			),
			'shortcodes'       => array(
				'setting' => 'enable_shortcodes',
				'default' => true,
			),
			'block_patterns'   => array(
				'setting' => 'enable_block_patterns',
				'default' => true,
			),
			'live_search'      => array(
				'setting' => 'enable_live_search',
				'default' => false,
			),
			'fuzzy_search'     => array(
				'setting' => 'enable_fuzzy_search',
				'default' => true,
			),
			'did_you_mean'     => array(
				'setting' => 'enable_did_you_mean',
				'default' => true,
			),
			'search_redirects' => array(
				'setting' => 'enable_search_redirects',
				'default' => true,
			),
			'custom_tables'    => array(
				'setting' => 'enable_custom_tables',
				'default' => true,
			),
			'multisite_search' => array(
				'setting' => 'enable_multisite_search',
				'default' => true,
			),
			'cli'              => array(
				'setting' => 'enable_cli',
				'default' => true,
			),
			'network_admin'    => array(
				'setting' => 'enable_network_admin',
				'default' => true,
			),
			'chart_drilldown'  => array(
				'setting' => 'enable_chart_drilldown',
				'default' => true,
			),
		);

		/**
		 * Filter the map of toggleable features.
		 *
		 * @since 4.4.0
		 *
		 * @param array $features Associative array of feature ID => array with 'setting' and 'default' keys.
		 */
		return apply_filters( 'bsearch_features', $features );
	}

	/**
	 * Whether a feature is enabled.
	 *
	 * A feature is enabled when its setting is missing (default) or truthy.
	 * Unknown feature IDs are treated as enabled.
	 *
	 * @since 4.4.0
	 *
	 * @param string $feature Feature ID.
	 * @return bool Whether the feature is enabled.
	 */
	public static function is_enabled( string $feature ): bool {
		$features = self::get_features();

		if ( isset( $features[ $feature ] ) ) {
			$setting = $features[ $feature ]['setting'];
			$default = ! empty( $features[ $feature ]['default'] );

			if ( null === self::$settings ) {
				$settings       = get_option( 'bsearch_settings' );
				self::$settings = is_array( $settings ) ? $settings : array();
			}

			$enabled = isset( self::$settings[ $setting ] ) ? ! empty( self::$settings[ $setting ] ) : $default;
		} else {
			$enabled = true;
		}

		/**
		 * Filter whether a feature is enabled.
		 *
		 * @since 4.4.0
		 *
		 * @param bool   $enabled Whether the feature is enabled.
		 * @param string $feature Feature ID.
		 */
		return (bool) apply_filters( 'bsearch_feature_enabled', $enabled, $feature );
	}
}
