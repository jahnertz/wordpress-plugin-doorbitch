<?php
/**
 * @package Doorbitch
 * @version 0.1.2
/*
Plugin Name: Doorbitch
Plugin URI: https://github.com/jahnertz/wordpress-plugin-doorbitch/tree/master
Description: A wordpress plugin to used to collect and export patrons' basic information. Use the 'Doorbitch' admin page (under Tools) to configure the plugin.
Login to the collection page via http://yoursite.com/doorbitch
Author: Jordan Han
Version: 0.1.2
Author URI: https://jhanrahan.com.au
*/
global $doorbitch;

define( 'DOORBITCH__DATABASE_VERSION', 1.2 );
// define( 'DOORBITCH__DEBUG_MODE', true );
// todo: make this an option!
define( 'DOORBITCH__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DOORBITCH__PLUGIN_DIR_URL', plugin_dir_url( __FILE__) );
define( 'DOORBITCH__OPTIONS', 'doorbitch_options' );

require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch.php' );

$doorbitch = new Doorbitch;

add_action( 'init', array( &$doorbitch, 'init' ), 1 );

register_activation_hook( __FILE__, array( $doorbitch, 'install' ) );