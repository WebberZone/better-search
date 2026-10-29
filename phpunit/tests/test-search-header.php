<?php

declare( strict_types=1 );

class SearchHeaderTest extends WP_UnitTestCase {

	private $original_get;
	private $original_query;

	public function set_up(): void {
		parent::set_up();
		global $wp_query;
		$this->original_get   = $_GET;
		$this->original_query = $wp_query;
		$_GET                = array();
		$wp_query            = new WP_Query();
	}

	public function tear_down(): void {
		global $wp_query;
		$_GET     = $this->original_get;
		$wp_query = $this->original_query;
		parent::tear_down();
	}

	/** @dataProvider summary_cases */
	public function test_header_preserves_summary_and_reading_order( int $found, int $limit, int $paged, int $pages, int $first, int $last, int $bydate ): void {
		$html = get_bsearch_header(
			array(
				'found_posts'   => $found,
				'limit'         => $limit,
				'paged'         => $paged,
				'max_num_pages' => $pages,
				'search_query'  => 'search term',
				'bydate'        => $bydate,
			)
		);
		$xpath = $this->parse_header( $html );

		$this->assertSame( 0, $xpath->query( '//table | //tr | //td | //th | //*[@role="table" or @role="grid"]' )->length );
		$parts = $xpath->query( '//div[@class="bsearch_nav"]/div/div' );
		$this->assertSame( 4, $parts->length );
		$this->assertSame( "Results $first - $last of $found", trim( $parts->item( 0 )->textContent ) );
		$this->assertSame( 'Page ' . max( 1, $paged ) . ' of ' . max( 1, $pages ), trim( $parts->item( 1 )->textContent ) );
		$this->assertSame( $bydate ? 'Sorted by: Date | Sort by: Relevance' : 'Sorted by: Relevance | Sort by: Date', trim( $parts->item( 2 )->textContent ) );
		$this->assertStringStartsWith( 'Results per-page:', trim( $parts->item( 3 )->textContent ) );
		$this->assertSame( 5, $xpath->query( '//strong' )->length );
		$this->assertSame( 1, $xpath->query( '//div[@class="bsearch_nav_row1"]' )->length );
		$this->assertSame( 1, $xpath->query( '//div[@class="bsearch_nav_row2"]' )->length );
	}

	public static function summary_cases(): array {
		return array(
			'first page'      => array( 12, 10, 1, 2, 1, 10, 0 ),
			'last page'       => array( 12, 10, 2, 2, 11, 12, 1 ),
			'single result'   => array( 1, 10, 1, 1, 1, 1, 0 ),
			'exact page'      => array( 20, 10, 2, 2, 11, 20, 1 ),
			'all results'     => array( 55, 55, 1, 1, 1, 55, 0 ),
			'default page'    => array( 12, 10, 0, 2, 1, 10, 0 ),
			'large resultset' => array( 12345, 50, 247, 247, 12301, 12345, 1 ),
		);
	}

	/** @dataProvider sort_cases */
	public function test_control_links_preserve_search_and_selected_options( int $bydate ): void {
		$_GET = array( 'bydate' => $bydate, 'limit' => 20, 'post_types' => 'page' );
		$term = 'café & "search" + 東京';
		$html = get_bsearch_header(
			array(
				'found_posts'   => 55,
				'max_num_pages' => 3,
				'paged'         => 2,
				'search_query'  => $term,
			)
		);
		$xpath = $this->parse_header( $html );
		$links = $xpath->query( '//a' );
		$this->assertSame( 5, $links->length );

		foreach ( $links as $index => $link ) {
			parse_str( wp_parse_url( $link->getAttribute( 'href' ), PHP_URL_QUERY ), $query );
			$this->assertSame( $term, $query['s'] );
			$this->assertSame( 'page', $query['post_types'] );
			$this->assertSame( (string) ( 0 === $index ? 1 - $bydate : $bydate ), $query['bydate'] );
			$this->assertSame( (string) array( 20, 10, 20, 50, 55 )[ $index ], $query['limit'] );
			$this->assertArrayNotHasKey( 'paged', $query, 'Changing sort or page size must reset pagination.' );
		}
		$this->assertSame( array( $bydate ? 'Relevance' : 'Date', '10', '20', '50', 'All' ), array_map( static function ( $link ) { return $link->textContent; }, iterator_to_array( $links ) ) );
	}

	public static function sort_cases(): array {
		return array( 'relevance' => array( 0 ), 'date' => array( 1 ) );
	}

	public function test_header_uses_main_query_defaults(): void {
		global $wp_query;
		$wp_query->found_posts   = 12;
		$wp_query->max_num_pages = 2;
		$wp_query->set( 'paged', 2 );
		$wp_query->set( 's', 'default query' );
		$_GET['limit'] = 10;
		$xpath         = $this->parse_header( get_bsearch_header() );

		$this->assertSame( 'Results 11 - 12 of 12', trim( $xpath->query( '//div[@class="bsearch_nav_results"]' )->item( 0 )->textContent ) );
		$this->assertSame( 'Page 2 of 2', trim( $xpath->query( '//div[@class="bsearch_nav_pages"]' )->item( 0 )->textContent ) );
	}

	public function test_limit_filters_remain_supported(): void {
		$seen_args = null;
		$steps     = static function ( $defaults, $args ) use ( &$seen_args ) {
			$seen_args = $args;
			return array( 5, 15, 500 );
		};
		add_filter( 'bsearch_header_limit_steps', $steps, 10, 2 );
		add_filter( 'bsearch_header_show_all', '__return_false' );
		try {
			$xpath = $this->parse_header( get_bsearch_header( array( 'found_posts' => 55, 'search_query' => 'custom steps' ) ) );
			$links = $xpath->query( '//div[@class="bsearch_nav_limits"]/a' );
			$this->assertSame( array( '5', '15' ), array_map( static function ( $link ) { return $link->textContent; }, iterator_to_array( $links ) ) );
			$this->assertSame( 55, $seen_args['found_posts'] );
			$this->assertSame( rawurlencode( 'custom steps' ), $seen_args['search_query'] );
		} finally {
			remove_filter( 'bsearch_header_limit_steps', $steps );
			remove_filter( 'bsearch_header_show_all', '__return_false' );
		}
	}

	public function test_output_filters_and_echo_wrapper_remain_supported(): void {
		$args   = array( 'found_posts' => 12, 'search_query' => 'filtered' );
		$getter = static function ( $html, $received_args ) {
			return $html . '<p>' . esc_html( $received_args['search_query'] ) . '</p>';
		};
		$echoer = static function ( $html, $received_args ) {
			return '<section data-count="' . $received_args['found_posts'] . '">' . $html . '</section>';
		};
		add_filter( 'get_bsearch_header', $getter, 10, 2 );
		add_filter( 'the_bsearch_header', $echoer, 10, 2 );
		try {
			$expected = '<section data-count="12">' . get_bsearch_header( $args ) . '</section>';
			$this->expectOutputString( $expected );
			the_bsearch_header( $args );
			$this->assertStringContainsString( '<p>filtered</p>', $expected );
		} finally {
			remove_filter( 'get_bsearch_header', $getter );
			remove_filter( 'the_bsearch_header', $echoer );
		}
	}

	private function parse_header( string $html ): DOMXPath {
		$document = new DOMDocument();
		$this->assertTrue( $document->loadHTML( '<!doctype html><html><head><meta charset="utf-8"><title>Search</title></head><body>' . $html . '</body></html>' ) );
		return new DOMXPath( $document );
	}
}
