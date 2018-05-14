<?php
// include PhpSpreadsheet library:
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Doorbitch {
	//TODO: initiated defaults to false, save as an option
	public $debug_mode = true;
	public static $debug_messages = array();
	public $table_suffix = 'doorbitch';
	public $default_event = 'Example Event';

	private $options;

	public function init () {
		$this->options = get_option( DOORBITCH__OPTIONS );

		// Run the install function if we're not already initiated.
		if ( ! isset( $this->options[ 'initiated' ] ) || $this->options[ 'initiated' ] == false ) {
			$this->install();
		} 

		// Show _POST data:
		// foreach ( $_POST as $key => $value) {
		// 	$this->debug( $key . ':' . $value );
		// }

		require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch-virtual-pages.php' );
		$doorbitch_virtual_pages = new Doorbitch_Virtual_Pages();
	
		//Add admin options page under 'tools' section:
		if( is_admin() ) {
		    // include PhpSpreadsheet library:
		    // require 'vendor/autoload.php';
			require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch-admin.php' );

			$doorbitch_admin = new Doorbitch_Admin();
			add_action( 'init', array( &$doorbitch_admin, 'init' ) );

			function enqueue_admin_styles() {
				wp_enqueue_style( 'doorbitch-admin', plugins_url( '/css/doorbitch-admin.css', __FILE__ ) );
			}
			add_action( 'admin_enqueue_scripts', 'enqueue_admin_styles' );
		} else {
			function enqueue_user_styles() {
				wp_enqueue_style( 'doorbitch-frontend-styles', plugins_url( '/css/doorbitch.css' , __FILE__) );
			}
			add_action( 'wp_enqueue_scripts', 'enqueue_user_styles' );
		}

		//upgrade the database if neccessary:
		// add_action( 'plugins_loaded', array( get_called_class(), 'update_db_check' ) );

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

 	// 	if ( array_key_exists( 'events' , $this->options ) ) { $this->options[ 'events' ] = serialize( $event_array ); }
		// if ( array_key_exists( 'form_title' , $this->options ) ) { $this->options[ 'form_title ' ] = 'Register'; }
		// if ( array_key_exists( 'form_html' , $this->options ) ) { $this->options[ 'form_html' ] = file_get_contents( DOORBITCH__PLUGIN_DIR . '/forms/default.php' ); }
		$this->options[ 'events' ] = serialize( $event_array );
		$this->options[ 'form_html' ] = file_get_contents( DOORBITCH__PLUGIN_DIR . '/forms/default.php' );
		$this->options[ 'initiated' ] = true;

		update_option( 'doorbitch_options', $this->options );
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

	public static function add_event( $event_name ) {
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

	public static function remove_event( $event_name ) {
		$options = get_option( DOORBITCH__OPTIONS );
		if ( ! array_key_exists( 'events', $options ) ) {
			$event_array = array();
			self::add_event( $default_event );
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

	public static function set_current_event( $event_name = NULL ) {
		$options = get_option( 'doorbitch_options' );
		$event_array = unserialize( $options[ 'events' ] );
		if ( $event_name == NULL ) {
			$event_name = $event_array[ 0 ];
		}
		if ( in_array( $event_name, $event_array ) ) {
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

    public static function export_records( $event ) {
        $entries = self::get_registrants( $event );
        if ( empty( $entries ) ) {
        	return false;
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // write the title on row 1:
        $row = 1;
        $col = 1;
        $sheet->setCellValueByColumnAndRow( $col, $row, 'Event:' );
        $sheet->setCellValueByColumnAndRow( $col + 1, $row, $event );

        // write the headers on row 2:
        $row = 2;
        $col = 1;
        foreach ( $entries[0] as $header => $value ) {
            $sheet->setCellValueByColumnAndRow( $col, $row, $header );
            $col++;
        }

        // write the entries, starting on row 3:
        $row = 3;
        foreach ( $entries as $entry ) {
            $col = 1;
            foreach ( $entry as $key => $value) {
                $sheet->setCellValueByColumnAndRow( $col, $row, $value );
                $col++;
            }
            $row++;
        }

        // Fields that need to be persist:
        $persist = array ( 'event', 'action' );

        // Get credentials:
        $method = ''; //ftp or empty.
        $url = wp_nonce_url('tools.php?page=doorbitch-settings-admin', 'doorbitch_view_export_nonce' );
			if (false === ($creds = request_filesystem_credentials( $url, $method, false, false, $persist ) ) ) {
        		return true;
        	}
        // fire up wp_filesystem:
        if ( ! WP_Filesystem( $creds ) ) {
        	request_filesystem_credentials( $url, $method, true, false, $persist );
        	return true;
        }

        global $wp_filesystem;

        // create the export directory
        $export_dir = DOORBITCH__PLUGIN_DIR . 'export';
        $filename = trailingslashit( $export_dir ) . preg_replace('/\s/', '-', $event) . '_' . current_time( 'Y-m-d_Hi') . '.xlsx';

        if ( ! $wp_filesystem->mkdir( $export_dir ) ) {
		add_settings_error( 'doorbitch', 'create_directory', esc_html__('Unable to create the export directory.', 'doortbitch'), 'error' );
		return $_POST;
        }

        // create the file
        // todo: create an empty file so we can write to it with a pointer.
		if ( ! $wp_filesystem->put_contents( $filename, '', FS_CHMOD_FILE) ) {
			add_settings_error( 'pluginception', 'create_file', esc_html__('Unable to create the plugin file.', 'pluginception'), 'error' );
		}

        $writer = new Xlsx($spreadsheet);
        $upload_dir = wp_upload_dir();
        // this should be writing to a pointer to our empty file.
        // $test_filename = trailingslashit( $export_dir ) . 'test.xlsx';
        // $saved = $writer->save( $filename );
        echo $saved;
        return $filename;
    }

    public static function get_registrants( $event ) {
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


	public function debug_show() {
		if ( ! empty( $debug_messages ) ) {
			echo "<div class='doorbitch-debug'>";
			for ($i = 0; $i < count( self::$debug_messages ); $i++ ) {
				print_r( self::$debug_messages[$i] );
			}
			echo "</div>";
		}
	}

	public static function debug( $debug_text ) {
		$file = basename( debug_backtrace()[0]['file'] );
		self::$debug_messages[] = '<p><i>' . htmlspecialchars( $debug_text ) . '</i> -> ' . $file . '</p>';
		if ( is_array( $debug_text ) || is_object( $debug_text ) ) {
		 error_log( print_r( $debug_text, true ) );
		} else {
		 error_log( $debug_text );
		}

		//TODO: Print errors from table of common errors.

	}

	public function dump_options() {
		// Show options array in debug area:
		$this->options = get_option( DOORBITCH__OPTIONS );
		foreach ( $this->options as $option => $value ) {
			$this->debug( $option . ' : ' . $value );
		}
	}
}

