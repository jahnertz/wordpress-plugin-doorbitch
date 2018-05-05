<?php
// include PhpSpreadsheet library:
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsl;

class Doorbitch_Admin
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
                    global $wpdb;
                    ?>
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
                                $events = $wpdb->get_results ( "SELECT DISTINCT event FROM {$wpdb->prefix}doorbitch" );
                                foreach ($events as $event) {
                                    ?>
                                    <option value="<?php echo $event->event ?>"><?php echo $event->event ?></option>
                                    <?php
                                }
                               // TODO: retrieve list of events;
                               ?> 
                            </select>
                        </td>
                        <td>
                            <input type="submit" value="export">
                        </td>
                        </table>
                    </form>
                    <?php
                    $this->display_records();
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
            'id_number', // ID
            'ID Number', // Title 
            array( $this, 'id_number_callback' ), // Callback
            'doorbitch-settings-admin', // Page
            'options-section' // Section           
        );      

        add_settings_field(
            'title', 
            'Title', 
            array( $this, 'title_callback' ), 
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

    private function display_records() {
        global $wpdb;
        ?>
        <table class="doorbitch-records">
            <?php
            // Show data:
            $results = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}doorbitch" );
            // Create headers:
            ?>
            <tr>
                <th>event</th>
                <th>date</th>
                <th>name</th>
                <th>age</th>
                <th>comment</th>
            </tr>
            <?php
            foreach ( $results as $result ) {
                ?>
                <tr>
                    <td><?php echo $result->event; ?></td>
                    <td><?php echo $result->time; ?></td>
                    <?php $data = explode( ',', $result->data );
                        foreach ( $data as $datum ) {
                            $keypair = explode( ':', $datum );
                            ?>
                            <td><?php echo $keypair[1]; ?></td>
                            <?php
                        }
                        ?>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }

    private function export_records( $event ) {
        global $wpdb;
        $filename = 'Doorbitch-' . $event . current_time( 'Y-m-d_H:i') . 'xlsx';

        // TODO: read the data from the database and write it into the spreadsheet.
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue( 'A1', 'Hello World!' );

        $writer = new Xlsx( $spreadsheet );
        $writer->save( $filename );
    }
}
