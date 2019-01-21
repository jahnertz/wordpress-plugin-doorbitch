<?php

class Doorbitch_Admin
{
    private $options;

    public  $visible_event = '';
    public  $exported_file;
    private $new_event;
    private $del_event;
    private $export;
    /**
     * Start up
     */
    public function init ()
    {
        global $doorbitch;

        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'add_plugin_settings_page' ) );
        $this->options = get_option( DOORBITCH__OPTIONS );

        // Deal with _POST data
        if ( $_POST ) {
            // show all _POST data in debug area:
            // TODO: this breaks because it's trying to convert an array to a string.
            // foreach ( $_POST as $key => $value) {
            //     Doorbitch::debug( $key . ': ' . $value );
            // }
        // check_admin_referer( 'doorbitch-settings-admin' );
        if ( array_key_exists( 'action', $_POST ) )
        {
            switch ( $_POST[ 'action' ] ) {
                //TODO: clean this up.
                case 'view':
                    check_admin_referer( 'doorbitch_view_export_nonce' );
                    $this->visible_event = $_POST[ 'event' ];
                    break;
                
                case 'set as current event':
                    check_admin_referer( 'doorbitch_view_export_nonce' );
                    Doorbitch::set_current_event( $_POST[ 'event' ] );
                    $this->visible_event = $_POST[ 'event' ];
                    break;
                
                case 'export':
                    // check_admin_referer( 'doorbitch_view_export_nonce' );
                    $this->visible_event = $_POST[ 'event' ];
                    break;

                case 'new event':
                    check_admin_referer( 'doorbitch_view_export_nonce' );
                    $this->new_event = true;
                    $this->visible_event = $_POST[ 'event' ];
                    break;

                case 'delete':
                    check_admin_referer( 'doorbitch_view_export_nonce' );
                    $this->del_event = true;
                    $this->visible_event = $_POST[ 'event' ];
                    break;

                case 'delete this event':
                    check_admin_referer( 'doorbitch_view_export_nonce' );
                    Doorbitch::remove_event( $_POST[ 'event' ] );
                    Doorbitch::set_current_event();
                    $this->visible_event = $this->options[ 'current_event' ];
                    break;

                case 'create':
                    check_admin_referer( 'doorbitch_view_export_nonce' );
                    if ( isset( $_POST[ 'new_event_name' ] ) ) {
                        if ( $_POST[ 'new_event_name' ] == '' ) {
                            $this->new_event = true;
                            Doorbitch::debug( 'Please enter an event name' );
                            $this->visible_event = $_POST[ 'event' ];
                        }
                        else {
                            Doorbitch::add_event( $_POST[ 'new_event_name' ] );
                            $this->visible_event = $_POST[ 'new_event_name' ];
                        }
                    }
                    break;

                }
            } else {
                $this->visible_event = $this->options[ 'current_event' ];
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
        if ( $this->check_fs_creds() ) return;
        $this->options = get_option( DOORBITCH__OPTIONS );
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
                    <h3>Current Event: <i><?php echo $this->options[ 'current_event' ] ?></i></h3>
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
                                        $current_event = $this->options[ 'current_event' ];
                                        $event_array = unserialize( $this->options[ 'events' ] );
                                        foreach ( $event_array as $event) {
                                            ?>
                                            <option value="<?php echo $event;?>" <?php if ( $event == $this->visible_event ) { echo 'selected="selected"';} ?>"><?php echo $event; ?></option>
                                            <?php
                                        }
                                       ?> 
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php wp_nonce_field( 'doorbitch_view_export_nonce' ); ?>
                                    <input type="submit" name="action" value="view" class="button button-secondary">
                                    <input type="submit" name="action" value="export" class="button button-secondary">
                                    <input type="submit" name="action" value="set as current event" class="button button-secondary">
                                    <input type="submit" name="action" value="new event" class="button button-secondary">
                                    <input type="submit" name="action" value="delete" class="button button-secondary">
                                </td>
                            </tr>
                            <?php
                            if ( isset( $this->del_event ) ) {
                                ?>
                                <tr>
                                    <td>
                                        <input type="submit" name="action" value="delete this event" id="del-confirm" class="button button-primary">
                                        <input type="submit" name="action" value="cancel" id="del-confirm" class="button button-secondary">
                                    </td>
                                </tr>
                                <?php
                            }
                            if ( isset( $this->new_event ) ) {
                                ?>
                                <tr>
                                    <td>
                                        <input type="text" name="new_event_name" value="" placeholder="New Event Name" >
                                        <input type="submit" name="action" value="create" class="button button-primary" >
                                    </td>
                                </tr>
                                <?php
                            }
                            if ( isset( $this->exported_file ) && $this->exported_file == false ) {
                                ?>
                                <tr>
                                    <td>
                                        <p>There was an error exporting the spreadsheet.</p>
                                    </td>
                                </tr>
                                <?php
                            }
                            if ( isset( $this->exported_file ) ) {
                                ?>
                                <tr>
                                    <td>
                                        <?php
                                        printf(
                                            '<a href=%s alt="exported file">%s</a>',
                                            $this->exported_file,
                                            basename( $this->exported_file )
                                        );
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </form>
                    <?php 
                    if ( $this->visible_event == '' ) {
                        $this->visible_event = $this->options[ 'current_event' ];
                    }
                    echo "<h3>" . $this->visible_event . "</h3>";
                    $this->display_records( $this->visible_event );
                    break;
                
                default:
                    ?>
                    <form method="post" action="options.php" enctype="multipart/form-data">
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

    public function check_fs_creds () {
        if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'export' ) {
            $url = wp_nonce_url( 'tools.php?page=doorbitch-settings-admin' );
            $method = '';
            $form_fields = array ( 'event', 'action' );

            if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $form_fields ) ) ) {
                return true;
            }

            if ( ! WP_Filesystem( $creds ) ) {
                request_filesystem_credentials( $url, $method, true, false, $form_fields );
                return true;
            }
            $this->exported_file = Doorbitch::export_records( $_POST[ 'event' ], 'csv' );
        }

    }

    /**
     * Register and add settings
     */
    public function add_plugin_settings_page()
    {        
        register_setting(
            'doorbitch_options_group', // Option group
            'doorbitch_options', // Option name
            array( $this, 'sanitize_callback' ) // Sanitize
        );

        add_settings_section(
            'options-section', // ID
            'Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'doorbitch-settings-admin' // Page
        );  

        add_settings_field(
            'initiated',
            'Initiated',
            array( $this, 'initiated_callback' ),
            'doorbitch-settings-admin',
            'options-section',
            [ 'class' => 'hidden' ]
        );

        add_settings_field(
            'db_version',
            'DB_Version',
            array( $this, 'db_version_callback' ),
            'doorbitch-settings-admin',
            'options-section',
            [ 'class' => 'hidden' ]
        );

        add_settings_field(
            'events',
            'Events',
            array( $this, 'events_callback' ),
            'doorbitch-settings-admin',
            'options-section',
            [ 'class' => 'hidden' ]
        );

        add_settings_field(
            'current_event',
            'Current Event',
            array( $this, 'current_event_callback' ),
            'doorbitch-settings-admin',
            'options-section',
            [ 'class' => 'hidden' ]
        );

        /*/
        /* Settings tab:
        /*/

        add_settings_field(
            'form_url',
            'Form URL',
            array( $this, 'form_url_callback'),
            'doorbitch-settings-admin',
            'options-section'
        );

        add_settings_field(
            'require_auth',
            'Require Login',
            array( $this, 'require_auth_callback' ),
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

        add_settings_field(
            'debug_mode', 
            'Debug Mode', 
            array( $this, 'debug_mode_callback' ), 
            'doorbitch-settings-admin', 
            'options-section'
        );      

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_callback( $input )
    {
        if( isset( $input['initiated'] ) )
            $new_input['initiated'] = sanitize_text_field( $input['initiated'] );

        if( isset( $input['db_version'] ) )
            $new_input['db_version'] = sanitize_text_field( $input['db_version'] );

        if( isset( $input['events'] ) )
            $new_input['events'] = sanitize_text_field( unserialize( $input['events'] ) );

        if( isset( $input['current_event'] ) )
            $new_input['current_event'] = sanitize_text_field( $input['current_event'] );

        if( isset( $input['form_url'] ) )
            $new_input['form_url'] = sanitize_text_field( $input[ 'form_url' ] );

        if( isset( $input[ 'require_auth' ] ) ) {
            $new_input[ 'require_auth' ] = $input[ 'require_auth' ];
        } else {
            $new_input[ 'require_auth' ] = 0;
        }

        if( isset( $input['form_html'] ) )
            $new_input['form_html'] = wp_kses( $input['form_html'], $this->expanded_allowed_tags() );

        if( isset( $input[ 'debug_mode' ] ) ) {
            $new_input[ 'debug_mode' ] = $input[ 'debug_mode' ];
        } else {
            $new_input[ 'debug_mode' ] = 0;
        }
        // TODO: create list of required fields and save it to options.

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    public function db_version_callback()
    {
        printf(
            '<input type="text" id="db_version" name="doorbitch_options[db_version]" value="%s" />',
            isset( $this->options['db_version'] ) ? esc_attr( $this->options['db_version'] ) : ''
        );
    }

    public function initiated_callback()
    {
        printf(
            '<input type="text" id="initiated" name="doorbitch_options[initiated]" value="%s" />',
            isset( $this->options['initiated'] ) ? esc_attr( $this->options['initiated'] ) : ''
        );
    }

    public function events_callback()
    {
        printf(
            '<input type="text" id="events" name="doorbitch_options[events]" value="%s" />',
            isset( $this->options['events'] ) ? esc_attr( serialize( $this->options['events'] ) ) : ''
        );
    }

    public function current_event_callback()
    {
        printf(
            '<input type="text" id="current_event" name="doorbitch_options[current_event]" value="%s" />',
            isset( $this->options['current_event'] ) ? esc_attr( $this->options['current_event'] ) : ''
        );
    }

    public function form_url_callback()
    {
        global $doorbitch;
        $default_form_url = Doorbitch::default_form_url;
        printf(
            '%s/<input type="text" id="form_url" name="doorbitch_options[form_url]" value="%s" />',
            get_site_url(),
            isset( $this->options['form_url'] ) ? esc_attr( $this->options['form_url'] ) : $default_form_url
        );
    }

    public function require_auth_callback()
    {
        if ( $this->options[ 'require_auth' ] ) { $checked = 'checked="checked"'; } else { $checked = ''; }
        printf(
            '<input type="checkbox" id="require_auth" name="doorbitch_options[require_auth]" %s />',
            $checked
        );
    }

    public function form_html_callback()
    {
        $wp_editor_settings = array(
            'media_buttons' => true,
            'textarea_name' => 'doorbitch_options[form_html]'
        );
        wp_editor( $this->options[ 'form_html' ], 'form-html', $wp_editor_settings );
    }

    public function debug_mode_callback()
    {
        if ( $this->options[ 'debug_mode' ] ) { $checked = 'checked="checked"'; } else { $checked = ''; }
        printf(
            '<input type="checkbox" id="debug_mode" name="doorbitch_options[debug_mode]" %s />',
            $checked
        );
    }


    private function display_records( $event ) {
        $entries = Doorbitch::get_registrants( $event );
        ?>
        <table class="doorbitch-records">
            <?php
            if ( empty( $entries ) ){
                ?>
                <h4>No registrants yet.</h4>
                <?php
            }
            else {
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
            }
            ?>
        </table>
        <?php
    }


    private function expanded_allowed_tags() {
        // formatting tags:
        $allowed[ 'h1' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'h2' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'h3' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'h4' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'h5' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'h6' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'p' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'ul' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'ol' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'li' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'i' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'b' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'span' ] = array (
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $allowed[ 'br' ] = array ();
        
        // form fields:
        $allowed[ 'label' ] = array (
            'for' => array(), 'class' => array(),
        );
        $allowed[ 'input' ] = array (
            'class' => array(), 'id' => array(), 'name' => array(), 'value' => array(), 'type' => array(),
        );
        $allowed[ 'select' ] = array (
            'class' => array(), 'id' => array(), 'name' => array(), 'value' => array(), 'type' => array(),
        );
        $allowed[ 'option' ] = array (
            'selected' => array(),
        );
        $allowed[ 'style' ] = array (
            'types' => array(),
        );

        // table fields:
        $allowed [ 'table' ] = array (
            'class' => array(), 'id' => array(),
        );
        $allowed [ 'tr' ] = array (
            'class' => array(), 'id' => array(),
        );
        $allowed [ 'td' ] = array (
            'class' => array(), 'id' => array(), 'colspan' => array(),
        );

        // Image fields:
        $allowed [ 'img' ] = array (
            'class' => array(), 'id' => array(), 'src' => array(), 'title' => array(), 'width' => array(), 'height' => array(), 'alt' => array(),
        );

        return $allowed;
    }
}
