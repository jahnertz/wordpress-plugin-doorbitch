<?php

class Doorbitch {
	private static $initiated = false;
	public static $debug_mode = true;
	public static $debug = array();

	public static function init() {
		if ( self::$debug_mode ){
			function enqueue_debug_styles() { 
				wp_enqueue_style( 'debug', plugins_url( '/css/debug.css', __FILE__ ) ); 
			}
			add_action( 'wp_enqueue_scripts', 'enqueue_debug_styles' );
			add_action( 'admin_notices', array( get_called_class(), 'debug_show' ) );
			add_action( 'wp_footer', array( get_called_class(), 'debug_show' ) );
		}

		if ( ! self::$initiated ) {
			self::init_hooks();
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
	}
	
	/**
	 * Initialize wordpress hooks:
	 */
	private static function init_hooks() {
		self::$initiated = true;
	}

	public static function bitch_install() {
		global $wpdb;
		global $bitch_db_version;

		$table_name = $wpdb->prefix . 'doorbitch';
		
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

		add_option( 'bitch_db_version', $bitch_db_version );

		// set default frontend form:
		if ( get_option( 'doorbitch_frontend_form' ) == false ) {
			$bitch_frontend_form = file_get_contents( plugin_dir_path( __FILE__ ) . 'forms/default.php' );
			add_option( 'doorbitch_frontend_form', $doorbitch_frontend_form );
		}
	}

	public static function bitch_install_data() {
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
		$installed_ver = get_option( "bitch_db_version" );
		if ( $installed_ver != $bitch_db_version ) {

			$table_name = $wpdb->prefix . 'doorbitch';

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				event tinytext NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				data text NOT NULL,	
				PRIMARY KEY  (id)
			);";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			update_option( "bitch_db_version", $bitch_db_version );
		}
	}

	//Since 3.1 the activation function registered with register_activation_hook() is not called when a plugin is updated:
	public function doorbitch_update_db_check() {
		global $bitch_db_version;
		if ( get_site_option( 'bitch_db_version' ) != $bitch_db_version ) {
			bitch_install();
		}
	}

	public static function debug_show() {
		echo "<div class='doorbitch-debug'><h4>Debug</h4>";
		for ($i = 0; $i < count( self::$debug ); $i++ ) {
			print_r( self::$debug[$i] );
		}
		echo "</div>";
	}

	public static function debug( $debug_text ) {
		$file = basename( debug_backtrace()[0]['file'] );
		self::$debug[] = '<p><i>' . $debug_text . '</i> -> ' . $file . '</p>';
		//TODO: Print errors from table of common errors.

	}
}

