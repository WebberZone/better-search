<?php
/**
 * Better Search Widgets
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Frontend\Widgets;

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}


/**
 * Create a WordPress Widget with the search box.
 *
 * @since 3.3.0
 */
class Search_Box extends \WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'widget_bsearch_form',
			'description'                 => __( 'A better search form for your site.', 'better-search' ),
			'customize_selective_refresh' => true,
			'show_instance_in_rest'       => true,
		);

		parent::__construct(
			'bsearch_search_box',
			__( 'Search Form [Better Search]', 'better-search' ),
			$widget_ops
		);
		add_action( 'wp_enqueue_scripts', array( $this, 'front_end_styles' ), 11 );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$show_post_types = isset( $instance['show_post_types'] ) ? $instance['show_post_types'] : '';
		$post_types      = $show_post_types ? bsearch_get_option( 'post_types' ) : '';

		echo $args['before_widget']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		the_bsearch_form(
			'',
			/**
			* Filters the arguments for the Better Search form widget.
			*
			* @since 3.0.0
			*
			* @see get_bsearch_form()
			*
			* @param array $args     An array of arguments used to retrieve the Better Search form.
			* @param array $instance Array of settings for the current widget.
			*/
			apply_filters(
				'widget_bsearch_form_args',
				array(
					'post_types'      => $post_types,
					'show_post_types' => $show_post_types,
				),
				$instance
			)
		);

		echo $args['after_widget']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function form( $instance ) {
		$title           = isset( $instance['title'] ) ? $instance['title'] : '';
		$show_post_types = isset( $instance['show_post_types'] ) ? (bool) $instance['show_post_types'] : false;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'better-search' ); ?>:</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_post_types ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_post_types' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_post_types' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_post_types' ) ); ?>"><?php esc_html_e( 'Display post types dropdown?', 'better-search' ); ?></label>
		</p>

		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = $old_instance;
		$new_instance                = wp_parse_args( (array) $new_instance, array( 'title' => '' ) );
		$instance['title']           = sanitize_text_field( $new_instance['title'] );
		$instance['show_post_types'] = isset( $new_instance['show_post_types'] ) ? (bool) $new_instance['show_post_types'] : false;

		/**
		 * Filters Update widget options array for the Search box.
		 *
		 * @since   2.1.0
		 *
		 * @param   array   $instance   Widget options array
		 */
		return apply_filters( 'bsearch_search_widget_options_update', $instance );
	}

	/**
	 * Add styles to the front end if the widget is active.
	 *
	 * @since 3.0.0
	 */
	public function front_end_styles() {

		// We need to process all instances because this function gets to run only once.
		$widget_settings = get_option( $this->option_name );

		foreach ( (array) $widget_settings as $instance => $options ) {

			// Identify instance.
			$widget_id = "{$this->id_base}-{$instance}";

			// Check if it's our instance.
			if ( ! is_active_widget( false, $widget_id, $this->id_base, true ) ) {
				continue;   // Not active.
			}

			wp_enqueue_style( 'bsearch-style' );
			wp_add_inline_style( 'bsearch-style', esc_html( bsearch_get_option( 'custom_css' ) ) );
		}
	}
}
