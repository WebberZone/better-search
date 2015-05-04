<?php
/**
 * Better Search Widget
 *
 * @package BSearch
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
			__( 'Popular Searches [Better Search]', BSEARCH_LOCAL_NAME ), // Name
			array( 'description' => __( 'Display the popular searches', BSEARCH_LOCAL_NAME ), ) // Args
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
				<?php _e( 'Title', BSEARCH_LOCAL_NAME ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<select class="widefat" id="<?php echo $this->get_field_id( 'daily' ); ?>" name="<?php echo $this->get_field_name( 'daily' ); ?>">
				<option value="overall" <?php if ( $daily == 'overall' ) echo 'selected="selected"' ?>><?php _e( 'Overall', BSEARCH_LOCAL_NAME ); ?></option>
				<option value="daily" <?php if ( $daily == 'daily' ) echo 'selected="selected"' ?>><?php _e( 'Custom time period (Enter below)', BSEARCH_LOCAL_NAME ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'daily_range' ); ?>">
				<?php _e( 'Range in number of days (applies only to custom option above)', BSEARCH_LOCAL_NAME ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'daily_range' ); ?>" name="<?php echo $this->get_field_name( 'daily_range' ); ?>" type="text" value="<?php echo esc_attr( $daily_range ); ?>" />
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

		extract( $args, EXTR_SKIP );

		$daily_range = isset( $instance['daily_range'] ) ? $instance['daily_range'] : $bsearch_settings['daily_range'];

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? strip_tags( $bsearch_settings['title'] ) : $instance['title'] );

		$daily = isset( $instance['daily'] ) ? $instance['daily'] : 'overall';

		echo $before_widget;
		echo $before_title . $title . $after_title;

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
			echo '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
		}

		echo $after_widget;

	} //ending function widget
}


/**
 * Initialise Better Search Widgets.
 *
 * @since	1.3.3
 *
 */
function bsearch_register_widget() {
	register_widget( 'BSearch_Widget' );
}
add_action( 'widgets_init', 'bsearch_register_widget', 1 );


?>