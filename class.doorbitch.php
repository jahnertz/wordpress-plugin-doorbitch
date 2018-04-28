<?php

class Doorbitch {
	private static $initiated = false;
	public static $debug = true;

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}
	
	/**
	 * Initialize wordpress hooks:
	 */
	private static function init_hooks() {
		self::$initiated = true;
	}

	public static function show_debug() {
		echo "<h4>Debug</h4>";
	}
}

