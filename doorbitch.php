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

global $bitch_db_version;
$bitch_db_version = '1.0';

function bitch_install() {

	global $wpdb;
	global $bitch_db_version;

	$table_name = $wpdb->prefix . 'doorbitch';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(55) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'bitch_db_version', $bitch_db_version );
}

function bitch_install_data() {
	global $wpdb;

	$welcome_name = "Mr. Doorbitch";
	$welcome_text = "Congratulations, the plugin was successfully installed";

	$table_name = $wpdb->prefix . 'doorbitch';

	$wpdb->insert(
		$table_name,
		array(
				'time' => current_time( 'mysql' ),
				'name' => $welcome_name,
				'text' => $welcome_text,
			)
	);
}

register_activation_hook( __FILE__, 'bitch_install' );
register_activation_hook( __FILE__, 'bitch_install_data' );

//upgrade the database if necessary:
global $wpdb;
$installed_ver = get_option( "jal_db_version" );
if ( $installed_ver != $bitch_db_version ) {

	$table_name = $wpdb->prefix . 'doorbitch';

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(100) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	update_option( "bitch_db_version", $bitch_db_version );
}
//Since 3.1 the activation function registered with register_activation_hook() is not called when a plugin is updated:
function doorbitch_update_db_check() {
	global $bitch_db_version;
	if ( get_site_option( 'bitch_db_version' ) != $bitch_db_version ) {
		bitch_install();
	}
}
add_action( 'plugins_loaded', 'doorbitch_update_db_check' );

class DoorbitchSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'add_plugin_settings_page' ) );
    }

    /**
     * Add settings page under tools
     */
    public function add_plugin_page()
    {
        add_submenu_page(
        	'tools.php',
        	'Doorbitch Settings',
        	'Doorbitch',
        	'manage_options',
        	'doorbitch-settings-admin',
        	array( $this, 'create_admin_page' )
        );
    }

    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'doorbitch_options' );
        ?>
        <div class="wrap">
            <h1>Doorbitch</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'doorbitch_options_group' );
                do_settings_sections( 'doorbitch-settings-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function add_plugin_settings_page()
    {        
        register_setting(
            'doorbitch_options_group', // Option group
            'doorbitch_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'data-field-section', // ID
            'Data Fields', // Title
            array( $this, 'print_section_info' ), // Callback
            'doorbitch-settings-admin' // Page
        );  

        add_settings_field(
            'id_number', // ID
            'ID Number', // Title 
            array( $this, 'id_number_callback' ), // Callback
            'doorbitch-settings-admin', // Page
            'data-field-section' // Section           
        );      

        add_settings_field(
            'title', 
            'Title', 
            array( $this, 'title_callback' ), 
            'doorbitch-settings-admin', 
            'data-field-section'
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function id_number_callback()
    {
        printf(
            '<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
        );
    }
}

if( is_admin() )
    $doorbitch_settings_page = new DoorbitchSettingsPage();