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
 * Create a WordPress Widget for Popular search terms.
 *
 * @since 3.3.0
 */
class Search_Heatmap extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'widget_bsearch_pop',
			'description'                 => __( 'Popular searches cloud', 'better-search' ),
			'customize_selective_refresh' => true,
			'show_instance_in_rest'       => true,
		);

		parent::__construct(
			'widget_bsearch_pop',
			__( 'Popular Searches [Better Search]', 'better-search' ),
			$widget_ops
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
		$title       = isset( $instance['title'] ) ? $instance['title'] : '';
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
				<option value="overall" <?php selected( $daily, 'overall' ); ?>><?php esc_attr_e( 'Overall', 'better-search' ); ?></option>
				<option value="daily" <?php selected( $daily, 'daily' ); ?>><?php esc_attr_e( 'Custom time period', 'better-search' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'daily_range' ) ); ?>">
				<?php esc_attr_e( 'Range in number of days (custom time period only)', 'better-search' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'daily_range' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'daily_range' ) ); ?>" type="text" value="<?php echo esc_attr( $daily_range ); ?>" />
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
			do_action( 'bsearch_heatmap_options_after', $instance );
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
		$instance['daily']       = esc_attr( $new_instance['daily'] );
		$instance['daily_range'] = esc_attr( $new_instance['daily_range'] );

		/**
		 * Filters Update widget options array.
		 *
		 * @since   2.0.0
		 *
		 * @param   array   $instance   Widget options array
		 */
		return apply_filters( 'bsearch_heatmap_options_update', $instance );
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
		$default_title = wp_strip_all_tags( bsearch_get_option( 'title' ) );
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : $default_title;

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$daily       = isset( $instance['daily'] ) ? $instance['daily'] : 'overall';
		$daily       = ( 'overall' === $daily ) ? 0 : 1;
		$daily_range = isset( $instance['daily_range'] ) ? absint( $instance['daily_range'] ) : bsearch_get_option( 'daily_range' );

		echo $args['before_widget']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		the_bsearch_heatmap(
			/**
			* Filters the arguments for the Better Search form widget.
			*
			* @since 3.0.0
			*
			* @see the_bsearch_heatmap()
			*
			* @param array $args     An array of arguments used to retrieve the Better Search form.
			* @param array $instance Array of settings for the current widget.
			*/
			apply_filters(
				'widget_bsearch_heatmap_args',
				array(
					'daily'       => $daily,
					'daily_range' => $daily_range,
				),
				$instance
			)
		);

		echo $args['after_widget']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} //ending function widget
}
