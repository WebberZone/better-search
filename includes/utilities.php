<?php
/**
 * Utlity functions used by Better Search
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Clean search string from XSS exploits.
 *
 * @since   1.0
 *
 * @param   string $val    Potentially unclean string.
 * @return  string  Cleaned string
 */
function bsearch_clean_terms( $val ) {

	$val = stripslashes( rawurldecode( $val ) );

	$badwords = array_map( 'trim', explode( ',', bsearch_get_option( 'badwords' ) ) );

	$censor_char = ' ';

	/**
	 * Allow the censored character to be replaced.
	 *
	 * @since   2.1.0
	 *
	 * @param   string  $censor_char Censored character
	 * @param   string  $val        Raw search string
	 */
	$censor_char = apply_filters( 'bsearch_censor_char', $censor_char, $val );

	$val_censored = bsearch_censor_string( $val, $badwords, $censor_char );  // No more bad words.
	$val          = $val_censored['clean'];

	$val = wp_kses_post( $val );

	/**
	 * Clean search string from XSS exploits.
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $val    Cleaned string
	 */
	return apply_filters( 'bsearch_clean_terms', $val );
}
add_filter( 'get_search_query', 'bsearch_clean_terms' );


/**
 * Generates a random string.
 *
 * @since   1.3.3
 *
 * @param   string $chars  Chars that can be used.
 * @param   int    $len    Length of the output string.
 * @return  string  Random string
 */
function bsearch_rand_censor( $chars, $len ) {

	$output = '';
	for ( $i = 0; $i < $len; $i++ ) {
		$output .= substr( $chars, wp_rand( 0, strlen( $chars ) - 1 ), 1 );
	}

	return $output;
}


/**
 * Apply censorship to $string, replacing $badwords with $censor_char.
 *
 * @since   1.3.3
 *
 * @param   string $string     String to be censored.
 * @param   array  $badwords   Array of badwords.
 * @param   string $censor_char String which replaces bad words. If it's more than 1-char long, a random string will be generated from these chars. Default: '*'.
 * @return  string  Cleaned up string
 */
function bsearch_censor_string( $string, $badwords, $censor_char = '*' ) {

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

	$words = explode( ' ', $string );

	// is $censor_char a single char?
	$is_one_char = ( strlen( $censor_char ) === 1 );

	// Count the bad words.
	$no_of_badwords = count( $badwords );

	for ( $x = 0; $x < $no_of_badwords; $x++ ) {

		$replacement[ $x ] = $is_one_char
			? str_repeat( $censor_char, strlen( $badwords[ $x ] ) )
			: bsearch_rand_censor( $censor_char, strlen( $badwords[ $x ] ) );

		$badwords[ $x ] = '/' . str_ireplace( array_keys( $leet_replace ), array_values( $leet_replace ), $badwords[ $x ] ) . '/i';
	}

	$newstring          = array();
	$newstring['orig']  = ( $string );
	$newstring['clean'] = preg_replace( $badwords, $replacement, $newstring['orig'] );

	return $newstring;

}


/**
 * Convert Hexadecimal colour code to RGB.
 *
 * @since   1.3.4
 *
 * @param   string $color  Hexadecimal colour.
 * @return  array   Array containing RGB colour code
 */
function bsearch_html2rgb( $color ) {

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
function bsearch_rgb2html( $r, $g = -1, $b = -1, $padhash = false ) {

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

