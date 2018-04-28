<?php

class Doorbitch {
	private static $initiated = false;
	public static $debug_mode = true;
	public static $debug = array();

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
		add_option( 'doorbitch_debug_mode', $debug_mode );
	}
	
	/**
	 * Initialize wordpress hooks:
	 */
	private static function init_hooks() {
		self::$initiated = true;
	}

	public static function debug_show() {
		echo "<div class='doorbitch-debug'><h4>Debug</h4>";
		for ($i = 0; $i < count( self::$debug ); $i++ ) {
			print_r( self::$debug[$i] );
		}
		echo "</div>";
	}

	public static function debug_add() {

	}
}

