<?php
/**
 * @package Doorbitch
 * @version 0.0.7
 *
 */
/*
Plugin Name: Doorbitch
Plugin URI: https://github.com/jahnertz/wordpress-plugin-doorbitch/tree/master
Description: A wordpress plugin to used to collect and export patrons' basic information. Use the 'Doorbitch' admin page (under Tools) to configure the plugin.
Login to the collection page via http://yoursite.com/doorbitch
Author: Jordan Han
Version: 0.0.7
Author URI: https://jhanrahan.com.au
*/

global $bitch_db_version;
global $doorbitch;
$bitch_db_version = '1.1';


define( 'DOORBITCH__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch.php' );

$doorbitch = new Doorbitch;

register_activation_hook( __FILE__, array( 'Doorbitch', 'install' ) );
register_activation_hook( __FILE__, array( 'Doorbitch', 'install_data' ) );

