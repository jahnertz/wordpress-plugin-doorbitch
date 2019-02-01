<?php

class Doorbitch {
    const default_form_url = 'register';
	public $debug_mode = true;
	public $debug_messages = array();
	public $table_suffix = 'doorbitch';
	public $default_event = 'Example Event';

	private $options;

	public function init () {
		$this->options = get_option( DOORBITCH__OPTIONS );
		// Run the install function if we're not already initiated.
		$this->debug( "Plugin started successfully." );
		if ( ! isset( $this->options[ 'initiated' ] ) || $this->options[ 'initiated' ] == false ) {
			$this->install();
		} 

        if ( ! is_admin() ) {
            // add routing class for virtual pages etc.
    		require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch-router.php' );
    		$doorbitch_router = new Doorbitch_Router( $this );
			function enqueue_user_styles() {
				wp_enqueue_style( 'doorbitch-frontend-styles', plugins_url( '/css/doorbitch.css' , __FILE__) );
			}
			add_action( 'wp_enqueue_scripts', 'enqueue_user_styles' );
        } else {
			// Add admin options page under 'tools' section:
			require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch-admin.php' );
			$doorbitch_admin = new Doorbitch_Admin( $this );

			function enqueue_admin_styles() {
				wp_enqueue_style( 'doorbitch-admin', plugins_url( '/css/doorbitch-admin.css', __FILE__ ) );
			}
			add_action( 'admin_enqueue_scripts', 'enqueue_admin_styles' );
		}

		// upgrade the database if neccessary:
		// add_action( 'plugins_loaded', array( get_called_class(), 'update_db_check' ) );

		// Add debug mode hooks if it's activated:
		if ( isset ( $this->options[ 'debug_mode' ] ) && $this->options[ 'debug_mode' ] ) {
			function enqueue_debug_styles() { 
				wp_enqueue_style( 'debug', plugins_url( '/css/debug.css', __FILE__ ) ); 
			}
			add_action( 'wp_enqueue_scripts', 'enqueue_debug_styles' );
			add_action( 'admin_notices', array( $this, 'debug_display_own' ) );
		}
	}

