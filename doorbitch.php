<?php
/**
 * @package Doorbitch
 * @version 0.0.11
/*
Plugin Name: Doorbitch
Plugin URI: https://github.com/jahnertz/wordpress-plugin-doorbitch/tree/master
Description: A wordpress plugin to used to collect and export patrons' basic information. Use the 'Doorbitch' admin page (under Tools) to configure the plugin.
Login to the collection page via http://yoursite.com/doorbitch
Author: Jordan Han
Version: 0.0.11
Author URI: https://jhanrahan.com.au
*/
global $doorbitch;

define( 'DOORBITCH__DATABASE_VERSION', 1.2 );
define( 'DOORBITCH__DEBUG_MODE', true );
define( 'DOORBITCH__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DOORBITCH__OPTIONS', 'doorbitch_options' );

require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch.php' );

$doorbitch = new Doorbitch;

register_activation_hook( __FILE__, array( $doorbitch, 'install' ) );
// add_action( 'plugins_loaded', array( $doorbitch, 'dump_options' ) );

// Add debugger assets if we're in debug mode.
if ( DOORBITCH__DEBUG_MODE ){
	function enqueue_debug_styles() { 
		wp_enqueue_style( 'debug', plugins_url( '/css/debug.css', __FILE__ ) ); 
	}
	add_action( 'wp_enqueue_scripts', 'enqueue_debug_styles' );
	add_action( 'admin_notices', array( $doorbitch, 'debug_show' ) );
	add_action( 'wp_footer', array( $doorbitch, 'debug_show' ) );
}
