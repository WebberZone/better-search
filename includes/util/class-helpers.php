<?php
/**
 * Helper functions
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Util;

use WP_Query;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Helpers {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
	}

	/**
	 * Function to delete all rows in the posts table.
	 *
	 * @since 3.3.0
	 *
	 * @param bool $daily  Daily flag.
	 */
	public static function get_bsearch_table( $daily = false ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'bsearch';
		if ( $daily ) {
			$table_name .= '_daily';
		}
		return $table_name;
	}

	/**
	 * Clean search string from XSS exploits.
	 *
	 * @since 3.3.0
	 *
	 * @param   string $val    Potentially unclean string.
	 * @return  string  Cleaned string if successful or empty string on use of banned word
	 */
	public static function clean_terms( $val ) {
		global $bsearch_error;

		// Instantiate WP_Error class.
		$bsearch_error = new \WP_Error();

		$val = rawurldecode( $val );

		// Block SQL injection attempts by detecting common patterns.
		$sql_injection_patterns = array(
			'/[\\s\\r\\n\\)]+OR[\\s\\r\\n\\(]+/i',          // Detect OR statements.
			'/[\\s\\r\\n\\)]+AND[\\s\\r\\n\\(]+/i',         // Detect AND statements.
			'/\\bUNION\\b.*\\bSELECT\\b/i',              // UNION SELECT.
			'/[\\\'\\"\\-\\#]\\s*OR\\s+[\\d\\w]+=[\\d\\w]+/i', // Quoted OR equality.
			'/\\-\\-/',                              // SQL comments.
			'/;\\s*(?:SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC)/i', // Multiple statements - only match actual SQL keywords.
			'/\\bDROP\\b/i',                         // DROP statements.
			'/\\bEXEC\\b/i',                          // EXEC statements.
			'/SLEEP\\s*\\(/i',                       // SLEEP injection.
			'/BENCHMARK\\s*\\(/i',                   // BENCHMARK injection.
		);

		// Check each pattern against the search query.
		foreach ( $sql_injection_patterns as $pattern ) {
			if ( preg_match( $pattern, $val ) ) {
				$bsearch_error->add(
					'bsearch_sql_injection',
					__( 'Your search query appears to be malicious. Please try a different search.', 'better-search' )
				);

				/**
				 * Fires when a SQL injection attempt is detected.
				 *
				 * @since 4.0.6
				 *
				 * @param string $val The search query that triggered the detection.
				 * @param string $pattern The pattern that matched.
				 */
				do_action( 'bsearch_sql_injection_detected', $val, $pattern );

				return '';
			}
		}

		$badwords = array_map( 'trim', explode( ',', bsearch_get_option( 'badwords' ) ) );

		$censor_char = '';

		/**
		 * Allow the censored character to be replaced.
		 *
		 * @since   2.1.0
		 *
		 * @param   string  $censor_char Censored character
		 * @param   string  $val        Raw search string
		 */
		$censor_char = apply_filters( 'bsearch_censor_char', $censor_char, $val );

		$val_censored = self::censor_string( $val, $badwords, $censor_char, bsearch_get_option( 'banned_whole_words' ) );  // No more bad words.

		if ( $val_censored['clean'] !== $val_censored['orig'] ) {
			$bsearch_error->add( 'bsearch_banned', __( 'Your search query consists banned words. Please remove these and try again.', 'better-search' ) );
			if ( bsearch_get_option( 'banned_stop_search' ) ) {
				return '';
			}
		}

		$val = $val_censored['clean'];
		$val = preg_replace( '!\s+!', ' ', $val ); // Replace multiple spaces with a single.
		$val = preg_replace( '!\++!', '+', $val ); // Replace multiple + with a single.
		$val = rtrim( $val, '+' ); // Remove any trailing + signs.
		$val = sanitize_text_field( $val );

		/**
		 * Clean search string from XSS exploits.
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $val    Cleaned string
		 */
		return apply_filters( 'bsearch_clean_terms', $val );
	}


	/**
	 * Generates a random string.
	 *
	 * @since 3.3.0
	 *
	 * @param   string $chars  Chars that can be used.
	 * @param   int    $len    Length of the output string.
	 * @return  string  Random string
	 */
	public static function rand_censor( $chars, $len ) {

		$output = '';
		for ( $i = 0; $i < $len; $i++ ) {
			$output .= substr( $chars, wp_rand( 0, strlen( $chars ) - 1 ), 1 );
		}

		return $output;
	}


	/**
	 * Apply censorship to $message, replacing $badwords with $censor_char.
	 *
	 * @since 3.3.0
	 *
	 * @param  string $message      String to be censored.
	 * @param  array  $badwords    Array of badwords.
	 * @param  string $censor_char String which replaces bad words. If it's more than 1-char long, a random string will be generated from these chars. Default: '*'.
	 * @param  bool   $whole_words Filter whole worlds only.
	 * @return array  Array containing the original string at `orig` and the censored string at `clean`.
	 */
	public static function censor_string( $message, $badwords, $censor_char = '*', $whole_words = false ) {

		$replacement = array();

		$leet_replace      = array();
		$leet_replace['a'] = '(a|a\.|a\-|4|@|Á|á|À|Â|à|Â|â|Ä|ä|Ã|ã|Å|å|α|Δ|Λ|λ)';
		$leet_replace['b'] = '(b|b\.|b\-|8|\|3|ß|Β|β)';
		$leet_replace['c'] = '(c|c\.|c\-|Ç|ç|¢|€|<|\(|{|©)';
		$leet_replace['d'] = '(d|d\.|d\-|&part;|\|\)|Þ|þ|Ð|ð)';
		$leet_replace['e'] = '(e|e\.|e\-|3|€|È|è|É|é|Ê|ê|∑)';
		$leet_replace['f'] = '(f|f\.|f\-|ƒ)';
		$leet_replace['g'] = '(g|g\.|g\-|6|9)';
		$leet_replace['h'] = '(h|h\.|h\-|Η)';
		$leet_replace['i'] = '(i|i\.|i\-|!|\||\]\[|]|1|∫|Ì|Í|Î|Ï|ì|í|î|ï)';
		$leet_replace['j'] = '(j|j\.|j\-)';
		$leet_replace['k'] = '(k|k\.|k\-|Κ|κ)';
		$leet_replace['l'] = '(l|1\.|l\-|!|\||\]\[|]|£|∫|Ì|Í|Î|Ï)';
		$leet_replace['m'] = '(m|m\.|m\-)';
		$leet_replace['n'] = '(n|n\.|n\-|η|Ν|Π)';
		$leet_replace['o'] = '(o|o\.|o\-|0|Ο|ο|Φ|¤|°|ø)';
		$leet_replace['p'] = '(p|p\.|p\-|ρ|Ρ|¶|þ)';
		$leet_replace['q'] = '(q|q\.|q\-)';
		$leet_replace['r'] = '(r|r\.|r\-|®)';
		$leet_replace['s'] = '(s|s\.|s\-|5|\$|§)';
		$leet_replace['t'] = '(t|t\.|t\-|Τ|τ)';
		$leet_replace['u'] = '(u|u\.|u\-|υ|µ)';
		$leet_replace['v'] = '(v|v\.|v\-|υ|ν)';
		$leet_replace['w'] = '(w|w\.|w\-|ω|ψ|Ψ)';
		$leet_replace['x'] = '(x|x\.|x\-|Χ|χ)';
		$leet_replace['y'] = '(y|y\.|y\-|¥|γ|ÿ|ý|Ÿ|Ý)';
		$leet_replace['z'] = '(z|z\.|z\-|Ζ)';

		// is $censor_char a single char?
		$is_one_char = ( strlen( $censor_char ) === 1 );

		// Add boundary filter for whole words.
		if ( $whole_words ) {
			$boundary = '\b';
		} else {
			$boundary = '';
		}

		// Count the bad words.
		$no_of_badwords = count( $badwords );

		for ( $x = 0; $x < $no_of_badwords; $x++ ) {

			$replacement[ $x ] = $is_one_char
			? str_repeat( $censor_char, strlen( $badwords[ $x ] ) )
			: self::rand_censor( $censor_char, strlen( $badwords[ $x ] ) );

			$badwords[ $x ] = '/' . $boundary . str_ireplace( array_keys( $leet_replace ), array_values( $leet_replace ), $badwords[ $x ] ) . $boundary . '/i';
		}

		$newstring          = array();
		$newstring['orig']  = $message;
		$newstring['clean'] = preg_replace( $badwords, $replacement, $newstring['orig'] );

		return $newstring;
	}


	/**
	 * Convert Hexadecimal colour code to RGB.
	 *
	 * @since 3.3.0
	 *
	 * @param   string $color  Hexadecimal colour.
	 * @return  array|bool   Array containing RGB colour code or false if error.
	 */
	public static function html2rgb( $color ) {

		if ( '#' === $color[0] ) {
			$color = substr( $color, 1 );
		}

		if ( 6 === (int) strlen( $color ) ) {
			list( $r, $g, $b ) = array(
				$color[0] . $color[1],
				$color[2] . $color[3],
				$color[4] . $color[5],
			);
		} elseif ( 3 === (int) strlen( $color ) ) {
			list( $r, $g, $b ) = array(
				$color[0] . $color[0],
				$color[1] . $color[1],
				$color[2] . $color[2],
			);
		} else {
			return false;
		}

		$r = hexdec( $r );
		$g = hexdec( $g );
		$b = hexdec( $b );

		return array( $r, $g, $b );
	}


	/**
	 * Function to convert RGB color code to Hexadecimal.
	 *
	 * @since   1.3.4
	 *
	 * @param   int|string|array $r  Red colour or array of RGB values.
	 * @param   int|string       $g  (default: -1) Green colour.
	 * @param   int|string       $b  (default: -1) Blue colour.
	 * @param   bool             $padhash Pad # when returning.
	 * @return  string              HEX color code
	 */
	public static function rgb2html( $r, $g = -1, $b = -1, $padhash = false ) {

		if ( is_array( $r ) && 3 === count( $r ) ) {    // If $r is an array, extract the RGB values.
			list( $r, $g, $b ) = $r;
		}

		$r = intval( $r );
		$g = intval( $g );
		$b = intval( $b );

		$r = dechex( $r < 0 ? 0 : ( $r > 255 ? 255 : $r ) );
		$g = dechex( $g < 0 ? 0 : ( $g > 255 ? 255 : $g ) );
		$b = dechex( $b < 0 ? 0 : ( $b > 255 ? 255 : $b ) );

		$color  = ( strlen( $r ) < 2 ? '0' : '' ) . $r;
		$color .= ( strlen( $g ) < 2 ? '0' : '' ) . $g;
		$color .= ( strlen( $b ) < 2 ? '0' : '' ) . $b;

		if ( $padhash ) {
			$color = '#' . $color;
		}

		return $color;
	}

	/**
	 * Retrieve the from date for the query
	 *
	 * @since 3.3.0
	 *
	 * @param string $time        A date/time string.
	 * @param int    $daily_range Daily range.
	 * @return string From date
	 */
	public static function get_from_date( $time = null, $daily_range = null ) {

		$current_time = isset( $time ) ? strtotime( $time ) : strtotime( current_time( 'mysql' ) );
		$daily_range  = isset( $daily_range ) ? $daily_range : bsearch_get_option( 'daily_range' );

		$from_date = $current_time - ( max( 0, ( $daily_range - 1 ) ) * DAY_IN_SECONDS );
		$from_date = gmdate( 'Y-m-d', $from_date );

		/**
		 * Retrieve the from date for the query
		 *
		 * @since 2.4.0
		 *
		 * @param string $from_date   From date.
		 * @param string $time        A date/time string.
		 * @param int    $daily_range Daily range.
		 */
		return apply_filters( 'bsearch_get_from_date', $from_date, $time, $daily_range );
	}


	/**
	 * Convert float number to format based on the locale if number_format_count is true.
	 *
	 * @since 3.3.0
	 *
	 * @param float $number   The number to convert based on locale.
	 * @param int   $decimals Optional. Precision of the number of decimal places. Default 0.
	 * @return string Converted number in string format.
	 */
	public static function number_format_i18n( $number, $decimals = 0 ) {

		$formatted = $number;

		if ( bsearch_get_option( 'number_format_count' ) ) {
			$formatted = number_format_i18n( $number, $decimals );
		}

		/**
		 * Filters the number formatted based on the locale.
		 *
		 * @since 2.4.0
		 *
		 * @param string $formatted Converted number in string format.
		 * @param float  $number    The number to convert based on locale.
		 * @param int    $decimals  Precision of the number of decimal places.
		 */
		return apply_filters( 'bsearch_number_format_i18n', $formatted, $number, $decimals );
	}

	/**
	 * Convert a string to CSV.
	 *
	 * @since 3.3.0
	 *
	 * @param array  $input_array Input string.
	 * @param string $delimiter Delimiter.
	 * @param string $enclosure Enclosure.
	 * @param string $terminator Terminating string.
	 * @return string CSV string.
	 */
	public static function str_putcsv( $input_array, $delimiter = ',', $enclosure = '"', $terminator = "\n" ) {
		// First convert associative array to numeric indexed array.
		$work_array = array();
		foreach ( $input_array as $key => $value ) {
			$work_array[] = $value;
		}

		$output     = '';
		$array_size = count( $work_array );

		for ( $i = 0; $i < $array_size; $i++ ) {
			// Nested array, process nest item.
			if ( is_array( $work_array[ $i ] ) ) {
				$output .= self::str_putcsv( $work_array[ $i ], $delimiter, $enclosure, $terminator );
			} else {
				switch ( gettype( $work_array[ $i ] ) ) {
					// Manually set some strings.
					case 'NULL':
						$sp_format = '';
						break;
					case 'boolean':
						$sp_format = ( true === $work_array[ $i ] ) ? 'true' : 'false';
						break;
					// Make sure sprintf has a good datatype to work with.
					case 'integer':
						$sp_format = '%i';
						break;
					case 'double':
						$sp_format = '%0.2f';
						break;
					case 'string':
						$sp_format        = '%s';
						$work_array[ $i ] = str_replace( "$enclosure", "$enclosure$enclosure", $work_array[ $i ] );
						break;
					// Unknown or invalid items for a csv - note: the datatype of array is already handled above, assuming the data is nested.
					case 'object':
					case 'resource':
					default:
						$sp_format = '';
						break;
				}
				$output .= sprintf( '%2$s' . $sp_format . '%2$s', $work_array[ $i ], $enclosure );
				$output .= ( $i < ( $array_size - 1 ) ) ? $delimiter : $terminator;
			}
		}

		return $output;
	}

	/**
	 * Get the link to Better Search homepage.
	 *
	 * @since 3.3.0
	 *
	 * @return string HTML markup.
	 */
	public static function get_credit_link() {

		$output = '<div class="bsearch_credit" style="text-align:center;border-top:1px dotted #000;display:block;margin-top:5px;"><small>';

		/* translators: 1: Opening a tag and Better Search, 2: Closing a tag. */
		$output .= sprintf( __( 'Powered by %1$s plugin%2$s', 'better-search' ), '<a href="https://webberzone.com/plugins/better-search/" rel="nofollow">Better Search', '</a></small></div>' );

		return $output;
	}


	/**
	 * Highlights search terms in HTML content without affecting tags or attributes.
	 *
	 * @since 3.3.0
	 *
	 * Designed for WordPress post content. Not suitable for arbitrary HTML,
	 * REST API fragments, or content with inline scripts containing closing tags.
	 *
	 * @param string       $content The HTML content to process.
	 * @param string|array $terms   Single term, phrase, or array of terms/phrases to highlight.
	 *
	 * @return string The content with highlighted search terms.
	 */
	public static function highlight( $content, $terms ) {
		// Bail early if content or terms are empty.
		if ( empty( $content ) || empty( $terms ) ) {
			return $content;
		}

		// Convert terms to array if it's a string.
		if ( ! is_array( $terms ) ) {
			$terms = array( $terms );
		}

		// Clean quotes from terms and trim whitespace.
		$terms = array_map(
			function ( $term ) {
				// Remove only surrounding quotes, preserve embedded ones.
				return trim( $term, " \t\n\r\0\x0B\"'" );
			},
			$terms
		);

		// Remove empty terms.
		$terms = array_filter( $terms );

		if ( empty( $terms ) ) {
			return $content;
		}

		// Cap term count to prevent regex explosion.
		$max_terms = apply_filters( 'bsearch_highlight_max_terms', 50 );
		$terms     = array_slice( $terms, 0, $max_terms );

		// Escape special regex characters in each term.
		$escaped_terms = array_map( 'preg_quote', $terms, array_fill( 0, count( $terms ), '/' ) );

		// Sort by length (longest first) to match longer phrases before shorter ones.
		$length_func = function_exists( 'mb_strlen' ) ? 'mb_strlen' : 'strlen';
		usort(
			$escaped_terms,
			function ( $a, $b ) use ( $length_func ) {
				return $length_func( $b ) - $length_func( $a );
			}
		);

		// Allow filtering whether to use word boundaries.
		$use_boundaries = apply_filters( 'bsearch_highlight_use_boundaries', true );

		// Build the regex pattern once.
		if ( $use_boundaries ) {
			// Captures prefix separator to avoid variable-length lookbehind issues.
			$pattern = '/(^|[\s\p{P}\p{Z}>])(' . implode( '|', $escaped_terms ) . ')(?=[\s\p{P}\p{Z}]|$|<)/iu';
		} else {
			// No boundaries for CJK and emoji support.
			$pattern = '/(' . implode( '|', $escaped_terms ) . ')/iu';
		}

		// Early exit if no terms match in content (performance optimization).
		if ( ! preg_match( $pattern, $content ) ) {
			return $content;
		}

		// Split content into tags (including script/style) and text.
		// This pattern captures complete tags including those with newlines.
		$parts = preg_split( '/(<(?:script|style)[^>]*>.*?<\/(?:script|style)>|<[^>]+>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE );

		// Get highlight tag and sanitize.
		$tag          = apply_filters( 'bsearch_highlight_tag', 'mark' );
		$allowed_tags = array( 'mark', 'span', 'strong', 'em' );
		$tag          = in_array( $tag, $allowed_tags, true ) ? $tag : 'mark';

		// Get highlight class and sanitize.
		$class = apply_filters( 'bsearch_highlight_class', 'bsearch_highlight' );
		$class = sanitize_html_class( $class );

		$in_highlight = false;

		// Process only text segments, leave HTML tags and scripts untouched.
		foreach ( $parts as $index => $part ) {
			// Skip empty parts.
			if ( empty( $part ) ) {
				continue;
			}

			$part_ltrimmed = ltrim( $part );
			if ( '' === $part_ltrimmed ) {
				continue;
			}

			// Check if this is an HTML tag or script/style block (allow leading whitespace/newlines).
			if ( '<' === $part_ltrimmed[0] ) {
				// Track whether we are inside an existing highlight tag to avoid nesting.
				if ( preg_match( '#^<\s*' . preg_quote( $tag, '#' ) . '\b[^>]*\bclass\s*=\s*(["\"])(.*?)\1#i', $part_ltrimmed, $matches ) ) {
					$classes = $matches[2];
					if ( preg_match( '#(^|\s)' . preg_quote( $class, '#' ) . '(\s|$)#', $classes ) ) {
						$in_highlight = true;
					}
				} elseif ( preg_match( '#^<\s*/\s*' . preg_quote( $tag, '#' ) . '\s*>#i', $part_ltrimmed ) ) {
					$in_highlight = false;
				}

				continue;
			}

			// Skip parts that are inside an existing highlight wrapper.
			if ( $in_highlight ) {
				continue;
			}

			// Defensive: if a non-tag part still contains '<', it may be mixed/malformed markup.
			// Never highlight in such a part to avoid corrupting attributes.
			if ( false !== strpos( $part, '<' ) ) {
				continue;
			}

			// Replace matches in text content only.
			if ( $use_boundaries ) {
				// $1 preserves the boundary character, $2 wraps the actual term.
				$parts[ $index ] = preg_replace( $pattern, '$1<' . $tag . ' class="' . $class . '">$2</' . $tag . '>', $part );
			} else {
				// $1 wraps the term directly (no boundary capture).
				$parts[ $index ] = preg_replace( $pattern, '<' . $tag . ' class="' . $class . '">$1</' . $tag . '>', $part );
			}
		}

		return implode( '', $parts );
	}

	/**
	 * Function to convert the mySQL score to percentage.
	 *
	 * @since 3.3.0
	 *
	 * @param  float $score    Score for the search result.
	 * @param  float $topscore Score for the most relevant search result.
	 * @return string Score converted to percentage
	 */
	public static function score2percent( $score, $topscore ) {
		$output = '';

		if ( $score > 0 && $topscore > 0 ) {
			$score  = $score * 100 / $topscore;
			$output = self::number_format_i18n( $score, 0 ) . '%';
		}

		/**
		 * Filter search result score
		 *
		 * @since 3.0.0
		 *
		 * @param  string $output Score converted to percentage.
		 * @param  float $score    Score for the search result.
		 * @param  float $topscore Score for the most relevant search result.
		 */
		return apply_filters( 'bsearch_score2percent', $output, $score, $topscore );
	}


	/**
	 * Find the locations of each of the words within the text.
	 *
	 * @since 3.3.0
	 *
	 * @param array  $words     Array of words whose location needs to be extracted.
	 * @param string $fulltext  Text to search the words in.
	 * @return array
	 */
	public static function extract_locations( $words, $fulltext ) {
		$locations = array();
		if ( ! is_array( $words ) ) {
			$words = array( $words );
		}

		if (
		// Check for empty search query to avoid infinite loop.
		! ( 1 === count( $words ) && '' === $words[0] )
		) {
			foreach ( $words as $word ) {
				$wordlen = strlen( $word );
				if ( $wordlen < 2 ) {
					continue;
				}
				$loc = stripos( $fulltext, $word );
				while ( false !== $loc ) {
					$locations[] = $loc;
					$loc         = stripos( $fulltext, $word, $loc + $wordlen );
				}
			}
		}
		$locations = array_unique( $locations );

		// If no words were found, show beginning of the fulltext.
		if ( empty( $locations ) ) {
			$locations[0] = 0;
		}

		sort( $locations );
		return $locations;
	}

	/**
	 * Extract the start position of the relevant portion to display.
	 *
	 * This is done by looping over each match and finding the smallest distance between two found
	 * strings. The idea being that the closer the terms are the better match the snippet would be.
	 * When checking for matches we only change the location if there is a better match.
	 * The only exception is where we have only two matches in which case we just take the
	 * first as will be equally distant.
	 *
	 * @since 3.3.0
	 *
	 * @param array $locations      Array of locations.
	 * @param int   $padding_before Number of characters to include before the first match.
	 * @return int Starting position of the relevant extract.
	 */
	public static function extract_start_position( $locations, $padding_before ) {
		// If we only have 1 match we dont actually do the for loop so set to the first.
		$startpos     = $locations[0];
		$loccount     = count( $locations );
		$smallestdiff = PHP_INT_MAX;

		// If we only have 2 skip as its probably equally relevant.
		if ( count( $locations ) > 2 ) {
			// Skip the first as we check 1 behind.
			for ( $i = 1; $i < $loccount; $i++ ) {
				if ( ( $loccount - 1 ) === $i ) {
					$diff = $locations[ $i ] - $locations[ $i - 1 ];
				} else {
					$diff = $locations[ $i + 1 ] - $locations[ $i ];
				}

				if ( $smallestdiff > $diff ) {
					$smallestdiff = $diff;
					$startpos     = $locations[ $i ];
				}
			}
		}

		$startpos = $startpos > $padding_before ? $startpos - $padding_before : 0;
		return $startpos;
	}

	/**
	 * Extract the relevant excerpt for a set of words.
	 *
	 * @since 3.3.0
	 *
	 * @param array  $words           Array of words used to determine the relevant excerpt.
	 * @param string $fulltext        Full text to search for the extract.
	 * @param string $excerpt_more    What to append if $text needs to be trimmed. Default '…'.
	 * @param int    $excerpt_length  Excerpt length in characters.
	 * @param int    $padding_before  Number of characters to include before the first match.
	 * @return string Excerpt containing the relevant portion of of the text.
	 */
	public static function extract_relevant_excerpt( $words, $fulltext, $excerpt_more = '&hellip;', $excerpt_length = -1, $padding_before = 100 ) {

		$textlength = strlen( $fulltext );
		if ( $textlength <= $excerpt_length ) {
			return $fulltext;
		}

		$locations = self::extract_locations( $words, $fulltext );
		$startpos  = self::extract_start_position( $locations, $padding_before );

		// if we are going to snip too much...
		if ( $textlength - $startpos < $excerpt_length ) {
			$startpos = $startpos - ( $textlength - $startpos ) / 2;
		}

		$reltext = substr( $fulltext, $startpos, $excerpt_length );

		// Check to ensure we dont snip the last word if thats the match.
		if ( $startpos + $excerpt_length < $textlength ) {
			$reltext = substr( $reltext, 0, strrpos( $reltext, ' ' ) ) . $excerpt_more; // Remove last word.
		}

		// If we trimmed from the front add ...
		if ( 0 !== $startpos ) {
			$reltext = $excerpt_more . substr( $reltext, strpos( $reltext, ' ' ) + 1 ); // Remove first word.
		}

		return $reltext;
	}

	/**
	 * Strip stopwords from text.
	 *
	 * @since 4.2.0
	 *
	 * @param string|array $subject The string or an array with strings to search and replace.
	 * @param string|array $search  Optional. The pattern to search for. It can be either a string or an array with strings.
	 * @param string|array $replace Optional. The string to replace with. Default empty string.
	 *
	 * @return string Processed text with stopwords removed.
	 */
	public static function strip_stopwords( $subject = '', $search = '', $replace = '' ): string {
		// If no search terms provided, get WordPress stopwords.
		if ( empty( $search ) ) {
			$get_search_stopwords = new \ReflectionMethod( 'WP_Query', 'get_search_stopwords' );
			$get_search_stopwords->setAccessible( true );
			$search = $get_search_stopwords->invoke( new WP_Query() );
			$search = array_merge( $search, array( 'from', 'where' ) );
		}

		// Build regex pattern for all stopwords at once.
		$pattern = '/\b(' . implode( '|', array_map( 'preg_quote', (array) $search ) ) . ')\b/ui';

		// Remove stopwords.
		$output = preg_replace( $pattern, $replace, (string) $subject );

		// Remove single characters and normalize whitespace.
		$output = preg_replace( '/\b[a-z\-]\b/i', '', $output );
		$output = preg_replace( '/\s+/', ' ', $output );

		return trim( $output );
	}

	/**
	 * Parse WP_Query variables to parse comma separated list of IDs and convert them to arrays as needed by WP_Query.
	 *
	 * @since 4.0.0
	 *
	 * @param array $query_vars Defined query variables.
	 * @return array Complete query variables with undefined ones filled in empty.
	 */
	public static function parse_wp_query_arguments( $query_vars ) {

		$array_keys = array(
			'category__in',
			'category__not_in',
			'category__and',
			'post__in',
			'post__not_in',
			'post_name__in',
			'tag__in',
			'tag__not_in',
			'tag__and',
			'tag_slug__in',
			'tag_slug__and',
			'post_parent__in',
			'post_parent__not_in',
			'author__in',
			'author__not_in',
		);

		foreach ( $array_keys as $key ) {
			if ( isset( $query_vars[ $key ] ) ) {
				$query_vars[ $key ] = wp_parse_list( $query_vars[ $key ] );
			}
		}

		return $query_vars;
	}

	/**
	 * Sanitize args.
	 *
	 * @since 4.1.1
	 *
	 * @param array $args Array of arguments.
	 * @return array Sanitized array of arguments.
	 */
	public static function sanitize_args( $args ): array {
		foreach ( $args as $key => $value ) {
			if ( is_string( $value ) ) {
				$args[ $key ] = wp_kses_post( $value );
			}
		}
		return $args;
	}

	/**
	 * Get the primary term for a given post.
	 *
	 * @since 4.2.0
	 *
	 * @param int|\WP_Post $post       Post ID or WP_Post object.
	 * @param string       $term       Term name.
	 * @param bool         $return_all Whether to return all terms.
	 * @param bool         $return_first Whether to return the first term.
	 * @return array Primary term object at `primary` and array of term
	 *               objects at `all` if $return_all is true.
	 */
	public static function get_primary_term( $post, $term = 'category', $return_all = false, $return_first = true ) {
		$return = array(
			'primary' => '',
			'all'     => array(),
		);

		$post = get_post( $post );
		if ( empty( $post ) ) {
			return $return;
		}

		// Yoast primary term.
		if ( class_exists( 'WPSEO_Primary_Term' ) ) {
			$wpseo_primary_term = new \WPSEO_Primary_Term( $term, $post->ID );
			$primary_term       = $wpseo_primary_term->get_primary_term();
			$primary_term       = get_term( $wpseo_primary_term->get_primary_term() );

			if ( ! is_wp_error( $primary_term ) ) {
				$return['primary'] = $primary_term;
			}
		}

		// Rank Math SEO primary term.
		if ( class_exists( 'RankMath' ) ) {
			$primary_term = get_term( get_post_meta( $post->ID, "rank_math_primary_{$term}", true ) );
			if ( ! is_wp_error( $primary_term ) ) {
				$return['primary'] = $primary_term;
			}
		}

		// The SEO Framework primary term.
		if ( function_exists( 'the_seo_framework' ) ) {
			$primary_term = get_term( get_post_meta( $post->ID, "_primary_term_{$term}", true ) );
			if ( ! is_wp_error( $primary_term ) ) {
				$return['primary'] = $primary_term;
			}
		}

		// SEOPress primary term.
		if ( function_exists( 'seopress_init' ) ) {
			$primary_term = get_term( get_post_meta( $post->ID, '_seopress_robots_primary_cat', true ) );
			if ( ! is_wp_error( $primary_term ) ) {
				$return['primary'] = $primary_term;
			}
		}

		if ( empty( $return['primary'] ) || $return_all ) {
			$terms = get_the_terms( $post, $term );

			if ( ! empty( $terms ) ) {
				if ( empty( $return['primary'] ) && $return_first ) {
					$return['primary'] = $terms[0];
				}
				if ( $return_all ) {
					$return['all'] = $terms;
				}
			}
		}

		/**
		 * Filters the primary category/term for the given post.
		 *
		 * @since 3.2.0
		 *
		 * @param array        $return Primary term object at `primary` and optionally
		 *                            array of term objects at `all`.
		 * @param int|\WP_Post $post   Post ID or WP_Post object.
		 * @param string       $term   Term name.
		 */
		return apply_filters( 'bsearch_get_primary_term', $return, $post, $term );
	}

	/**
	 * Get a message about MySQL/MariaDB compatibility issues.
	 *
	 * @since 4.2.0
	 *
	 * @return string Message about compatibility or empty string if compatible.
	 */
	public static function get_database_compatibility_message() {
		global $wpdb;

		$db_version = $wpdb->get_var( 'SELECT VERSION()' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$is_mariadb = strpos( $db_version, 'MariaDB' ) !== false;

		// Extract version number.
		if ( $is_mariadb ) {
			preg_match( '/([0-9]+\.[0-9]+\.[0-9]+)/', $db_version, $matches );
			$version     = $matches[1] ?? '0.0.0';
			$min_version = '10.5.7';
			$rec_version = '10.7.1';
			$db_name     = 'MariaDB';
		} else {
			// MySQL.
			preg_match( '/([0-9]+\.[0-9]+\.[0-9]+)/', $db_version, $matches );
			$version     = $matches[1] ?? '0.0.0';
			$min_version = '5.7.8';
			$rec_version = '8.0.13';
			$db_name     = 'MySQL';
		}

		if ( version_compare( $version, $min_version, '<' ) ) {
			return sprintf(
				/* translators: 1: Database type (MySQL/MariaDB) 2: Current database version 3: Required database version */
				__( '⚠️ Your %1$s version (%2$s) does not support all custom table features. %1$s %3$s or higher is required for optimal performance. The plugin might not be able to deliver the best results. Please consider upgrading your database version.', 'better-search' ),
				esc_html( $db_name ),
				esc_html( $version ),
				esc_html( $min_version )
			);
		}

		if ( version_compare( $version, $rec_version, '<' ) ) {
			return sprintf(
				/* translators: 1: Database type (MySQL/MariaDB) 2: Current database version 3: Recommended database version */
				__( '⚠️ Your %1$s version (%2$s) is below the recommended version %3$s. While the plugin will work, upgrading your database is recommended for better performance.', 'better-search' ),
				esc_html( $db_name ),
				esc_html( $version ),
				esc_html( $rec_version )
			);
		}

		return '';
	}
}
