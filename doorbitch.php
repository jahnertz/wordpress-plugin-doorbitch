<?php
/**
 * @package Doorbitch
 * @version 0.0.1
 *
 */
/*
Plugin Name: Doorbitch
Plugin URI: https://github.com/jahnertz/wordpress-plugin-doorbitch/tree/master
Description: A wordpress plugin to used to collect and export patrons' basic information.  
Originally written for https://UNKNWN.asia
Login to the collection page via http://yoursite.com/doorbitch
Author: Jordan Han
Version: 0.0.1
Author URI: https://jhanrahan.com.au
*/

function doorbitch_install () {
	global $wpdb;

	$table_name = $wpdb->prefix . "doorbitch";
}