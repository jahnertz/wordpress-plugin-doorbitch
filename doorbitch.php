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
global $doorbitch;

define( 'DOORBITCH__DATABASE_VERSION', 1.2 );
define( 'DOORBITCH__DEBUG_MODE', true );
define( 'DOORBITCH__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch.php' );

$doorbitch = new Doorbitch;

/* For debugging only */
if (defined('WP_DEBUG') && true === WP_DEBUG) { 
    function myplugin_activated_plugin_error() {
        update_option( 'myplugin_error',  ob_get_contents() );
    }
    function myplugin_deactivated_plugin_error() {
        delete_option( 'myplugin_error' );
    }
    add_action( 'activated_plugin', 'myplugin_activated_plugin_error' );
    add_action( 'deactivated_plugin', 'myplugin_deactivated_plugin_error' );
     
    function myplugin_message_plugin_error() {
        ?>
        <div class="notice notice-error">
            <p><?php echo get_option( 'myplugin_error' ); ?></p>
        </div>
        <?php
        }
    if( get_option( 'myplugin_error' ) ) {
        add_action( 'admin_notices', 'myplugin_message_plugin_error' ); 
    }
}

register_activation_hook( __FILE__, array( 'Doorbitch', 'install' ) );
register_activation_hook( __FILE__, array( 'Doorbitch', 'install_data' ) );

