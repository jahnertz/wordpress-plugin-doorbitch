<?php
/**
 * @package Doorbitch
 * @version 0.0.9
 *
 */
/*
Plugin Name: Doorbitch
Plugin URI: https://github.com/jahnertz/wordpress-plugin-doorbitch/tree/master
Description: A wordpress plugin to used to collect and export patrons' basic information. Use the 'Doorbitch' admin page (under Tools) to configure the plugin.
Login to the collection page via http://yoursite.com/doorbitch
Author: Jordan Han
Version: 0.0.9
Author URI: https://jhanrahan.com.au
*/
global $doorbitch;

define( 'DOORBITCH__DATABASE_VERSION', 1.2 );
define( 'DOORBITCH__DEBUG_MODE', true );
define( 'DOORBITCH__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch.php' );

$doorbitch = new Doorbitch;

doorbitch::set_current_event( 'Example' );

register_activation_hook( __FILE__, array( 'Doorbitch', 'install' ) );
register_activation_hook( __FILE__, array( 'Doorbitch', 'install_data' ) );

