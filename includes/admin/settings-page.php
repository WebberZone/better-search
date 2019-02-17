<?php
/**
 * Renders the settings page.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, etc.
 *
 * @link https://webberzone.com
 * @since 2.2.0
 *
 * @package Better Search
 * @subpackage Admin/Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Render the settings page.
 *
 * @since 2.2.0
 *
 * @return void
 */
function bsearch_options_page() {
	$active_tab = isset( $_GET['tab'] ) && array_key_exists( sanitize_key( wp_unslash( $_GET['tab'] ) ), bsearch_get_settings_sections() ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	ob_start();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Better Search Settings', 'better-search' ); ?></h1>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<ul class="nav-tab-wrapper" style="padding:0">
				<?php
				foreach ( bsearch_get_settings_sections() as $tab_id => $tab_name ) {

					$active = $active_tab === $tab_id ? ' ' : '';

					echo '<li><a href="#' . esc_attr( $tab_id ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab ' . sanitize_html_class( $active ) . '">';
						echo esc_html( $tab_name );
					echo '</a></li>';

				}
				?>
			</ul>

			<form method="post" action="options.php">

				<?php settings_fields( 'bsearch_settings' ); ?>

				<?php foreach ( bsearch_get_settings_sections() as $tab_id => $tab_name ) : ?>

				<div id="<?php echo esc_attr( $tab_id ); ?>">
					<table class="form-table">
					<?php
						do_settings_fields( 'bsearch_settings_' . $tab_id, 'bsearch_settings_' . $tab_id );
					?>
					</table>
					<p>
					<?php
						// Default submit button.
						submit_button(
							__( 'Save Changes', 'better-search' ),
							'primary',
							'submit',
							false
						);

						echo '&nbsp;&nbsp;';

						// Reset button.
						$confirm = esc_js( __( 'Do you really want to reset all these settings to their default values?', 'better-search' ) );
						submit_button(
							__( 'Reset all settings', 'better-search' ),
							'secondary',
							'settings_reset',
							false,
							array(
								'onclick' => "return confirm('{$confirm}');",
							)
						);
					?>
					</p>
				</div><!-- /#tab_id-->

				<?php endforeach; ?>

			</form>

		</div><!-- /#post-body-content -->

		<div id="postbox-container-1" class="postbox-container">

			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<?php include_once 'sidebar.php'; ?>
			</div><!-- /#side-sortables -->

		</div><!-- /#postbox-container-1 -->
		</div><!-- /#post-body -->
		<br class="clear" />
		</div><!-- /#poststuff -->

	</div><!-- /.wrap -->

	<?php
	echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Array containing the settings' sections.
 *
 * @since 2.2.0
 *
 * @return array Settings array
 */
function bsearch_get_settings_sections() {
	$bsearch_settings_sections = array(
		'general' => __( 'General', 'better-search' ),
		'search'  => __( 'Search', 'better-search' ),
		'heatmap' => __( 'Heatmap', 'better-search' ),
		'styles'  => __( 'Styles', 'better-search' ),
	);

	/**
	 * Filter the array containing the settings' sections.
	 *
	 * @since 2.2.0
	 *
	 * @param array $bsearch_settings_sections Settings array
	 */
	return apply_filters( 'bsearch_settings_sections', $bsearch_settings_sections );

}


/**
 * Miscellaneous callback funcion
 *
 * @since 2.2.0
 *
 * @param array $args Arguments passed by the setting.
 * @return void
 */
function bsearch_missing_callback( $args ) {
	/* translators: %s: Setting ID. */
	printf( esc_html__( 'The callback function used for the <strong>%s</strong> setting is missing.', 'better-search' ), esc_html( $args['id'] ) );
}


/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 2.2.0
 *
 * @param array $args Arguments passed by the setting.
 * @return void
 */
function bsearch_header_callback( $args ) {

	$html = '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/**
	 * After Settings Output filter
	 *
	 * @since 2.2.0
	 * @param string $html HTML string.
	 * @param array  $args Arguments array.
	 */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Display text fields.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_text_callback( $args ) {

	// First, we read the options collection.
	global $bsearch_settings;

	if ( isset( $bsearch_settings[ $args['id'] ] ) ) {
		$value = $bsearch_settings[ $args['id'] ];
	} else {
		$value = isset( $args['options'] ) ? $args['options'] : '';
	}

	$size = sanitize_html_class( ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular' );

	$class = sanitize_html_class( $args['field_class'] );

	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
	$readonly = ( isset( $args['readonly'] ) && true === $args['readonly'] ) ? ' readonly="readonly"' : '';

	$attributes = $disabled . $readonly;

	foreach ( (array) $args['field_attributes'] as $attribute => $val ) {
		$attributes .= sprintf( ' %1$s="%2$s"', $attribute, esc_attr( $val ) );
	}

	$html  = sprintf( '<input type="text" id="bsearch_settings[%1$s]" name="bsearch_settings[%1$s]" class="%2$s" value="%3$s" %4$s />', sanitize_key( $args['id'] ), $class . ' ' . $size . '-text', esc_attr( stripslashes( $value ) ), $attributes );
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Display csv fields.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_csv_callback( $args ) {

	bsearch_text_callback( $args );
}


/**
 * Display CSV fields of numbers.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_numbercsv_callback( $args ) {

	bsearch_csv_callback( $args );
}


/**
 * Display textarea.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_textarea_callback( $args ) {

	// First, we read the options collection.
	global $bsearch_settings;

	if ( isset( $bsearch_settings[ $args['id'] ] ) ) {
		$value = $bsearch_settings[ $args['id'] ];
	} else {
		$value = isset( $args['options'] ) ? $args['options'] : '';
	}

	$html  = sprintf( '<textarea class="large-text" cols="50" rows="10" id="bsearch_settings[%1$s]" name="bsearch_settings[%1$s]">%2$s</textarea>', sanitize_key( $args['id'] ), esc_textarea( stripslashes( $value ) ) );
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Display CSS fields.
 *
 * @since 2.5.1
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_css_callback( $args ) {

	bsearch_textarea_callback( $args );
}


/**
 * Display checboxes.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_checkbox_callback( $args ) {

	// First, we read the options collection.
	global $bsearch_settings;

	$checked = ! empty( $bsearch_settings[ $args['id'] ] ) ? checked( 1, $bsearch_settings[ $args['id'] ], false ) : '';
	$default = isset( $args['options'] ) ? $args['options'] : '';
	$set     = isset( $bsearch_settings[ $args['id'] ] ) ? $bsearch_settings[ $args['id'] ] : '';

	$html  = sprintf( '<input type="hidden" name="bsearch_settings[%1$s]" value="-1" />', sanitize_key( $args['id'] ) );
	$html .= sprintf( '<input type="checkbox" id="bsearch_settings[%1$s]" name="bsearch_settings[%1$s]" value="1" %2$s />', sanitize_key( $args['id'] ), $checked );
	$html .= ( $set <> $default ) ? '<em style="color:orange"> ' . esc_html__( 'Modified from default setting', 'better-search' ) . '</em>' : ''; //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_multicheck_callback( $args ) {
	global $bsearch_settings;
	$html = '';

	if ( ! empty( $args['options'] ) ) {
		$html .= sprintf( '<input type="hidden" name="bsearch_settings[%1$s]" value="-1" />', $args['id'] );

		foreach ( $args['options'] as $key => $option ) {
			if ( isset( $bsearch_settings[ $args['id'] ][ $key ] ) ) {
				$enabled = $key;
			} else {
				$enabled = null;
			}

			$html .= sprintf( '<input name="bsearch_settings[%1$s][%2$s]" id="bsearch_settings[%1$s][%2$s]" type="checkbox" value="%3$s" %4$s /> ', sanitize_key( $args['id'] ), sanitize_key( $key ), esc_attr( $key ), checked( $key, $enabled, false ) );
			$html .= sprintf( '<label for="bsearch_settings[%1$s][%2$s]">%3$s</label> <br />', sanitize_key( $args['id'] ), sanitize_key( $key ), $option );
		}

		$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_radio_callback( $args ) {
	global $bsearch_settings;
	$html = '';

	foreach ( $args['options'] as $key => $option ) {
		$checked = false;

		if ( isset( $bsearch_settings[ $args['id'] ] ) && $bsearch_settings[ $args['id'] ] === $key ) {
			$checked = true;
		} elseif ( isset( $args['default'] ) && $args['default'] === $key && ! isset( $bsearch_settings[ $args['id'] ] ) ) {
			$checked = true;
		}

		$html .= sprintf( '<input name="bsearch_settings[%1$s]" id="bsearch_settings[%1$s][%2$s]" type="radio" value="%2$s" %3$s /> ', sanitize_key( $args['id'] ), $key, checked( true, $checked, false ) );
		$html .= sprintf( '<label for="bsearch_settings[%1$s][%2$s]">%3$s</label> <br />', sanitize_key( $args['id'] ), $key, $option );
	}

	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Radio callback with description.
 *
 * Renders radio boxes with each item having it separate description.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_radiodesc_callback( $args ) {
	global $bsearch_settings;
	$html = '';

	foreach ( $args['options'] as $option ) {
		$checked = false;

		if ( isset( $bsearch_settings[ $args['id'] ] ) && $bsearch_settings[ $args['id'] ] === $option['id'] ) {
			$checked = true;
		} elseif ( isset( $args['default'] ) && $args['default'] === $option['id'] && ! isset( $bsearch_settings[ $args['id'] ] ) ) {
			$checked = true;
		}

		$html .= sprintf( '<input name="bsearch_settings[%1$s]" id="bsearch_settings[%1$s][%2$s]" type="radio" value="%2$s" %3$s /> ', sanitize_key( $args['id'] ), $option['id'], checked( true, $checked, false ) );
		$html .= sprintf( '<label for="bsearch_settings[%1$s][%2$s]">%3$s</label>', sanitize_key( $args['id'] ), $option['id'], $option['name'] );
		$html .= ': <em>' . wp_kses_post( $option['description'] ) . '</em> <br />';
	}

	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Callback for thumbnail sizes
 *
 * Renders list of radio boxes with various thumbnail sizes.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_thumbsizes_callback( $args ) {
	global $bsearch_settings;
	$html = '';

	if ( ! isset( $args['options']['bsearch_thumbnail'] ) ) {
		$args['options']['bsearch_thumbnail'] = array(
			'name'   => 'bsearch_thumbnail',
			'width'  => bsearch_get_option( 'thumb_width', 150 ),
			'height' => bsearch_get_option( 'thumb_height', 150 ),
			'crop'   => bsearch_get_option( 'thumb_crop', true ),
		);
	}

	foreach ( $args['options'] as $option ) {
		$checked = false;

		if ( isset( $bsearch_settings[ $args['id'] ] ) && $bsearch_settings[ $args['id'] ] === $option['name'] ) {
			$checked = true;
		} elseif ( isset( $args['default'] ) && $args['default'] === $option['name'] && ! isset( $bsearch_settings[ $args['id'] ] ) ) {
			$checked = true;
		}

		$html .= sprintf( '<input name="bsearch_settings[%1$s]" id="bsearch_settings[%1$s][%2$s]" type="radio" value="%2$s" %3$s /> ', sanitize_key( $args['id'] ), $option['name'], checked( true, $checked, false ) );
		$html .= sprintf( '<label for="bsearch_settings[%1$s][%2$s]">%3$s</label> <br />', sanitize_key( $args['id'] ), $option['name'], $option['name'] . ' (' . $option['width'] . 'x' . $option['height'] . ')' );
	}

	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_number_callback( $args ) {
	global $bsearch_settings;

	if ( isset( $bsearch_settings[ $args['id'] ] ) ) {
		$value = $bsearch_settings[ $args['id'] ];
	} else {
		$value = isset( $args['options'] ) ? $args['options'] : '';
	}

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';

	$html  = sprintf( '<input type="number" step="%1$s" max="%2$s" min="%3$s" class="%4$s" id="bsearch_settings[%5$s]" name="bsearch_settings[%5$s]" value="%6$s"/>', esc_attr( $step ), esc_attr( $max ), esc_attr( $min ), sanitize_html_class( $size ) . '-text', sanitize_key( $args['id'] ), esc_attr( stripslashes( $value ) ) );
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_select_callback( $args ) {
	global $bsearch_settings;

	if ( isset( $bsearch_settings[ $args['id'] ] ) ) {
		$value = $bsearch_settings[ $args['id'] ];
	} else {
		$value = isset( $args['default'] ) ? $args['default'] : '';
	}

	if ( isset( $args['chosen'] ) ) {
		$chosen = 'class="bsearch-chosen"';
	} else {
		$chosen = '';
	}

	$html = sprintf( '<select id="bsearch_settings[%1$s]" name="bsearch_settings[%1$s]" %2$s />', sanitize_key( $args['id'] ), $chosen );

	foreach ( $args['options'] as $option => $name ) {
		$html .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', sanitize_key( $option ), selected( $option, $value, false ), $name );
	}

	$html .= '</select>';
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_descriptive_text_callback( $args ) {
	$html = '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Display csv fields.
 *
 * @since 2.2.0
 *
 * @param array $args Array of arguments.
 * @return void
 */
function bsearch_posttypes_callback( $args ) {

	global $bsearch_settings;
	$html = '';

	if ( isset( $bsearch_settings[ $args['id'] ] ) ) {
		$options = $bsearch_settings[ $args['id'] ];
	} else {
		$options = isset( $args['options'] ) ? $args['options'] : '';
	}

	// If post_types is empty or contains a query string then use parse_str else consider it comma-separated.
	if ( is_array( $options ) ) {
		$post_types = $options;
	} elseif ( ! is_array( $options ) && false === strpos( $options, '=' ) ) {
		$post_types = explode( ',', $options );
	} else {
		parse_str( $options, $post_types );
	}

	$wp_post_types   = get_post_types(
		array(
			'public' => true,
		)
	);
	$posts_types_inc = array_intersect( $wp_post_types, $post_types );

	foreach ( $wp_post_types as $wp_post_type ) {

		$html .= sprintf( '<input name="bsearch_settings[%1$s][%2$s]" id="bsearch_settings[%1$s][%2$s]" type="checkbox" value="%2$s" %3$s /> ', sanitize_key( $args['id'] ), esc_attr( $wp_post_type ), checked( true, in_array( $wp_post_type, $posts_types_inc, true ), false ) );
		$html .= sprintf( '<label for="bsearch_settings[%1$s][%2$s]">%2$s</label> <br />', sanitize_key( $args['id'] ), $wp_post_type );

	}

	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	/** This filter has been defined in settings-page.php */
	echo apply_filters( 'bsearch_after_setting_output', $html, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Function to add an action to search for tags using Ajax.
 *
 * @since 2.1.0
 *
 * @return void
 */
function bsearch_tags_search() {

	if ( ! isset( $_REQUEST['tax'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_die( 0 );
	}

	$taxonomy = sanitize_key( $_REQUEST['tax'] );
	$tax      = get_taxonomy( $taxonomy );
	if ( ! $tax ) {
		wp_die( 0 );
	}

	if ( ! current_user_can( $tax->cap->assign_terms ) ) {
		wp_die( -1 );
	}

	$s = isset( $_REQUEST['q'] ) ? wp_unslash( $_REQUEST['q'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Recommended

	$comma = _x( ',', 'tag delimiter' );
	if ( ',' !== $comma ) {
		$s = str_replace( $comma, ',', $s );
	}
	if ( false !== strpos( $s, ',' ) ) {
		$s = explode( ',', $s );
		$s = $s[ count( $s ) - 1 ];
	}
	$s = trim( $s );

	/** This filter has been defined in /wp-admin/includes/ajax-actions.php */
	$term_search_min_chars = (int) apply_filters( 'term_search_min_chars', 2, $tax, $s );

	/*
	 * Require $term_search_min_chars chars for matching (default: 2)
	 * ensure it's a non-negative, non-zero integer.
	 */
	if ( ( 0 === $term_search_min_chars ) || ( strlen( $s ) < $term_search_min_chars ) ) {
		wp_die();
	}

	$results = get_terms(
		$taxonomy,
		array(
			'name__like' => $s,
			'fields'     => 'names',
			'hide_empty' => false,
		)
	);

	echo wp_json_encode( $results );
	wp_die();

}
add_action( 'wp_ajax_nopriv_bsearch_tag_search', 'bsearch_tags_search' );
add_action( 'wp_ajax_bsearch_tag_search', 'bsearch_tags_search' );


