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

	public static function sort_cases(): array {
		return array( 'relevance' => array( 0 ), 'date' => array( 1 ) );
	}

	private function parse_header( string $html ): DOMXPath {
		$document = new DOMDocument();
		$this->assertTrue( $document->loadHTML( '<!doctype html><html><head><meta charset="utf-8"><title>Search</title></head><body>' . $html . '</body></html>' ) );
		return new DOMXPath( $document );
	}
}
