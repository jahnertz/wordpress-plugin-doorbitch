<?php

class Doorbitch {
	private static $initiated = false;

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
}

