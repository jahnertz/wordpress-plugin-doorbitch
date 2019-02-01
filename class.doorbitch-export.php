<?php
// include PhpSpreadsheet library:
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Doorbitch_Export {
    public $doorbitch;

    public function __construct( Doorbitch $doorbitch ) {
        $this->doorbitch = $doorbitch;
        $doorbitch->debug( "Exporter created." );
    }

	public function export_records( $event ) {
        $doorbitch = $this->doorbitch;
        $doorbitch->debug( 'Exporting records for ' . $event );
        
		global $wp_filesystem;
		$export_dir = trailingslashit( DOORBITCH__PLUGIN_DIR . 'export' );
		$doorbitch->debug( 'Export Dir: ' . $export_dir );
		$filename = preg_replace( '/\s/', '_', $event )
			. '_'
			. current_time( 'Y-m-d_Hi' )
			. '.csv';
		$url = wp_nonce_url( 'tools.php?page=doorbitch-settings-admin', 'doorbitch-settings-admin');
		$method = '';
        $form_fields = array ( 'event', 'action' );
        // TODO: request_filesystem_credentials is undefined. Possibly this is being called too early.
        function export_file() {
    		if( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $form_fields ) ) ) {
    			$doorbitch->debug( 'failed at request_filesystem_credentials' );
    			return false;
    		}
    		if ( ! WP_Filesystem( $creds ) ) {
    			request_filesystem_credentials( $url, $method, true, false, $form_fields );
    			$doorbitch->debug( 'failed at WP_Filesystem' );
    			return false;
    		} 
    		if ( ! $csv_data = self::format_csv( $doorbitch, $event ) ) {
    			$doorbitch->debug( 'export failed at formatting csv' );
    			return false;
    		}
    		if (! $wp_filesystem->put_contents( $filename, $csv_data, FS_CHMOD_FILE ) ) {
    			$doorbitch->debug( 'export failed to create file' );
    			return false;
    		}
        }
        add_action( 'admin_init', array( $this, 'export_file' ) );
		return $export_dir . $filename;
	}

    public static function format_csv ( Doorbitch $doorbitch, $event ) {
    	if ( ! $entries = $doorbitch->get_registrants ( $event ) ) {
    		return false;
    	}
    	$csv = 'Event:,' . $event . "\n";

    	// Add headers:
    	foreach ( $entries[0] as $header => $value ) {
    		$csv .= $header . ',' ;
    	}
    	$csv .= "\n";

    	// Add entries:
    	foreach ( $entries as $entry ) {
    		foreach ( $entry as $field => $value ) {
    			$csv .= $value . ',' ;
    		}
    		$csv .= "\n";
    	}

    	return $csv;
    }

    public static function create_spreadsheet ( Doorbitch $doorbitch, $event ) {
        $entries = $doorbitch->get_registrants( $event );
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

        return $spreadsheet;
    }

}
?>