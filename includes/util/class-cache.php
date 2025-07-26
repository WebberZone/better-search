<?php
/**
 * Cache functions used by Better Search
 *
 * @since 3.3.0
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Util;

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

		$count = $this->delete();

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

		$default_transients = self::get_keys();

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
	 * Get the default meta keys used for the cache
	 *
	 * @since 3.3.0
	 *
	 * @return  array   Transient meta keys
	 */
	public static function get_keys() {

		global $wpdb;

		$keys = array();

		$sql = "
			SELECT option_name
			FROM {$wpdb->options}
			WHERE `option_name` LIKE '_transient_bs_%'
		";

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				$keys[] = str_replace( '_transient_', '', $result->option_name );
			}
		}

		return apply_filters( 'bsearch_cache_get_keys', $keys );
	}

	/**
	 * Get the meta key based on a list of parameters.
	 *
	 * @since 3.3.0
	 *
	 * @param mixed  $attr    Array of attributes typically.
	 * @param string $context Context of the cache key to be set.
	 * @return string Cache meta key
	 */
	public static function get_key( $attr, $context = 'query' ) {

		$key = sprintf( 'bs_cache_%1$s_%2$s', md5( wp_json_encode( $attr ) ), $context );

		return $key;
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
