<?php
/**
 * Better Search Options API.
 *
 * @since 3.3.0
 *
 * @package WebberZone\Better_Search
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get Settings.
 *
 * Retrieves all plugin settings
 *
 * @since  2.2.0
 *
 * @return array Better Search settings
 */
function bsearch_get_settings() {

	$settings = get_option( 'bsearch_settings', array() );

	/**
	 * Settings array
	 *
	 * Retrieves all plugin settings
	 *
	 * @since 1.2.0
	 * @param array $settings Settings array
	 */
	return apply_filters( 'bsearch_get_settings', $settings );
}


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 2.2.0
 *
 * @param string $key           Key of the option to fetch.
 * @param mixed  $default_value Default value to fetch if option is missing.
 * @return mixed
 */
function bsearch_get_option( $key = '', $default_value = null ) {

	$bsearch_settings = bsearch_get_settings();

	if ( null === $default_value ) {
		$default_value = bsearch_get_default_option( $key );
	}

	$value = $bsearch_settings[ $key ] ?? $default_value;

	/**
	 * Filter the value for the option being fetched.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed   $value   Value of the option
	 * @param mixed   $key     Name of the option
	 * @param mixed   $default_value Default value
	 */
	$value = apply_filters( 'bsearch_get_option', $value, $key, $default_value );

	/**
	 * Key specific filter for the value of the option being fetched.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed   $value   Value of the option
	 * @param mixed   $key     Name of the option
	 * @param mixed   $default_value Default value
	 */
	return apply_filters( 'bsearch_get_option_' . $key, $value, $key, $default_value );
}

/**
 * Get an option from a specific blog in a multisite network.
 *
 * @since 4.2.0
 *
 * @param int    $blog_id       Blog ID to fetch the option from.
 * @param string $key           Key of the option to fetch.
 * @param mixed  $default_value Default value to fetch if option is missing.
 * @return mixed
 */
function bsearch_get_blog_option( $blog_id, $key = '', $default_value = false ) {

	$blog_id = (int) $blog_id;

	if ( empty( $blog_id ) ) {
		$blog_id = get_current_blog_id();
	}

	if ( get_current_blog_id() === $blog_id ) {
		return bsearch_get_option( $key, $default_value );
	}

	if ( is_multisite() ) {
		switch_to_blog( $blog_id );
		$value = bsearch_get_option( $key, $default_value );
		restore_current_blog();
	} else {
		$value = bsearch_get_option( $key, $default_value );
	}

	/**
	 * Filters a blog option value.
	 *
	 * @since 4.2.0
	 *
	 * @param mixed  $value   The option value.
	 * @param int    $blog_id Blog ID.
	 * @param string $key     Option key.
	 */
	return apply_filters( "bsearch_blog_option_{$key}", $value, $blog_id, $key );
}


/**
 * Update an option
 *
 * Updates an bsearch setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the bsearch_options array.
 *
 * @since 2.2.0
 *
 * @param string          $key   The Key to update.
 * @param string|bool|int $value The value to set the key to.
 * @return boolean   True if updated, false if not.
 */
function bsearch_update_option( $key = '', $value = false ) {

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// If no value, delete.
	if ( false === $value ) {
		$remove_option = bsearch_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings.
	$options = get_option( 'bsearch_settings' );

	/**
	 * Filters the value before it is updated
	 *
	 * @since 2.2.0
	 *
	 * @param string|bool|int $value The value to set the key to
	 * @param string  $key   The Key to update
	 */
	$value = apply_filters( 'bsearch_update_option', $value, $key );

	// Next let's try to update the value.
	$options[ $key ] = $value;
	$did_update      = update_option( 'bsearch_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		global $bsearch_settings;
		$bsearch_settings[ $key ] = $value;
	}
	return $did_update;
}


/**
 * Remove an option
 *
 * Removes an bsearch setting value in both the db and the global variable.
 *
 * @since 2.2.0
 *
 * @param string $key The Key to update.
 * @return boolean   True if updated, false if not.
 */
function bsearch_delete_option( $key = '' ) {

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings.
	$options = get_option( 'bsearch_settings' );

	// Next let's try to update the value.
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'bsearch_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		global $bsearch_settings;
		$bsearch_settings = $options;
	}
	return $did_update;
}

/**
 * Flattens bsearch_get_registered_settings() into $setting[id] => $setting[type] format.
 *
 * @since 2.2.0
 *
 * @return array Default settings
 */
function bsearch_get_registered_settings_types() {

	$options = array();

	// Populate some default values.
	foreach ( \WebberZone\Better_Search\Admin\Settings::get_registered_settings() as $tab => $settings ) {
		foreach ( $settings as $option ) {
			$options[ $option['id'] ] = $option['type'];
		}
	}

	/**
	 * Filters the settings array.
	 *
	 * @since 2.2.0
	 *
	 * @param array   $options Default settings.
	 */
	return apply_filters( 'bsearch_get_settings_types', $options );
}


/**
 * Default settings.
 *
 * @since 2.2.0
 *
 * @return array Default settings
 */
function bsearch_settings_defaults() {

	$options       = array();
	$default_types = array(
		'color',
		'css',
		'csv',
		'file',
		'html',
		'multicheck',
		'number',
		'numbercsv',
		'password',
		'postids',
		'posttypes',
		'radio',
		'radiodesc',
		'repeater',
		'select',
		'sensitive',
		'taxonomies',
		'text',
		'textarea',
		'thumbsizes',
		'url',
		'wysiwyg',
	);

	// Populate some default values.
	foreach ( \WebberZone\Better_Search\Admin\Settings::get_registered_settings() as $tab => $settings ) {
		foreach ( $settings as $option ) {
			if ( ! isset( $option['id'] ) ) {
				continue;
			}

			$setting_id    = $option['id'];
			$setting_type  = $option['type'] ?? '';
			$default_value = '';

			// When checkbox is set to true, set this to 1.
			if ( 'checkbox' === $setting_type ) {
				$default_value = isset( $option['default'] ) ? (int) (bool) $option['default'] : 0;
			} elseif ( isset( $option['default'] ) && in_array( $setting_type, $default_types, true ) ) {
				$default_value = $option['default'];
			}

			$options[ $setting_id ] = $default_value;
		}
	}

	/**
	 * Filters the default settings array.
	 *
	 * @since 2.2.0
	 *
	 * @param array   $options Default settings.
	 */
	return apply_filters( 'bsearch_settings_defaults', $options );
}


/**
 * Get the default option for a specific key
 *
 * @since 2.2.0
 *
 * @param string $key Key of the option to fetch.
 * @return mixed
 */
function bsearch_get_default_option( $key = '' ) {

	$default_value_settings = bsearch_settings_defaults();

	if ( array_key_exists( $key, $default_value_settings ) ) {
		return $default_value_settings[ $key ];
	} else {
		return false;
	}
}


/**
 * Reset settings.
 *
 * @since 2.2.0
 *
 * @return void
 */
function bsearch_settings_reset() {
	delete_option( 'bsearch_settings' );
}
