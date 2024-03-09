<?php
/**
 * Better Search Display statistics table.
 *
 * @package   Better_Search
 */

namespace WebberZone\Better_Search\Admin;

use WebberZone\Better_Search\Util\Helpers;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Better_Search_Statistics_Table class.
 *
 * Display the popular search terms in a tabular format.
 *
 * @since 3.3.0
 */
class Statistics_Table extends \WP_List_Table {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'popular_search', 'better-search' ), // Singular name of the listed records.
				'plural'   => __( 'popular_searches', 'better-search' ), // plural name of the listed records.
			)
		);
	}

	/**
	 * Retrieve the Better Search search terms
	 *
	 * @param int   $per_page Posts per page.
	 * @param int   $page_number Page number.
	 * @param array $args Array of arguments.
	 *
	 * @return  array   Array of popular search terms
	 */
	public function get_popular_searches( $per_page = 20, $page_number = 1, $args = null ) {

		global $wpdb;

		$from_date = isset( $args['search-date-filter-from'] ) ? $args['search-date-filter-from'] : gmdate( 'd M Y', strtotime( '-1 month' ) );
		$from_date = gmdate( 'Y-m-d', strtotime( $from_date ) );
		$to_date   = isset( $args['search-date-filter-to'] ) ? $args['search-date-filter-to'] : current_time( 'd M Y' );
		$to_date   = gmdate( 'Y-m-d', strtotime( $to_date ) );

		/* Start creating the SQL */
		$table_name_daily = $wpdb->prefix . 'bsearch_daily AS bsd';
		$table_name       = $wpdb->prefix . 'bsearch AS bst';

		// Fields to return.
		$fields[] = 'bst.searchvar as title';
		$fields[] = 'bst.cntaccess as total_count';
		$fields[] = 'SUM(bsd.cntaccess) as daily_count';

		$fields = implode( ', ', $fields );

		// Create the JOIN clause.
		$join = $wpdb->prepare(
			" LEFT JOIN (
			SELECT * FROM {$table_name_daily} " . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'WHERE DATE(bsd.dp_date) >= DATE(%s) AND DATE(bsd.dp_date) <= DATE(%s)
			) AS bsd
			ON bst.searchvar=bsd.searchvar
			',
			$from_date,
			$to_date
		);

		// Create the base WHERE clause.
		$where = '';

		/* If search argument is set, do a search for it. */
		if ( ! empty( $args['search'] ) ) {
			$where .= $wpdb->prepare( ' AND bst.searchvar LIKE %s ', '%' . $wpdb->esc_like( $args['search'] ) . '%' );
		}

		// Create the base GROUP BY clause.
		$groupby = ' title ';

		// Create the ORDER BY clause.
		$orderby = '';
		if ( ! empty( $_REQUEST['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( ! empty( $args['orderby'] ) ) {
			$orderby = $args['orderby'];
		}

		if ( $orderby ) {
			if ( ! in_array( $orderby, array( 'title', 'daily_count', 'total_count' ) ) ) { //phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				$orderby = ' total_count ';
			}

			$order = '';
			if ( ! empty( $_REQUEST['order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} elseif ( ! empty( $args['order'] ) ) {
				$order = $args['order'];
			}

			if ( $order && in_array( $order, array( 'asc', 'ASC', 'desc', 'DESC' ), true ) ) {
				$orderby .= " {$order}";
			} else {
				$orderby .= ' DESC';
			}
		} else {
			$orderby = ' total_count DESC ';
		}

		// Create the base LIMITS clause.
		$limits = $wpdb->prepare( ' LIMIT %d, %d ', ( $page_number - 1 ) * $per_page, $per_page );

		$groupby = " GROUP BY {$groupby} ";
		$orderby = " ORDER BY {$orderby} ";

		$sql = "SELECT $fields FROM {$table_name} $join WHERE 1=1 $where $groupby $orderby $limits";

		$result = $wpdb->get_results( $sql, 'ARRAY_A' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return $result;
	}


	/**
	 * Delete search result.
	 *
	 * @param string $id Search result.
	 */
	public static function delete_search_entry( $id ) {
		global $wpdb;

		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"{$wpdb->prefix}bsearch",
			array(
				'searchvar' => $id,
			),
			array( '%s' )
		);

		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"{$wpdb->prefix}bsearch_daily",
			array(
				'searchvar' => $id,
			),
			array( '%s' )
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @param  array $args Array of arguments.
	 * @return int   Number of records.
	 */
	public function record_count( $args = null ) {

		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}bsearch as bst";

		if ( isset( $args['search'] ) ) {
			$sql .= $wpdb->prepare( ' WHERE bst.searchvar LIKE %s ', '%' . $wpdb->esc_like( $args['search'] ) . '%' );
		}

		return intval( $wpdb->get_var( $sql ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Text displayed when no post data is available
	 */
	public function no_items() {
		esc_html_e( 'No popular searches available.', 'better-search' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array  $item Current item.
	 * @param string $column_name Column name.
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'total_count':
			case 'daily_count':
				return Helpers::number_format_i18n( absint( $item[ $column_name ] ) );
			default:
				// Show the whole array for troubleshooting purposes.
				return print_r( $item, true );  //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'search',
			esc_attr( $item['title'] )
		);
	}

	/**
	 * Render the title column.
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	public function column_title( $item ) {

		$delete_nonce = wp_create_nonce( 'bsearch_delete_entry' );
		$page         = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$actions = array(
			'view'   => sprintf(
				'<a href="%s" target="_blank">' . __( 'View', 'better-search' ) . '</a>',
				home_url() . '/?s=' . esc_attr( $item['title'] )
			),
			'delete' => sprintf(
				'<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">' . __( 'Delete', 'better-search' ) . '</a>',
				esc_attr( $page ),
				'delete',
				esc_attr( $item['title'] ),
				$delete_nonce
			),
		);

		// Return the title contents.
		return sprintf(
			'<a href="%2$s">%1$s</a>%3$s',
			esc_attr( $item['title'] ),
			home_url() . '/?s=' . esc_attr( $item['title'] ),
			$this->row_actions( $actions )
		);
	}


	/**
	 * Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'title'       => __( 'Search term', 'better-search' ),
			'total_count' => __( 'Total searches', 'better-search' ),
			'daily_count' => __( 'Daily searches', 'better-search' ),
		);

		/**
		 * Filter the columns displayed in the Posts list table.
		 *
		 * @since 2.4.0
		 *
		 * @param   array   $columns    An array of column names.
		 */
		return apply_filters( 'manage_pop_searches_columns', $columns );
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'title'       => array( 'title', false ),
			'total_count' => array( 'total_count', false ),
			'daily_count' => array( 'daily_count', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete search term', 'better-search' ),
		);
		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$args = array();

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page( 'pop_searches_per_page', 20 );

		$current_page = $this->get_pagenum();

		// If this is a search?
		if ( isset( $_REQUEST['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['search'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// If this is a post date filter?
		if ( isset( $_REQUEST['search-date-filter-to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['search-date-filter-to'] = sanitize_text_field( wp_unslash( $_REQUEST['search-date-filter-to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( isset( $_REQUEST['search-date-filter-from'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['search-date-filter-from'] = sanitize_text_field( wp_unslash( $_REQUEST['search-date-filter-from'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$this->items = self::get_popular_searches( $per_page, $current_page, $args );
		$total_items = (int) self::record_count( $args );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // WE have to calculate the total number of items.
				'per_page'    => $per_page, // WE have to determine how many items to show on a page.
				'total_pages' => intval( ceil( $total_items / $per_page ) ), // WE have to calculate the total number of pages.
			)
		);
	}

	/**
	 * Handles any bulk actions
	 */
	public function process_bulk_action() {

		// Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';

			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'bsearch_delete_entry' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				self::delete_search_entry( $id );
			} else {
				die( esc_html__( 'Are you sure you want to do this', 'better-search' ) );
			}
		}

		// If the delete bulk action is triggered.
		if ( ( isset( $_REQUEST['action'] ) && 'bulk-delete' === $_REQUEST['action'] )
			|| ( isset( $_REQUEST['action2'] ) && 'bulk-delete' === $_REQUEST['action2'] )
		) {
			$delete_ids = isset( $_REQUEST['search'] ) ? array_map( 'wp_kses_post', (array) wp_unslash( $_REQUEST['search'] ) ) : array();

			// Loop over the array of record IDs and delete them.
			foreach ( $delete_ids as $id ) {
				self::delete_search_entry( $id );
			}
		}
	}

	/**
	 * Adds extra navigation elements to the table.
	 *
	 * @param string $which Which part of the table are we.
	 */
	public function extra_tablenav( $which ) {
		?>
		<div class="alignleft actions">
		<?php
		if ( 'top' === $which ) {
			ob_start();

			// Add date selector.
			$to_date   = current_time( 'd M Y' );
			$from_date = gmdate( 'd M Y', strtotime( '-1 month' ) );

			$post_date_from = isset( $_REQUEST['search-date-filter-from'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search-date-filter-from'] ) ) : $from_date; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<input type="text" id="datepicker-from" name="search-date-filter-from" value="' . esc_attr( $post_date_from ) . '" size="11" />';

			$post_date_to = isset( $_REQUEST['search-date-filter-to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search-date-filter-to'] ) ) : $to_date; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<input type="text" id="datepicker-to" name="search-date-filter-to" value="' . esc_attr( $post_date_to ) . '" size="11" />';

			$output = ob_get_clean();

			if ( ! empty( $output ) ) {
				echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				submit_button( __( 'Filter' ), '', 'filter_action', false, array( 'id' => 'better-search-query-submit' ) );
			}
		}
		?>
		</div>
		<?php
	}
}

