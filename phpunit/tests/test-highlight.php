<?php

declare( strict_types=1 );

use WebberZone\Better_Search\Util\Helpers;

class HighlightTest extends WP_UnitTestCase {

	public function test_highlight_cases(): void {
		foreach ( self::highlight_cases() as $case ) {
			$desc              = $case['desc'];
			$content           = $case['content'];
			$terms             = $case['terms'];
			$must_contain      = $case['must_contain'] ?? array();
			$must_not_contain  = $case['must_not_contain'] ?? array();
			$regex_contain     = $case['regex_contain'] ?? array();
			$regex_not_contain = $case['regex_not_contain'] ?? array();

			$this->assertTrue( class_exists( Helpers::class ), $desc . ': Helpers class not loaded.' );
			$result = Helpers::highlight( $content, $terms );

			foreach ( $must_contain as $needle ) {
				$this->assertStringContainsString( $needle, $result, $desc . ': Expected to contain: ' . $needle );
			}

			foreach ( $must_not_contain as $needle ) {
				$this->assertStringNotContainsString( $needle, $result, $desc . ': Expected to NOT contain: ' . $needle );
			}

			foreach ( $regex_contain as $pattern ) {
				$this->assertSame( 1, preg_match( $pattern, $result ), $desc . ': Expected regex match: ' . $pattern );
			}

			foreach ( $regex_not_contain as $pattern ) {
				$this->assertSame( 0, preg_match( $pattern, $result ), $desc . ': Expected regex NOT to match: ' . $pattern );
			}
		}
	}

	private static function highlight_cases(): array {
		return array(
			array(
				'desc'         => 'RTL (Arabic) + Latin mixed.',
				'content'      => '<p>مرحبا بكم في Ajay\'s house اليوم</p>',
				'terms'        => array( 'مرحبا', 'اليوم' ),
				'must_contain' => array(
					'<mark class="bsearch_highlight">مرحبا</mark>',
					'<mark class="bsearch_highlight">اليوم</mark>',
				),
			),
			array(
				'desc'         => 'CJK (Japanese).',
				'content'      => '<p>日本語の文章。東京, 大阪。</p>',
				'terms'        => array( '東京', '大阪' ),
				'must_contain' => array(
					'<mark class="bsearch_highlight">東京</mark>',
					'<mark class="bsearch_highlight">大阪</mark>',
				),
			),
			array(
				'desc'         => 'Emoji terms.',
				'content'      => '<p>Emojis: 🚀 ✨ 🎉 are fun</p>',
				'terms'        => array( '🚀', '🎉' ),
				'must_contain' => array(
					'<mark class="bsearch_highlight">🚀</mark>',
					'<mark class="bsearch_highlight">🎉</mark>',
				),
			),
			array(
				'desc'             => 'Do not touch attributes (alt/title/data-*).',
				'content'          => '<div data-title="Spiderman"><img alt="Spiderman" title="Spiderman" src="#" /> Spiderman</div>',
				'terms'            => array( 'Spiderman' ),
				'must_not_contain' => array(
					'alt="<mark',
					'title="<mark',
					'data-title="<mark',
				),
				'must_contain'     => array( '<mark class="bsearch_highlight">Spiderman</mark>' ),
			),
			array(
				'desc'             => 'Gutenberg block comments should not be modified.',
				'content'          => '<!-- wp:paragraph --><p>Block content Ajay</p><!-- /wp:paragraph -->',
				'terms'            => array( 'Ajay', 'wp:paragraph' ),
				'must_not_contain' => array( '<mark class="bsearch_highlight">wp:paragraph</mark>' ),
				'must_contain'     => array( '<mark class="bsearch_highlight">Ajay</mark>' ),
			),
			array(
				'desc'              => 'Script tag content should not be highlighted.',
				'content'           => '<script>var Spiderman = "Spiderman";</script><p>Spiderman</p>',
				'terms'             => array( 'Spiderman' ),
				'regex_not_contain' => array( '#<script[^>]*>.*?<mark\b.*?</script>#is' ),
				'regex_contain'     => array( '#<p><mark class="bsearch_highlight">Spiderman</mark></p>#' ),
			),
			array(
				'desc'              => 'Style tag content should not be highlighted.',
				'content'           => '<style>.spiderman{content:"Spiderman";}</style><p>Spiderman</p>',
				'terms'             => array( 'Spiderman' ),
				'regex_not_contain' => array( '#<style[^>]*>.*?<mark\b.*?</style>#is' ),
				'regex_contain'     => array( '#<p><mark class="bsearch_highlight">Spiderman</mark></p>#' ),
			),
			array(
				'desc'             => 'Already-highlighted markup should not nest.',
				'content'          => '<p><mark class="bsearch_highlight">Spiderman</mark> Spiderman</p>',
				'terms'            => array( 'Spiderman' ),
				'must_not_contain' => array( '<mark class="bsearch_highlight"><mark' ),
			),
		);
	}
}
