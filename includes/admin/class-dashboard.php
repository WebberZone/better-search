<?php
/**
 * Dashboard.
 *
 * @link https://webberzone.com
 * @since 3.3.0
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Admin;

use WebberZone\Better_Search\Util\Helpers;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Dashboard Class.
 *
 * @since 3.3.0
 */
class Dashboard {

	/**
	 * Parent Menu ID.
	 *
	 * @since 3.3.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_bsearch_chart_data', array( $this, 'get_chart_data' ) );
	}

	/**
	 * Render the settings page.
	 *
	 * @since 3.3.0
	 */
	public function render_page() {
		ob_start();

		// Add date selector.
		$chart_to_date   = current_time( 'd M Y' );
		$chart_from_date = gmdate( 'd M Y', strtotime( '-1 month' ) );

		$post_date_from = ( isset( $_REQUEST['search-date-filter-from'] ) && check_admin_referer( 'bsearch-dashboard' ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['search-date-filter-from'] ) ) : $chart_from_date;

		$post_date_to = ( isset( $_REQUEST['search-date-filter-to'] ) && check_admin_referer( 'bsearch-dashboard' ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['search-date-filter-to'] ) ) : $chart_to_date;

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Better Search Dashboard', 'better-search' ); ?></h1>

			<?php settings_errors(); ?>

			<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<form method="post" >
					<?php wp_nonce_field( 'bsearch-dashboard' ); ?>

					<div>
						<input type="text" id="datepicker-from" name="search-date-filter-from" value="<?php echo esc_attr( $post_date_from ); ?>" size="11" />
						<input type="text" id="datepicker-to" name="search-date-filter-to" value="<?php echo esc_attr( $post_date_to ); ?>" size="11" />
						<?php
						submit_button(
							__( 'Update', 'better-search' ),
							'primary',
							'filter_action',
							false,
							array(
								'id'      => 'better-search-chart-submit',
								'onclick' => 'updateChart(); return false;',
							)
						);
						?>
					</div>
					<div>
						<canvas id="searches" width="400" height="150" aria-label="<?php esc_html_e( 'Better Search Searches', 'better-search' ); ?>" role="img"></canvas>
					</div>

				</form>

				<h2><?php esc_html_e( 'Historical searches', 'better-search' ); ?></h2>
				<ul class="nav-tab-wrapper" style="padding:0; border-bottom: 1px solid #ccc;">
					<?php
					foreach ( $this->get_tabs() as $tab_id => $tab_name ) {

						echo '<li style="padding:0; border:0; margin:0;"><a href="#' . esc_attr( $tab_id ) . '" title="' . esc_attr( $tab_name['title'] ) . '" class="nav-tab">';
							echo esc_html( $tab_name['title'] );
						echo '</a></li>';

					}
					?>
				</ul>

				<form method="post" action="options.php">

					<?php foreach ( $this->get_tabs() as $tab_id => $tab_name ) : ?>

					<div id="<?php echo esc_attr( $tab_id ); ?>">
						<table class="form-table">
						<?php
							$output = $this->display_popular_searches( $tab_name );
							echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
						</table>

						<div style="font-weight:bold;padding:5px;">

							<?php
							$query_args = array(
								'order' => 'desc',
							);
							$daily      = ( isset( $tab_name['daily'] ) ) ? $tab_name['daily'] : true;

							if ( $daily ) {
								$query_args['orderby'] = 'daily_count';

								if ( ! empty( $tab_name['from_date'] ) ) {
									$query_args['search-date-filter-from'] = gmdate( 'd+M+Y', strtotime( $tab_name['from_date'] ) );
								}
								if ( ! empty( $tab_name['to_date'] ) ) {
									$query_args['search-date-filter-to'] = gmdate( 'd+M+Y', strtotime( $tab_name['to_date'] ) );
								}
							} else {
								$query_args['orderby'] = 'total_count';
							}
							$url = add_query_arg( $query_args, admin_url( 'admin.php?page=bsearch_popular_searches' ) );

							?>

							<a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'View all popular searches', 'better-search' ); ?> &raquo;</a>

						</div>

					</div><!-- /#tab_id-->

					<?php endforeach; ?>

				</form>

			</div><!-- /#post-body-content -->

