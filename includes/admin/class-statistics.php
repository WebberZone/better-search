<?php
/**
 * Better Search Display statistics page.
 *
 * @package   Better_Search
 */

namespace WebberZone\Better_Search\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Better_Search_Statistics class.
 *
 * Renders the popular searches page.
 *
 * @since 3.3.0
 */
class Statistics {

	/**
	 * Holds the Popular Searches Statistics Table
	 *
	 * @var \WebberZone\Better_Search\Admin\Statistics_Table
	 */
	public $pop_search_table;

	/**
	 * Parent Menu ID.
	 *
	 * @since 3.3.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Class constructor.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook === $this->parent_id ) {
			wp_enqueue_script( 'better-search-admin-js' );
			wp_enqueue_style( 'better-search-admin-ui-css' );
		}
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.0.0
	 */
	public function admin_menu() {
		$this->parent_id = add_submenu_page(
			'bsearch_dashboard',
			__( 'Better Search Popular Searches', 'better-search' ),
			__( 'Popular Searches', 'better-search' ),
			'manage_options',
			'bsearch_popular_searches',
			array( $this, 'render_page' )
		);

		add_submenu_page(
			'bsearch_dashboard',
			__( 'Better Search Daily Popular Searches', 'better-search' ),
			__( 'Daily Popular Searches', 'better-search' ),
			'manage_options',
			'bsearch_popular_searches&orderby=daily_count&order=desc',
			array( $this, 'render_page' )
		);

		add_action( "load-{$this->parent_id}", array( $this, 'screen_option' ) );
	}

	/**
	 * Set screen.
	 *
	 * @param  string $status Status of screen.
	 * @param  string $option Option name.
	 * @param  string $value  Option value.
	 * @return string Value.
	 */
	public static function set_screen( $status, $option, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
		return $value;
	}

	/**
	 * Plugin settings page
	 */
	public function render_page() {
		$page = '';

		if ( isset( $_REQUEST['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Better Search Popular Searches', 'better-search' ); ?></h1>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="get">
								<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
								<?php
								$this->pop_search_table->prepare_items();
								$this->pop_search_table->search_box( __( 'Search Table', 'better-search' ), 'better-search' );
								$this->pop_search_table->display();
								?>
							</form>
						</div>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<?php Admin::display_admin_sidebar(); ?>
						</div><!-- /side-sortables -->
					</div><!-- /postbox-container-1 -->
				</div><!-- /post-body -->
				<br class="clear" />
			</div><!-- /poststuff -->
		</div>
		<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Popular Searches per page', 'better-search' ),
			'default' => 20,
			'option'  => 'pop_searches_per_page',
		);
		add_screen_option( $option, $args );
		$this->pop_search_table = new Statistics_Table();
	}
}