	public function install() {
		global $wpdb;
		// get the options if they're still around or else intialise them.
	    if ( ! $this->options = get_option( DOORBITCH__OPTIONS ) ){
	    	$this->options = array();
	    }

		$this->debug( 'initiating...' );
		$db_current_version = 0.0;
 		if ( array_key_exists( 'db_version' , $this->options ) ) {
		    $db_current_version = $this->options[ 'db_version' ];
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
		$this->set_current_event( $this->default_event );
		$this->add_data( $this->default_event, 'Name:Joe Bloggs,Age:42,email:nobody@nowhere' );

		$events = $wpdb->get_results ( "SELECT DISTINCT event FROM {$wpdb->prefix}doorbitch" );
        $event_array = array();
        foreach ($events as $event) {
        	$this->debug( 'adding event: ' . $event->event );
        	array_push( $event_array, $event->event );
        }

		$this->options[ 'events' ] = serialize( $event_array );
        // Set options to defaults if they are missing:
		isset( $this->options[ 'form_html' ] ) ? $this->options[ 'form_html' ] : file_get_contents( DOORBITCH__PLUGIN_DIR . '/forms/default.html' );

        if ( !isset( $this->options[ 'confirmation_email_html' ] ) ) {
            $this->options[ 'confirmation_email_html' ] = file_get_contents(DOORBITCH__PLUGIN_DIR . '/email_templates/default.html' );
        }
		$this->options[ 'initiated' ] = true;
        isset( $this->options[ 'require_auth' ] ) ? $this->options[ 'require_auth' ] : true;
		isset( $this->options[ 'debug_mode' ] ) ? $this->options[ 'debug_mode' ] : false;
        isset( $this->options[ 'form_url' ] ) ? $this->options[ 'form_url' ] : $this->default_form_url;
        isset( $this->options[ 'confirmation_email_use_html' ] ) ? $this->options[ 'confirmation_email_use_html' ] : true;

		update_option( 'doorbitch_options', $this->options );
		$this->debug( 'saving options' );

        // reset rewrite rules - required by the router
        flush_rewrite_rules();

		// show error output on plugin activation
		if ( defined('WP_DEBUG') && true === WP_DEBUG ) { 
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

	public function set_current_event( $event_name = NULL ) {
		$options = get_option( 'doorbitch_options' );
		$event_array = unserialize( $options[ 'events' ] );
		if ( $event_name == NULL ) {
			$event_name = $event_array[ 0 ];
		}
		if ( in_array( $event_name, $event_array ) ) {
			$options[ 'current_event' ] = $event_name;
		}
		else {
			add_event( $event_name );
		}
		update_option( 'doorbitch_options', $options );
	}

	public function upgrade_database() {
		global $wpdb;
		$this->options = get_option( DOORBITCH__OPTIONS );

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

	    $this->options[ 'db_version' ] = DOORBITCH__DATABASE_VERSION;
		update_option( 'doorbitch_options', $this->options );
	}

	public function update_db_check( $db_current_version ) {
		//Since 3.1 the activation function registered with register_activation_hook() is not called when a plugin is updated:
		$this_db_version = DOORBITCH__DATABASE_VERSION;
		if ( $this_db_version > $db_current_version ) {
			$this->debug( "Installing database v." . DOORBITCH__DATABASE_VERSION );
			$this->upgrade_database();
		}
		return $this_db_version;
	}

    public function get_registrants( $event ) {
        global $wpdb;

        $results = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}doorbitch WHERE event='{$event}'" );
        if ( empty( $results ) ){
            // maybe unnessesary.
            return array();
        } else {
            $entries = array();
            foreach( $results as $result ) {
                $entry = array();
                // hide event column
                // $entry [ 'event' ] = $result->event;
                $entry [ 'time' ] = $result->time;
                $data = explode( ',', $result->data );
                foreach ( $data as $datum ) {
                    $keypair = explode( ':', $datum );
                    if ( array_key_exists( 1, $keypair ) ) {
                        $entry[ $keypair[0] ] = $keypair[1];
                    }
                }
                array_push( $entries, $entry );
            }
            return $entries;
        }
    }
	public function add_event( $event_name ) {
		$options = get_option( DOORBITCH__OPTIONS );
		if ( ! array_key_exists( 'events', $options ) ) {
			$event_array = array();
		}
		else {
			$event_array = unserialize( $options[ 'events' ] );
		}
		// $events = $options[ 'events' ];
		// add the event iff it doesn't already exist
		if ( ! in_array( $event_name, $event_array ) ) {
			array_push( $event_array, $event_name );
		}
		$options[ 'current_event' ] = $event_name;
		$options[ 'events' ] = serialize( $event_array );
		update_option( DOORBITCH__OPTIONS, $options );
	}

	public function remove_event( $event_name ) {
		$options = get_option( DOORBITCH__OPTIONS );
		if ( ! array_key_exists( 'events', $options ) ) {
			$event_array = array();
			$this->add_event( $default_event );
		}
		else {
			$event_array = unserialize( $options[ 'events' ] );
		}
		if ( $key = array_search( $event_name, $event_array ) !== false ) {
			unset( $event_array[ $key ] );
			$new_event_array = array_values( $event_array );
			$options[ 'events' ] = serialize( $new_event_array );
			update_option( DOORBITCH__OPTIONS, $options );
			return true;
		} else {
			return false;
		}
	}

	public function add_data( $event, $data ) {
		global $wpdb;
		$this->options = get_option( DOORBITCH__OPTIONS );

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

	public function debug_display_own() {
		$this->debug_display( $this->debug_messages );
	}

	public function debug_display( $debug_messages ) {
		foreach ( $debug_messages as $debug_message ) {
        	if ( is_admin() ) {
	        	echo( "<p>" . $debug_message . "</p>" );
	        }
            error_log( $debug_message );
		}
	}

	public function debug( $object ) {
        //collect debug messages and their origins:
		$file = basename( debug_backtrace()[0]['file'] );
        if (is_array($object)) {
            $this->debug_messages[] = htmlspecialchars( var_export( $object ) ) . ' (' . $file . ')';
        } else {
            $this->debug_messages[] = '[' . $file . '] ' . htmlspecialchars( $object );
        }
	}

	public function dump_options() {
		// Show options array in debug area:
		$this->options = get_option( DOORBITCH__OPTIONS );
		foreach ( $this->options as $option => $value ) {
			$this->debug( $option . ' : ' . $value );
		}
	}
}

