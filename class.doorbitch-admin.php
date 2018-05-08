<?php
// include PhpSpreadsheet library:
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsl;

class Doorbitch_Admin
{
    public static $visible_event = '';
    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'add_plugin_settings_page' ) );
        // Deal with _POST data
        if ( array_key_exists( 'action', $_POST ) ) {
        switch ( $_POST[ 'action' ] ) {
            case 'view':
                doorbitch::debug( 'viewing' );
                self::$visible_event = $_POST[ 'event' ];
                break;
            
            case 'select':
                doorbitch::debug( 'selecting' );
                doorbitch::set_current_event( $_POST[ 'event' ] );
                break;
            
            case 'export':
                doorbitch::debug( 'exporting' );
                break;

            default:
                # code...
                break;
            }
        }
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
        $options = doorbitch::get_options();
        ?>
        <div class="wrap">
            <?php
            $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'export';
            ?>
            <h2 class="nav-tab-wrapper">
                <a href="?page=doorbitch-settings-admin&tab=export" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>">View &amp; Export</a>
                <a href="?page=doorbitch-settings-admin&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
            </h2>
            <?php
            switch ( $active_tab ) {
                case 'export':
                    ?>
                    <h3>Current Event: <i><?php echo $options[ 'current_event' ] ?></i></h3>
                    <form method="post" action="" id="export">
                        <table>
                            <tr>
                                <td>
                                    <label for="event">Event</label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="event">
                                       <?php
                                        $current_event = $options[ 'current_event' ];
                                        foreach ( $options[ 'events' ] as $event) {
                                            ?>
                                            <option value="<?php echo $event;?>" <?php if ( $event == $current_event ) { echo 'selected';} ?>"><?php echo $event; ?></option>
                                            <?php
                                        }
                                       ?> 
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="submit" name="action" value="view" class="button button-secondary">
                                    <input type="submit" name="action" value="select" class="button button-secondary">
                                    <input type="submit" name="action" value="export" class="button button-secondary">
                                </td>
                            </tr>
                        </table>
                    </form>
                    <?php 
                    if ( self::$visible_event == '' ) {
                        self::$visible_event = $options[ 'current_event' ];
                    }
                    echo "<h3>" . self::$visible_event . "</h3>";
                    $this->display_records( self::$visible_event );
                    break;
                
                default:
                    ?>
                    <form method="post" action="options.php">
                        <?php
                            settings_fields( 'doorbitch_options_group' );
                            do_settings_sections( 'doorbitch-settings-admin' );
                            submit_button();
                        ?>
                    </form>
                    <?php
                    break;
                }
            ?>
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
            'options-section', // ID
            'Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'doorbitch-settings-admin' // Page
        );  

        add_settings_field(
            'title', 
            'Title', 
            array( $this, 'title_callback' ), 
            'doorbitch-settings-admin', 
            'options-section'
        );      

        add_settings_field(
            'form_html', 
            'Form HTML', 
            array( $this, 'form_html_callback' ), 
            'doorbitch-settings-admin', 
            'options-section'
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        if( isset( $input['form_html'] ) )
            $new_input['form_html'] = sanitize_text_field( $input['form_html'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="doorbitch_options[title]" value="%s" />',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title'] ) : ''
        );
    }

    public function form_html_callback()
    {
        // TODO: html is not being saved to optoions properly
        printf(
            '<textarea id="form-html" rows=20 name="doorbitch_options[form_html]">%s</textarea>',
            isset( $this->options['form_html'] ) ? esc_attr( htmlspecialchars_decode( $this->options['form_html'] ) ) : ''
        );
    }

    private function display_records( $event ) {
        // Todo - seperate this into its own function, move loading database entries into main class - this will make it reusable for exporting.
        global $wpdb;
        // Show data:

        $results = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}doorbitch WHERE event='{$event}'" );
        if ( empty( $results ) ) {
            ?>
            <h4>No registrants yet</h4>
            <?php
        }
        else {
            ?>
            <table class="doorbitch-records">
                <?php
                // Split into 2D array:
                $entries = array();
                foreach( $results as $result ) {
                    $entry = array();
                    // $entry [ 'event' ] = $result->event;
                    $entry [ 'time' ] = $result->time;
                    $data = explode( ',', $result->data );
                    foreach ( $data as $datum ) {
                        $keypair = explode( ':', $datum );
                        $entry[ $keypair[0] ] = $keypair[1];
                    }
                    array_push( $entries, $entry );
                }

                // Create headers:
                ?>
                <tr>
                    <?php foreach ( $entries[0] as $key => $value ) {
                        echo "<th>" . $key . "</th>";
                    }
                    ?>
                </tr>
                <?php
                foreach ( $entries as $entry ) {
                    ?>
                    <tr>
                        <?php foreach ( $entry as $key => $value ) {
                            echo '<td>' . $value . '</td>';
                        }?>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        }
    }

    private function export_records( $event ) {
        global $wpdb;
        $filename = 'Doorbitch-' . $event . current_time( 'Y-m-d_H:i') . 'xlsx';

        // TODO: read the data from the database and write it into the spreadsheet. this should be moved to the main class.
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue( 'A1', 'Hello World!' );

        $writer = new Xlsx( $spreadsheet );
        $writer->save( $filename );
    }
}