			<div id="postbox-container-1" class="postbox-container">

				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php include_once 'settings/sidebar.php'; ?>
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
	 * Admin Menu.
	 *
	 * @since 3.3.0
	 */
	public function admin_menu() {
		$this->parent_id = add_menu_page(
			esc_html__( 'Better Search Dashboard', 'better-search' ),
			esc_html__( 'Better Search', 'better-search' ),
			'manage_options',
			'bsearch_dashboard',
			array( $this, 'render_page' ),
			'dashicons-search'
		);

		add_submenu_page(
			'bsearch_dashboard',
			esc_html__( 'Better Search Dashboard', 'better-search' ),
			esc_html__( 'Dashboard', 'better-search' ),
			'manage_options',
			'bsearch_dashboard',
			array( $this, 'render_page' )
		);

		add_action( 'load-' . $this->parent_id, array( $this, 'help_tabs' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.3.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {

		if ( $hook === $this->parent_id ) {
			wp_enqueue_script( 'moment' );
			wp_enqueue_script( 'better-search-chartjs' );
			wp_enqueue_script( 'better-search-chart-datalabels-js' );
			wp_enqueue_script( 'better-search-chartjs-adapter-moment-js' );
			wp_enqueue_script( 'better-search-chart-data-js' );
			wp_enqueue_script( 'better-search-admin-js' );
			wp_localize_script(
				'better-search-chart-data-js',
				'bsearch_chart_data',
				array(
					'security'     => wp_create_nonce( 'bsearch-dashboard' ),
					'datasetlabel' => __( 'Searches', 'better-search' ),
					'charttitle'   => __( 'Daily Searches', 'better-search' ),
				)
			);
			wp_enqueue_style( 'better-search-admin-ui-css' );
		}
	}

	/**
	 * Function to add an action to search for tags using Ajax.
	 *
	 * @since 3.3.0
	 */
	public function get_chart_data() {
		global $wpdb;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		check_ajax_referer( 'bsearch-dashboard', 'security' );

		$blog_id = get_current_blog_id();

		// Add date selector.
		$to_date   = isset( $_REQUEST['to_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['to_date'] ) ) : current_time( 'd M Y' );
		$from_date = isset( $_REQUEST['from_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['from_date'] ) ) : gmdate( 'd M Y', strtotime( '-1 month' ) );

		$post_date_from = gmdate( 'Y-m-d', strtotime( $from_date ) );
		$post_date_to   = gmdate( 'Y-m-d', strtotime( $to_date ) );

		$sql = $wpdb->prepare(
			" SELECT SUM(cntaccess) AS searches, DATE(dp_date) as date
			FROM {$wpdb->prefix}bsearch_daily
			WHERE DATE(dp_date) >= DATE(%s)
			AND DATE(dp_date) <= DATE(%s)
			GROUP BY date
			ORDER BY date ASC
			",
			$post_date_from,
			$post_date_to,
		);

		$result = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		$data = array();
		foreach ( $result as $row ) {
			$data[] = $row;
		}

		echo wp_json_encode( $data );
		wp_die();
	}


	/**
	 * Array containing the settings' sections.
	 *
	 * @since 3.3.0
	 *
	 * @return array Settings array
	 */
	public function get_tabs() {
		$tabs = array(
			'today'         => array(
				'title'     => __( 'Today', 'better-search' ),
				'from_date' => current_time( 'd M Y' ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'yesterday'     => array(
				'title'     => __( 'Yesterday', 'better-search' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-1 day' ) ),
				'to_date'   => gmdate( 'd M Y', strtotime( '-1 day' ) ),
			),
			'lastweek'      => array(
				'title'     => __( 'Last 7 days', 'better-search' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-1 week' ) ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'lastfortnight' => array(
				'title'     => __( 'Last 14 days', 'better-search' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-2 weeks' ) ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'lastmonth'     => array(
				'title'     => __( 'Last 30 days', 'better-search' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-30 days' ) ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'overall'       => array(
				'title' => __( 'All time', 'better-search' ),
				'daily' => false,
			),
		);

		return $tabs;
	}

	/**
	 * Get popular searches for a date range.
	 *
	 * @since 3.3.0
	 *
	 * @param string|array $args {
	 *     Optional. Array or string of Query parameters.
	 *
	 *     @type bool         $daily       Set to true to get the daily/custom period searches. False for overall.
	 *     @type string       $from_date   From date. A date/time string.
	 *     @type int          $number Number of searches to fetch.
	 *     @type string       $to_date     To date. A date/time string.
	 * }
	 * @return string HTML table with popular searches.
	 */
	public function display_popular_searches( $args = array() ) {
		$output = '';

		$defaults = array(
			'daily'     => true,
			'from_date' => null,
			'number'    => 20,
			'to_date'   => null,
		);
		$args     = wp_parse_args( $args, $defaults );

		$results = $this->get_popular_searches( $args );

		ob_start();
		if ( $results ) :
			?>

			<table class="widefat striped">
			<?php
			foreach ( $results as $result ) :
				$searches = Helpers::number_format_i18n( $result->searches );
				?>
				<tr>
					<td>
						<a href="<?php echo esc_url( add_query_arg( 's', $result->name, home_url( '/' ) ) ); ?>" target="_blank"><?php echo esc_html( $result->name ); ?></a>
					</td>
					<td><?php echo esc_html( $searches ); ?></td>
				</tr>

			<?php endforeach; ?>
			</table>

		<?php else : ?>

				<?php esc_html_e( 'Sorry, no popular searches found.', 'better-search' ); ?>

		<?php endif; ?>

		<?php

		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Retrieve the popular searches.
	 *
	 * @since 3.3.0
	 *
	 * @param string|array $args {
	 *     Optional. Array or string of Query parameters.
	 *
	 *     @type bool         $daily       Set to true to get the daily/custom period searches. False for overall.
	 *     @type string       $from_date   From date. A date/time string.
	 *     @type int          $number Number of searches to fetch.
	 *     @type int          $offset      Offset.
	 *     @type string       $to_date     To date. A date/time string.
	 * }
	 * @return array Array of post objects.
	 */
	public function get_popular_searches( $args = array() ) {
		global $wpdb;

		// Initialise some variables.
		$fields  = array();
		$where   = '';
		$join    = '';
		$groupby = '';
		$orderby = '';
		$limits  = '';

		$defaults = array(
			'daily'     => true,
			'from_date' => null,
			'number'    => 20,
			'offset'    => 0,
			'to_date'   => null,
		);
		$args     = wp_parse_args( $args, $defaults );

		$table_name = Helpers::get_bsearch_table( $args['daily'] );

		// Fields to return.
		$fields[] = "{$table_name}.searchvar as name";
		$fields[] = ( $args['daily'] ) ? "SUM({$table_name}.cntaccess) as searches" : "{$table_name}.cntaccess as searches";

		$fields = implode( ', ', $fields );

		// Create the base WHERE clause.
		$where = " AND {$table_name}.searchvar != '' ";

		if ( isset( $args['from_date'] ) ) {
			$from_date = gmdate( 'Y-m-d', strtotime( $args['from_date'] ) );
			$where    .= $wpdb->prepare( " AND DATE({$table_name}.dp_date) >= DATE(%s) ", $from_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		if ( isset( $args['to_date'] ) ) {
			$to_date = gmdate( 'Y-m-d', strtotime( $args['to_date'] ) );
			$where  .= $wpdb->prepare( " AND DATE({$table_name}.dp_date) <= DATE(%s) ", $to_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		// Create the base GROUP BY clause.
		if ( $args['daily'] ) {
			$groupby = " {$table_name}.searchvar ";
		}

		// Create the base ORDER BY clause.
		$orderby = ' searches DESC, name ASC ';
		$orderby = " ORDER BY {$orderby} ";

		// Create the base LIMITS clause.
		$limits = $wpdb->prepare( ' LIMIT %d, %d ', $args['offset'], $args['number'] );

		if ( ! empty( $groupby ) ) {
			$groupby = " GROUP BY {$groupby} ";
		}

		$sql = "SELECT DISTINCT $fields FROM {$table_name} $join WHERE 1=1 $where $groupby $orderby $limits";

		$result = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return $result;
	}

	/**
	 * Generates the help tabs.
	 *
	 * @since 3.3.0
	 */
	public function help_tabs() {

		$screen = get_current_screen();

		$screen->set_help_sidebar(
			/* translators: 1: Support link. */
			'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%1$s">WebberZone support site</a>.', 'better-search' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
			/* translators: 1: Forum link. */
			'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%1$s">WordPress.org support forums</a>.', 'better-search' ), esc_url( 'https://wordpress.org/support/plugin/better-search' ) ) . '</p>' .
			'<p>' . sprintf(
				/* translators: 1: Github Issues link, 2: Github page. */
				__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'better-search' ),
				esc_url( 'https://github.com/WebberZone/better-search/issues' ),
				esc_url( 'https://github.com/WebberZone/better-search' )
			) . '</p>'
		);

		$screen->add_help_tab(
			array(
				'id'      => 'bsearch-dashboard',
				'title'   => __( 'Dashboard', 'better-search' ),
				'content' =>
				'<p>' . __( "The Admin Dashboard gives you instant insights into your site's search behaviour. At a glance, you'll see the number of searches performed, along with the top searches for the day, week, month, and even all time. ", 'better-search' ) . '</p>',
			)
		);
	}
}
