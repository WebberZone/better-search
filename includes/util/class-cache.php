<?php
/**
 * Cache functions used by Better Search
 *
 * @since 3.3.0
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Util;

use WebberZone\Better_Search\Frontend\Language_Handler;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Cache Class.
 *
 * @since 3.3.0
 */
class Cache {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_ajax_bsearch_clear_cache', array( $this, 'ajax_clearcache' ) );
	}

	/**
	 * Function to clear the Better Search Cache with Ajax.
	 *
	 * @since 3.3.0
	 */
	public function ajax_clearcache() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		check_ajax_referer( 'bsearch-admin', 'security' );

		$count = self::delete_all();

		wp_send_json_success(
			array(
				/* translators: %s is the number of entries cleared */
				'message' => sprintf( _n( '%s entry cleared', '%s entries cleared', $count, 'better-search' ), number_format_i18n( $count ) ),
			)
		);
	}

	/**
	 * Delete the Better Search cache.
	 *
	 * @since 3.3.0
	 *
	 * @param array $transients Array of transients to delete.
	 * @param bool  $network    Use network (site) transient if true, default false.
	 * @return int Number of transients deleted.
	 */
	public static function delete( $transients = array(), $network = false ) {
		$loop = 0;

		$default_transients = self::get_keys( $network );

		if ( ! empty( $transients ) ) {
			$transients = array_intersect( $default_transients, (array) $transients );
		} else {
			$transients = $default_transients;
		}

		foreach ( $transients as $transient ) {
			$del = $network ? delete_site_transient( $transient ) : delete_transient( $transient );
			if ( $del ) {
				++$loop;
			}
		}
		return $loop;
	}

	/**
	 * Delete every Better Search cache entry, in both the blog and the network scope.
	 *
	 * Search results are cached as blog transients by the ordinary query path and as site
	 * transients by the custom-table path, so clearing only one scope leaves the other serving
	 * results built from superseded settings.
	 *
	 * @since 4.5.0
	 *
	 * @return int Number of transients deleted.
	 */
	public static function delete_all() {
		return self::delete() + self::delete( array(), true );
	}

	/**
	 * Get the default meta keys used for the cache
	 *
	 * @since 3.3.0
	 * @since 4.5.0 Added the $network parameter.
	 *
	 * @param bool $network Look up network (site) transients instead of blog transients.
	 * @return  array   Transient meta keys
	 */
	public static function get_keys( $network = false ) {

		global $wpdb;

		$keys   = array();
		$prefix = $network ? '_site_transient_bs_' : '_transient_bs_';

		if ( $network && is_multisite() ) {
			$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT meta_key AS option_name FROM {$wpdb->sitemeta} WHERE site_id = %d AND meta_key LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					get_current_network_id(),
					$wpdb->esc_like( $prefix ) . '%'
				)
			);
		} else {
			$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$wpdb->esc_like( $prefix ) . '%'
				)
			);
		}

		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				$keys[] = substr( $result->option_name, strlen( $network ? '_site_transient_' : '_transient_' ) );
			}
		}

		/**
		 * Filters the list of Better Search cache keys found in the options table.
		 *
		 * @since 4.0.0
		 * @since 4.5.0 Added the $network parameter.
		 *
		 * @param string[] $keys    Cache keys without the transient prefix.
		 * @param bool     $network Whether network (site) transients were looked up.
		 */
		return apply_filters( 'bsearch_cache_get_keys', $keys, $network );
	}

	/**
	 * Get the cache key based on a list of parameters.
	 *
	 * @since 3.3.0
	 *
	 * @param mixed  $attr    Array of attributes typically.
	 * @param string $context Context of the cache key to be set.
	 * @return string Cache meta key
	 */
	public static function get_key( $attr, $context = 'query' ): string {
		$args = (array) $attr;

		static $setting_types = null;
		if ( null === $setting_types ) {
			$setting_types = function_exists( 'bsearch_get_registered_settings_types' ) ? bsearch_get_registered_settings_types() : array();
		}

		// Remove args that don't affect query results.
		$exclude_keys = array(
			'echo',
			'extra_class',
			'heading',
			'is_block',
			'is_manual',
			'is_shortcode',
			'is_widget',
			'other_attributes',
		);

		foreach ( $exclude_keys as $key ) {
			unset( $args[ $key ] );
		}

		// Remove any keys ending in _header or _desc, or with type 'header'.
		foreach ( $args as $key => $value ) {
			if ( '_header' === substr( $key, -7 ) || '_desc' === substr( $key, -5 ) ) {
				unset( $args[ $key ] );
				continue;
			}

			if ( isset( $setting_types[ $key ] ) && 'header' === $setting_types[ $key ] ) {
				unset( $args[ $key ] );
			}
		}

		// Define categories of types for normalization.
		$id_array_types     = array( 'postids', 'numbercsv', 'taxonomies' );
		$string_array_types = array( 'posttypes', 'csv', 'multicheck' );
		$numeric_types      = array( 'number', 'checkbox', 'select', 'radio', 'radiodesc' );

		// Process arguments based on their registered types.
		foreach ( $args as $key => $value ) {
			$type = $setting_types[ $key ] ?? '';

			if ( in_array( $type, $numeric_types, true ) && is_numeric( $value ) ) {
				$args[ $key ] = (int) $value;
			} elseif ( in_array( $type, $id_array_types, true ) ) {
				$args[ $key ] = is_array( $value ) ? $value : wp_parse_id_list( $value );
				$args[ $key ] = array_unique( array_map( 'absint', $args[ $key ] ) );
				$args[ $key ] = array_filter( $args[ $key ] );
				sort( $args[ $key ] );
				if ( empty( $args[ $key ] ) ) {
					unset( $args[ $key ] );
				}
			} elseif ( in_array( $type, $string_array_types, true ) ) {
				if ( is_string( $value ) && strpos( $value, '=' ) !== false ) {
					parse_str( $value, $parsed );
					$value = array_keys( $parsed );
				} elseif ( is_string( $value ) ) {
					$value = explode( ',', $value );
				}
				$args[ $key ] = is_array( $value ) ? $value : array( $value );
				$args[ $key ] = array_unique( array_map( 'strval', $args[ $key ] ) );
				$args[ $key ] = array_filter( $args[ $key ] );
				sort( $args[ $key ] );
				if ( empty( $args[ $key ] ) ) {
					unset( $args[ $key ] );
				}
			}
		}

		// Sort top-level arguments.
		ksort( $args );

		// Remove any remaining empty strings or null values.
		foreach ( $args as $key => $value ) {
			if ( '' === $value || null === $value ) {
				unset( $args[ $key ] );
			}
		}

		// Generate cache key.
		return sprintf( 'bs_cache_%1$s_%2$s', md5( Language_Handler::get_cache_language() . '|' . wp_json_encode( $args ) ), $context );
	}

	/**
	 * Get a cached value by key.
	 *
	 * @since 4.2.0
	 *
	 * @param string $key    Cache key.
	 * @param bool   $network Use network (site) transient if true, default false.
	 * @return mixed Cached value or false if not found.
	 */
	public static function get( $key, $network = false ) {
		$value = $network ? get_site_transient( $key ) : get_transient( $key );
		return false === $value ? false : $value;
	}

	/**
	 * Set a cached value by key.
	 *
	 * @since 4.2.0
	 *
	 * @param string $key    Cache key.
	 * @param mixed  $value  Value to cache.
	 * @param int    $ttl    Time to live in seconds.
	 * @param bool   $network Use network (site) transient if true, default false.
	 * @return bool True on success, false on failure.
	 */
	public static function set( $key, $value, $ttl = 0, $network = false ) {
		if ( $network ) {
			return $ttl > 0 ? set_site_transient( $key, $value, $ttl ) : set_site_transient( $key, $value );
		} else {
			return $ttl > 0 ? set_transient( $key, $value, $ttl ) : set_transient( $key, $value );
		}
	}
}
