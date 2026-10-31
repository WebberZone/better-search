<?php
/** Console-only WP-CLI test double; indexing and database code remain real. */
class BS_Indexing_CLI_Exit extends RuntimeException {}
class WP_CLI_Command {}
class WP_CLI {
	public static array $output = array();
	public static function line( $message ): void { self::$output[] = array( 'line', $message ); }
	public static function warning( $message ): void { self::$output[] = array( 'warning', $message ); }
	public static function success( $message ): void { self::$output[] = array( 'success', $message ); }
	public static function error( $message ): void { throw new BS_Indexing_CLI_Exit( $message, 1 ); }
}
