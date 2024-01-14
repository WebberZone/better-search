<?php
/**
 * Functions to sanitize settings.
 *
 * @link  https://webberzone.com
 * @since 3.3.0
 *
 * @package WebberZone\Better_Search
 */

namespace WebberZone\Better_Search\Admin\Settings;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings Sanitize Class.
 */
class Settings_Sanitize {

	/**
	 * Main constructor class.
	 */
	public function __construct() {
	}

	/**
	 * Miscellaneous sanitize function
	 *
	 * @param mixed $value Setting Value.
	 * @return string Sanitized value.
	 */
	public function sanitize_missing( $value ) {
		return $value;
	}

	/**
	 * Sanitize text fields
	 *
	 * @param string $value The field value.
	 * @return string Sanitizied value
	 */
	public function sanitize_text_field( $value ) {
		return $this->sanitize_textarea_field( $value );
	}

	/**
	 * Sanitize number fields
	 *
	 * @param  string $value The field value.
	 * @return string Sanitized value
	 */
	public function sanitize_number_field( $value ) {
		return filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
	}

	/**
	 * Sanitize CSV fields
	 *
	 * @param string $value The field value.
	 * @return string Sanitizied value
	 */
	public function sanitize_csv_field( $value ) {
		return implode( ',', array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) );
	}

	/**
	 * Sanitize CSV fields which hold numbers
	 *
	 * @param string $value The field value.
	 * @return string Sanitized value
	 */
	public function sanitize_numbercsv_field( $value ) {
		return implode( ',', array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) ) );
	}

	/**
	 * Sanitize CSV fields which hold post IDs
	 *
	 * @param string $value The field value.
	 * @return string Sanitized value
	 */
	public function sanitize_postids_field( $value ) {
		$ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) );

		foreach ( $ids as $key => $value ) {
			if ( false === get_post_status( $value ) ) {
				unset( $ids[ $key ] );
			}
		}

		return implode( ',', $ids );
	}

	/**
	 * Sanitize textarea fields
	 *
	 * @param string $value The field value.
	 * @return string Sanitized value
	 */
	public function sanitize_textarea_field( $value ) {

		global $allowedposttags;

		// We need more tags to allow for script and style.
		$moretags = array(
			'script' => array(
				'type'    => true,
				'src'     => true,
				'async'   => true,
				'defer'   => true,
				'charset' => true,
			),
			'style'  => array(
				'type'   => true,
				'media'  => true,
				'scoped' => true,
			),
			'link'   => array(
				'rel'      => true,
				'type'     => true,
				'href'     => true,
				'media'    => true,
				'sizes'    => true,
				'hreflang' => true,
			),
		);

		$allowedtags = array_merge( $allowedposttags, $moretags );

		/**
		 * Filter allowed tags allowed when sanitizing text and textarea fields.
		 *
		 * @param array $allowedtags Allowed tags array.
		 */
		$allowedtags = apply_filters( 'wz_sanitize_allowed_tags', $allowedtags );

		return wp_kses( wp_unslash( $value ), $allowedtags );
	}

	/**
	 * Sanitize checkbox fields
	 *
	 * @param mixed $value The field value.
	 * @return int  Sanitized value
	 */
	public function sanitize_checkbox_field( $value ) {
		$value = ( -1 === (int) $value ) ? 0 : 1;

		return $value;
	}

	/**
	 * Sanitize post_types fields
	 *
	 * @param  array $value The field value.
	 * @return string  $value  Sanitized value
	 */
	public function sanitize_posttypes_field( $value ) {
		$post_types = array_map( 'sanitize_text_field', (array) wp_unslash( $value ) );

		return implode( ',', $post_types );
	}

	/**
	 * Sanitize post_types fields
	 *
	 * @param  array $value The field value.
	 * @return string  $value  Sanitized value
	 */
	public function sanitize_taxonomies_field( $value ) {
		$taxonomies = array_map( 'sanitize_text_field', (array) wp_unslash( $value ) );

		return implode( ',', $taxonomies );
	}

	/**
	 * Sanitize color fields.
	 *
	 * @param  string $value The field value.
	 * @return string Sanitized value
	 */
	public function sanitize_color_field( $value ) {
		return sanitize_hex_color( $value );
	}
}
