<?php

class Doorbitch {
	//TODO: initiated defaults to false, save as an option
	public $debug_mode = true;
	public static $debug_messages = array();
	public $table_suffix = 'doorbitch';
	public $default_event = 'Example Event';

	public $options;

	public function __construct() {
		$options = $this->get_options();

		// Run the install function if we're not already initiated.
		// if ( ! isset( $options[ 'initiated' ] ) || $options[ 'initiated' ] == false ) {
		// 	$this->install();
		// } 

		// Show _POST data:
		foreach ( $_POST as $key => $value) {
			$this->debug( $key . ':' . $value );
		}

		require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch-virtual-pages.php' );
		$doorbitch_virtual_pages = new Doorbitch_Virtual_Pages();
	
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
		// add_action( 'plugins_loaded', array( get_called_class(), 'update_db_check' ) );

	}

	public function get_options() {
		// if ( empty( $this->options ) ) {
			if ( ! $this->options = get_option( 'doorbitch_options' ) ) {
				// get_option will return false if the option doesn't exist.
				$this->options = array();
				update_option( 'doorbitch_options', $this->options );
			}
		// }
		// $this->options = get_option( 'doorbitch_options' );
		return $this->options;
	}

	public function install() {
		global $wpdb;
	    $options = $this->get_options();

		$this->debug( 'initiating...' );

		$db_current_version = 0.0;
 		if ( array_key_exists( 'db_version' , $options ) ) {
		    $db_current_version = $options[ 'db_version' ];
	    }
	    $db_current_version = $this->update_db_check( $db_current_version );

		$table_name = $wpdb->prefix . $this->table_suffix;
		$charset_collate = $wpdb->get_charset_collate();

		// add the table to store data from the frontend form
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			event tinytext NOT NULL,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			data text NOT NULL,	
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );


		$this->add_event( $this->default_event );
		// $options[ 'current_event' ] = $this->default_event;
		$this->set_current_event( $this->default_event );
		$this->add_data( $this->default_event, 'Name:Joe Bloggs,Age:42,email:nobody@nowhere' );

		// TODO: scan database for events
		$events = $wpdb->get_results ( "SELECT DISTINCT event FROM {$wpdb->prefix}doorbitch" );
        $options[ 'events' ] = array();
        foreach ($events as $event) {
        	$this->debug( 'adding event: ' . $event->event );
        	array_push( $options[ 'events' ], $event->event );
        }

		$options[ 'form_html' ] = file_get_contents( DOORBITCH__PLUGIN_DIR . '/forms/default.php' );
		$options[ 'initiated' ] = true;

		update_option( 'doorbitch_options', $options );
		$this->debug( 'saving options' );

		// show error output on plugin activation
		if ( defined('WP_DEBUG') && true === WP_DEBUG && DOORBITCH__DEBUG_MODE ) { 
		    function doorbitch_activated_plugin_error() {
		        update_option( 'doorbitch_error',  ob_get_contents() );
		    }
		    function doorbitch_deactivated_plugin_error() {
		        delete_option( 'doorbitch_error' );
		    }
		    add_action( 'activated_plugin', 'doorbitch_activated_plugin_error' );
		    add_action( 'deactivated_plugin', 'doorbitch_deactivated_plugin_error' );
		     
		    function doorbitch_message_plugin_error() {
		        ?>
		        <div class="notice notice-error">
		            <p><?php echo get_option( 'doorbitch_error' ); ?></p>
		        </div>
		        <?php
		        }
		    if( get_option( 'doorbitch_error' ) ) {
		        add_action( 'admin_notices', 'doorbitch_message_plugin_error' ); 
		    }
		}
	}

	public function add_event( $event_name ) {
		$options = $this->get_options();
		if ( ! array_key_exists( 'events', $options ) ) {
			$options[ 'events' ] = array();
		}
		// $events = $options[ 'events' ];
		// add the event iff it doesn't already exist
		if ( ! in_array( $event_name, $options[ 'events' ] ) ) {
			array_push( $options[ 'events' ], $event_name );
		}
		$options[ 'current_event' ] = $event_name;
		update_option( 'doorbitch_options', $options );
	}

	public static function set_current_event( $event_name ) {
		$options = get_option( 'doorbitch_options' );
		if ( in_array( $event_name, $options[ 'events' ] ) ) {
			$options[ 'current_event' ] = $event_name;
		}
		else {
			// $this->debug( 'event \'' . $event_name . '\' not found' );
			add_event( $event_name );
		}
		update_option( 'doorbitch_options', $options );
	}

	public function upgrade_database() {
		global $wpdb;
		$options = $this->get_options();

		$table_name = $wpdb->prefix . $this->table_suffix;

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			event tinytext NOT NULL,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			data text NOT NULL,	
			PRIMARY KEY  (id)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	    $options[ 'db_version' ] = DOORBITCH__DATABASE_VERSION;
		update_option( 'doorbitch_options', $options );
	}

	//Since 3.1 the activation function registered with register_activation_hook() is not called when a plugin is updated:
	public function update_db_check( $db_current_version ) {
		// global $db_version;
		// TODO: Check if database needs upgrading
		$this_db_version = DOORBITCH__DATABASE_VERSION;
		if ( $this_db_version > $db_current_version ) {
			$this->debug( "Installing database v." . DOORBITCH__DATABASE_VERSION );
			$this->upgrade_database();
		}
		return $this_db_version;
	}

	public function add_data( $event, $data ) {
		global $wpdb;
		$options = $this->get_options();

		$table_name = $wpdb->prefix . $this->table_suffix;

		$wpdb->insert(
			$table_name,
			array(
				'event' => $event,
				'time' => current_time( 'mysql' ),
				'data' => $data
			)
		);
		return true;
	}

	public function debug_show() {
		echo "<div class='doorbitch-debug'><h4>Debug</h4>";
		for ($i = 0; $i < count( self::$debug_messages ); $i++ ) {
			print_r( self::$debug_messages[$i] );
		}
		echo "</div>";
	}

	public static function debug( $debug_text ) {
		$file = basename( debug_backtrace()[0]['file'] );
		self::$debug_messages[] = '<p><i>' . htmlspecialchars( $debug_text ) . '</i> -> ' . $file . '</p>';
		//TODO: Print errors from table of common errors.

	}

	public function dump_options() {
		// Show options array in debug area:
		$options = $this->get_options();
		foreach ( $options as $option => $value ) {
			if (! is_array( $value ) ){
				$this->debug( $option . ' : ' . $value );
			}
			else {
				$list = $option . ': ';
				foreach ($value as $item) {
					$list .= $item . ', ';
				}
				$this->debug( $list );
			}
		}
	}
}

