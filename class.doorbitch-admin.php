<?php
// include PhpSpreadsheet library:
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsl;

class Doorbitch_Admin
{
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
                                $events = $options[ 'events' ];
                                foreach ( $events as $event) {
                                    ?>
                                    <option value="<?php echo $event ?>"><?php echo $event?></option>
                                    <?php
                                }
                               ?> 
                            </select>
                        </td>
                        <td>
                            <input type="submit" value="export">
                        </td>
                        </table>
                    </form>
                    <?php
                    $this->display_records( $options[ 'current_event' ] );
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
        global $wpdb;
        ?>
        <table class="doorbitch-records">
            <?php
            // Show data:
            $results = $wpdb->get_results ( "SELECT * FROM {$wpdb->prefix}doorbitch WHERE event='{$event}'" );
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
