<?php

class Doorbitch_Export {

	public static function export_records( $event ) {
		global $doorbitch;
		global $wp_filesystem;
		doorbitch::debug( 'Exporting records for ' . $event );
		$export_dir = trailingslashit( DOORBITCH__PLUGIN_DIR . 'export' );
		doorbitch::debug( 'Export Dir: ' . $export_dir );
		$filename = preg_replace( '/\s/', '_', $event )
			. '_'
			. current_time( 'Y-m-d_Hi' )
			. '.csv';
		$spreadsheet = doorbitch::create_spreadsheet( $event );
		$url = wp_nonce_url( 'tools.php?page=doorbitch-settings-admin', 'doorbitch-settings-admin');
		$method = '';
        $form_fields = array ( 'event', 'action' );
		if( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $form_fields ) ) ) {
			doorbitch::debug( 'failed at request_filesystem_credentials' );
			return false;
		}
		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( $url, $method, true, false, $form_fields );
			doorbitch::debug( 'failed at WP_Filesystem' );
			return false;
		} 
		if ( ! $csv_data = doorbitch::format_csv( $event ) ) {
			doorbitch::debug( 'export failed at formatting csv' );
			return false;
		}
		if (! $wp_filesystem->put_contents( $filename, $csv_data, FS_CHMOD_FILE ) ) {
			doorbitch::debug( 'export failed to create file' );
			return false;
		}
		return $export_dir . $filename;
	}

  //   public static function export_records( $event, $format ) {
		// $export_dir = trailingslashit( DOORBITCH__PLUGIN_DIR . 'export' );
		// $export_dir_url = trailingslashit( DOORBITCH__PLUGIN_DIR_URL . 'export' );
		// $temp_dir = trailingslashit( sys_get_temp_dir() );
		// $filename = preg_replace( '/\s/', '-', $event ) . '_' . current_time( 'Y-m-d_Hi') . '.' . $format;
		// $filepath = $export_dir . $filename;
		// $temp_filepath = $temp_dir . $filename;

  //   	switch ( $format ) {
  //   		case 'xlsx':
		//     	if ( ! $spreadsheet = doorbitch::create_spreadsheet( $event ) ) return false;

		//         $writer = new Xlsx( $spreadsheet );
		//         $writer->save( $temp_filepath );

		//         global $wp_filesystem;

		//         //create the export directory if it doesn't already exist:
		//         if ( ! file_exists( $export_dir ) ) { $wp_filesystem->mkdir( $export_dir ); }
		//         $wp_filesystem->move( $temp_filepath, $filepath );

		//         return $export_dir_url . $filename;
  //   			break;

  //   		case 'csv':
  //   			if ( ! $csv_data = doorbitch::format_csv ( $event ) ) {
  //   				return false;
  //   			}

  //   			global $wp_filesystem;

  //   			if (! $wp_filesystem->put_contents( $filepath, $csv_data, FS_CHMOD_FILE ) ) {
  //   				echo "error saving csv file.";
  //                   break;
  //   			}
  //   			return $export_dir_url . $filename;
  //   			break;
  //   	}
  //   }

    public static function format_csv ( $event ) {
    	global $doorbitch;
    	if ( ! $entries = doorbitch::get_registrants ( $event ) ) {
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

    public static function create_spreadsheet ( $event ) {
        $entries = doorbitch::get_registrants( $event );
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