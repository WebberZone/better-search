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
		$instance          = $old_instance;
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
 * @since 2.5.0
 */
function bsearch_register_search_widget() {
	register_widget( 'BSearch_Search_Box' );
}
add_action( 'widgets_init', 'bsearch_register_search_widget' );

