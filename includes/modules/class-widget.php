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
 * Create a Wordpress Widget for Popular search terms.
 *
 * @since	1.3.3
 *
 * @extends WP_Widget
 */
class BSearch_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'widget_bsearch_pop', // Base ID
			__( 'Popular Searches [Better Search]', 'better-search' ), // Name
			array( 'description' => __( 'Display the popular searches', 'better-search' ) ) // Args
		);
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$daily = isset( $instance['title'] ) ? esc_attr( $instance['daily'] ) : 'overall';
		$daily_range = isset( $instance['daily_range'] ) ? esc_attr( $instance['daily_range'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title', 'better-search' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<select class="widefat" id="<?php echo $this->get_field_id( 'daily' ); ?>" name="<?php echo $this->get_field_name( 'daily' ); ?>">
				<option value="overall" <?php if ( $daily == 'overall' ) { echo 'selected="selected"'; } ?>><?php _e( 'Overall', 'better-search' ); ?></option>
				<option value="daily" <?php if ( $daily == 'daily' ) { echo 'selected="selected"'; } ?>><?php _e( 'Custom time period (Enter below)', 'better-search' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'daily_range' ); ?>">
				<?php _e( 'Range in number of days (applies only to custom option above)', 'better-search' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'daily_range' ); ?>" name="<?php echo $this->get_field_name( 'daily_range' ); ?>" type="text" value="<?php echo esc_attr( $daily_range ); ?>" />
			</label>
		</p>

		<?php
			/**
			 * Fires after Better Search widget options.
			 *
			 * @since	2.0.0
			 *
			 * @param	array	$instance	Widget options array
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
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['daily'] = strip_tags( $new_instance['daily'] );
		$instance['daily_range'] = strip_tags( $new_instance['daily_range'] );

		/**
		 * Filters Update widget options array.
		 *
		 * @since	2.0.0
		 *
		 * @param	array	$instance	Widget options array
		 */
		return apply_filters( 'bsearch_widget_options_update' , $instance );
	} //ending update


	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {
		global $wpdb, $bsearch_url, $bsearch_settings;

		$daily_range = isset( $instance['daily_range'] ) ? $instance['daily_range'] : $bsearch_settings['daily_range'];

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? strip_tags( $bsearch_settings['title'] ) : $instance['title'] );

		$daily = isset( $instance['daily'] ) ? $instance['daily'] : 'overall';

		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];

		if ( 'overall' == $daily ) {
			echo get_bsearch_heatmap( array(
				'daily' => 0,
				'daily_range' => $daily_range,
			) );
		} else {
			echo get_bsearch_heatmap( array(
				'daily' => 1,
				'daily_range' => $daily_range,
			) );
		}
		if ( $bsearch_settings['show_credit'] ) {
			echo '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
		}

		echo $args['after_widget'];

	} //ending function widget
}


class BSearch_Search_Box extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'bsearch_search_box', // Base ID
			__( 'Search Form [Better Search]', 'better-search' ), // Name
			array( 'description' => __( 'Search Form', 'better-search' ), ) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param	array	$args
	 * @param 	array	$instance
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		get_search_form();
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Search', 'better-search' );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		/**
		 * Filters Update widget options array for the Search box.
		 *
		 * @since	2.1.0
		 *
		 * @param	array	$instance	Widget options array
		 */
		return apply_filters( 'bsearch_search_widget_options_update' , $instance );
	}
}


/**
 * Initialise Better Search Widgets.
 *
 * @since	1.3.3
 */
function bsearch_register_widget() {
	register_widget( 'BSearch_Widget' );
	register_widget( 'BSearch_Search_Box' );
}
add_action( 'widgets_init', 'bsearch_register_widget' );


