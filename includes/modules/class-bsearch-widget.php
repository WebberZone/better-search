<?php
/**
 * Better Search Widgets
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}


/**
 * Create a WordPress Widget for Popular search terms.
 *
 * @since   1.3.3
 *
 * @extends WP_Widget
 */
class BSearch_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'widget_bsearch_pop', // Base ID.
			__( 'Popular Searches [Better Search]', 'better-search' ), // Name.
			array( 'description' => __( 'Display the popular searches', 'better-search' ) ) // Args.
		);
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title       = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$daily       = isset( $instance['title'] ) ? esc_attr( $instance['daily'] ) : 'overall';
		$daily_range = isset( $instance['daily_range'] ) ? esc_attr( $instance['daily_range'] ) : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_attr_e( 'Title', 'better-search' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'daily' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'daily' ) ); ?>">
				<option value="overall"
				<?php
				if ( 'overall' === $daily ) {
					echo 'selected="selected"'; }
				?>
><?php esc_attr_e( 'Overall', 'better-search' ); ?></option>
				<option value="daily"
				<?php
				if ( 'daily' === $daily ) {
					echo 'selected="selected"'; }
				?>
><?php esc_attr_e( 'Custom time period (Enter below)', 'better-search' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'daily_range' ) ); ?>">
				<?php esc_attr_e( 'Range in number of days (applies only to custom option above)', 'better-search' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'daily_range' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'daily_range' ) ); ?>" type="text" value="<?php echo esc_attr( $daily_range ); ?>" />
			</label>
		</p>

		<?php
			/**
			 * Fires after Better Search widget options.
			 *
			 * @since   2.0.0
			 *
			 * @param   array   $instance   Widget options array
			 */
			do_action( 'bsearch_widget_options_after', $instance );
		?>

		<?php
	} //ending form creation


	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                = $old_instance;
		$instance['title']       = wp_strip_all_tags( $new_instance['title'] );
		$instance['daily']       = wp_strip_all_tags( $new_instance['daily'] );
		$instance['daily_range'] = wp_strip_all_tags( $new_instance['daily_range'] );

		/**
		 * Filters Update widget options array.
		 *
		 * @since   2.0.0
		 *
		 * @param   array   $instance   Widget options array
		 */
		return apply_filters( 'bsearch_widget_options_update', $instance );
	} //ending update


	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		global $wpdb;

		$daily_range = isset( $instance['daily_range'] ) ? $instance['daily_range'] : bsearch_get_option( 'daily_range' );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? wp_strip_all_tags( bsearch_get_option( 'title' ) ) : $instance['title'] );

		$daily = isset( $instance['daily'] ) ? $instance['daily'] : 'overall';

		echo $args['before_widget']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . $title . $args['after_title']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( 'overall' === $daily ) {
			echo get_bsearch_heatmap( //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'daily'       => 0,
					'daily_range' => $daily_range,
				)
			);
		} else {
			echo get_bsearch_heatmap( //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'daily'       => 1,
					'daily_range' => $daily_range,
				)
			);
		}
		if ( bsearch_get_option( 'show_credit' ) ) {
			echo '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
		}

		echo $args['after_widget']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	} //ending function widget
}


/**
 * Create a WordPress Widget with the search box.
 *
 * @since   1.3.3
 *
 * @extends WP_Widget
 */
class BSearch_Search_Box extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'bsearch_search_box', // Base ID.
			__( 'Search Form [Better Search]', 'better-search' ), // Name.
			array( 'description' => __( 'Search Form', 'better-search' ) ) // Args.
		);
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
		echo $args['before_widget']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		get_search_form();
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
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Search', 'better-search' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
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
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';

		/**
		 * Filters Update widget options array for the Search box.
		 *
		 * @since   2.1.0
		 *
		 * @param   array   $instance   Widget options array
		 */
		return apply_filters( 'bsearch_search_widget_options_update', $instance );
	}
}


/**
 * Initialise Better Search Widgets.
 *
 * @since   1.3.3
 */
function bsearch_register_widget() {
	register_widget( 'BSearch_Widget' );
	register_widget( 'BSearch_Search_Box' );
}
add_action( 'widgets_init', 'bsearch_register_widget' );


