<?php

class Doorbitch {
	private static $initiated = false;
	//TODO: initiated defaults to false, save as an option
	public static $debug_mode = true;
	public static $debug_messages = array();
	public static $table_suffix = 'doorbitch';

	private $options;

	public function __construct() {
        $this->options = get_option( 'doorbitch_options' );
        foreach ( $this->options as $option => $value ) {
	        self::debug( $option . ':' . $value );
        }

		if ( self::$debug_mode ){
			function enqueue_debug_styles() { 
				wp_enqueue_style( 'debug', plugins_url( '/css/debug.css', __FILE__ ) ); 
			}
			add_action( 'wp_enqueue_scripts', 'enqueue_debug_styles' );
			add_action( 'admin_notices', array( get_called_class(), 'debug_show' ) );
			add_action( 'wp_footer', array( get_called_class(), 'debug_show' ) );
		}

		//Add virtual page for the frontend form:
		require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch-virtual-pages.php' );
		$doorbitch_virtual_pages = new Doorbitch_Virtual_Pages();
		// self::debug( 'adding virtual pages class' );
	
		//Add admin options page under 'tools' section:
		if( is_admin() ) {
		    // include PhpSpreadsheet library:
		    require 'vendor/autoload.php';
			require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch-admin.php' );
			$doorbitch_admin = new Doorbitch_Admin();
			function enqueue_admin_styles() {
				wp_enqueue_style( 'doorbitch-admin', plugins_url( '/css/doorbitch-admin.css', __FILE__ ) );
			}
			add_action( 'admin_enqueue_scripts', 'enqueue_admin_styles' );
		}

		//upgrade the database if neccessary:
		add_action( 'plugins_loaded', array( get_called_class(), 'update_db_check' ) );

		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}
	
	/**
	 * Initialize wordpress hooks:
	 */
	private static function init_hooks() {
		// flush_rewrite_rules();
	    // self::debug( 'flushing rewrite rules' );
		self::$initiated = true;
	}

	public static function install() {
		global $wpdb;
		global $db_version;

		$table_name = $wpdb->prefix . self::$table_suffix;
		
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			event tinytext NOT NULL,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			data text NOT NULL,	
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// add_option( 'db_version', $db_version );
		// array_push( $this->options, 'db_version', $db_version );

		// TODO: add frontend form to options
		// set default frontend form:
		// if ( get_option( 'doorbitch_frontend_form' ) == false ) {
		// 	$bitch_frontend_form = file_get_contents( plugin_dir_path( __FILE__ ) . 'forms/default.php' );
		// 	add_option( 'doorbitch_frontend_form', $doorbitch_frontend_form );
		// }

		// update_option( 'doorbitch_options', $this->options );
		self::debug( 'saving options' );
	}

	public static function install_data() {
		global $wpdb;

		$welcome_event = "Example";
		$welcome_data = "Name:Example Person,Age:18-25,Comment:Nothing to see here.";

		$table_name = $wpdb->prefix . 'doorbitch';

		$wpdb->insert(
			$table_name,
			array(
					'event' => $welcome_event,
					'time' => current_time( 'mysql' ),
					'data' => $welcome_data,
				)
		);

		$event_r = ['unknwn-event' => date(DATE_ISO8601), 1, false ];
		add_option( 'doorbitch_events', $event_r );
	}

	public static function upgrade_database() {
		//upgrade the database if necessary:
		global $wpdb;
		$installed_ver = get_option( "db_version" );
		if ( $installed_ver != $db_version ) {

			$table_name = $wpdb->prefix . self::$table_suffix;

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				event tinytext NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				data text NOT NULL,	
				PRIMARY KEY  (id)
			);";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			update_option( "db_version", $db_version );
		}
	}

	//Since 3.1 the activation function registered with register_activation_hook() is not called when a plugin is updated:
	public static function update_db_check() {
		global $db_version;
		// TODO: Check if database needs upgrading
		if ( false ) {
			self::debug( 'upgrading database' );
			self::install();
		}
	}

	public static function add_data( $data ) {
		global $wpdb;
		$current_event = 'none';
		$event = $current_event;
		$table_name = $wpdb->prefix . self::$table_suffix;

		$wpdb->insert(
			$table_name,
			array(
				'event' => $event,
				'time' => current_time( 'mysql' ),
				'data' => $data
			)
		);
	}

	public static function debug_show() {
		echo "<div class='doorbitch-debug'><h4>Debug</h4>";
		for ($i = 0; $i < count( self::$debug_messages ); $i++ ) {
			print_r( self::$debug_messages[$i] );
		}
		echo "</div>";
	}

	public static function debug( $debug_text ) {
		$file = basename( debug_backtrace()[0]['file'] );
		self::$debug_messages[] = '<p><i>' . $debug_text . '</i> -> ' . $file . '</p>';
		//TODO: Print errors from table of common errors.

	}
}

