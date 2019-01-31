<?php

class Doorbitch_Admin {

    private $options;

    public  $visible_event = '';
    private $new_event;
    private $del_event;
    private $export_flag;
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
        if( $_POST ) {
        if( array_key_exists( 'action', $_POST ) )
        {
            switch ( $_POST[ 'action' ] ) {
                case 'view':
                    check_admin_referer( 'doorbitch_view_export_nonce' );
                    $this->visible_event = $_POST[ 'event' ];
                    break;
                
                case 'set as current event':
                    check_admin_referer( 'doorbitch_view_export_nonce' );
                    doorbitch::set_current_event( $_POST[ 'event' ] );
                    $this->visible_event = $_POST[ 'event' ];
                    break;
                
                case 'export':
                    check_admin_referer( 'doorbitch_view_export_nonce' );
                    // load export class:
                    require_once( DOORBITCH__PLUGIN_DIR . 'class.doorbitch-export.php' );
                    $doorbitch_export = new Doorbitch_Export();
                    $this->export_flag = true;
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
                    doorbitch::remove_event( $_POST[ 'event' ] );
                    doorbitch::set_current_event();
                    $this->visible_event = $this->options[ 'current_event' ];
                    break;

                case 'create':
                    check_admin_referer( 'doorbitch_view_export_nonce' );
                    if( isset( $_POST[ 'new_event_name' ] ) ) {
                        if( $_POST[ 'new_event_name' ] == '' ) {
                            $this->new_event = true;
                            doorbitch::debug( 'Please enter an event name' );
                            $this->visible_event = $_POST[ 'event' ];
                        }
                        else {
                            doorbitch::add_event( $_POST[ 'new_event_name' ] );
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
                                            <option value="<?php echo $event;?>" <?php if( $event == $this->visible_event ) { echo 'selected="selected"';} ?>"><?php echo $event; ?></option>
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
                            if( isset( $this->del_event ) ) {
                                ?>
                                <tr>
                                    <td>
                                        <input type="submit" name="action" value="delete this event" id="del-confirm" class="button button-primary">
                                        <input type="submit" name="action" value="cancel" id="del-confirm" class="button button-secondary">
                                    </td>
                                </tr>
                                <?php
                            }
                            if( isset( $this->new_event ) ) {
                                ?>
                                <tr>
                                    <td>
                                        <input type="text" name="new_event_name" value="" placeholder="New Event Name" >
                                        <input type="submit" name="action" value="create" class="button button-primary" >
                                    </td>
                                </tr>
                                <?php
                            }
                            if( $this->export_flag ) {
                                doorbitch::debug( 'calling export_records ');
                                $exported_file = doorbitch_export::export_records( $_POST[ 'event' ] );
                                ?>
                                <tr>
                                    <td>
                                        <p>
                                            <?php
                                            doorbitch::debug( 'exporting' );
                                            if ( $exported_file ) {
                                                printf('<a href="%s">%s</a>',
                                                $exported_file,
                                                basename( $exported_file ) );
                                            } else {
                                                echo( 'There was an error exporting the spreadsheet.' );
                                            }
                                            ?>
                                        </p>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    </form>
                    <?php 
                    if( $this->visible_event == '' ) {
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
            'confirmation_email',
            'Send Confirmation Email to Registrant',
            array ( $this, 'confirmation_email_callback' ),
            'doorbitch-settings-admin',
            'options-section'
        );

        add_settings_field (
            'confirmation_email_use_html',
            'Use HTML in Email',
            array ( $this, 'confirmation_email_use_html_callback' ),
            'doorbitch-settings-admin',
            'options-section'
        );

        add_settings_field(
            'confirmation_email_from',
            'Confimation Email From Address',
            array ( $this, 'confirmation_email_from_callback' ),
            'doorbitch-settings-admin',
            'options-section'
        );

        add_settings_field(
            'confirmation_email_subject',
            'Confimation Email Subject',
            array ( $this, 'confirmation_email_subject_callback' ),
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

        add_settings_field (
            'confirmation_email_content',
            'Confirmation Email Content',
            array ( $this, 'confirmation_email_content_callback' ),
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
     */
    public function sanitize_callback( $input )
    {
        if( isset( $input['initiated'] ) )
            $sanitized_input['initiated'] = sanitize_text_field( $input['initiated'] );

        if( isset( $input['db_version'] ) )
            $sanitized_input['db_version'] = sanitize_text_field( $input['db_version'] );

        if( isset( $input['events'] ) )
            $sanitized_input['events'] = sanitize_text_field( unserialize( $input['events'] ) );

        if( isset( $input['current_event'] ) )
            $sanitized_input['current_event'] = sanitize_text_field( $input['current_event'] );

        if( isset( $input['form_url'] ) )
            $sanitized_input['form_url'] = sanitize_text_field( $input[ 'form_url' ] );

        if( isset( $input[ 'require_auth' ] ) ) {
            $sanitized_input[ 'require_auth' ] = $input[ 'require_auth' ];
        } else {
            $sanitized_input[ 'require_auth' ] = 0;
        }

        if( isset( $input[ 'confirmation_email' ] ) ) {
            $sanitized_input[ 'confirmation_email' ] = $input[ 'confirmation_email' ];
        } else {
            $sanitized_input[ 'confirmation_email' ] = 0;
        }

        if( isset( $input['confirmation_email_use_html'] ) ) {
            $sanitized_input[ 'confirmation_email_use_html'] = $input[ 'confirmation_email_use_html'];
        } else {
            $sanitized_input[ 'confirmation_email_use_html' ] = 0;
        }

        if( isset( $input[ 'confirmation_email_from' ] ) ) {
            $sanitized_input[ 'confirmation_email_from' ] = sanitize_text_field( $input[ 'confirmation_email_from' ] );
        }

        if( isset( $input[ 'confirmation_email_subject' ] ) ) {
            $sanitized_input[ 'confirmation_email_subject' ] = sanitize_text_field ( $input[ 'confirmation_email_subject' ] );
        }

        if( isset( $input['form_html'] ) )
            $sanitized_input['form_html'] = wp_kses( $input['form_html'], $this->expanded_allowed_tags() );

        if( isset( $input['confirmation_email_content'] ) )
            $sanitized_input['confirmation_email_content'] = wp_kses( $input['confirmation_email_content'], $this->expanded_allowed_tags() );

        if( isset( $input[ 'debug_mode' ] ) ) {
            $sanitized_input[ 'debug_mode' ] = $input[ 'debug_mode' ];
        } else {
            $sanitized_input[ 'debug_mode' ] = 0;
        }
        // TODO: create list of required fields and save it to options.

        return $sanitized_input;
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
        $default_form_url = doorbitch::default_form_url;
        printf(
            '%s/<input type="text" id="form_url" name="doorbitch_options[form_url]" value="%s" />',
            get_site_url(),
            isset( $this->options['form_url'] ) ? esc_attr( $this->options['form_url'] ) : $default_form_url
        );
    }

    public function require_auth_callback()
    {
        if( isset ( $this->options[ 'require_auth' ] ) && $this->options[ 'require_auth' ] ) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        printf(
            '<input type="checkbox" id="require_auth" name="doorbitch_options[require_auth]" %s />',
            $checked
        );
    }

    public function confirmation_email_callback ()
    {
        if( isset ( $this->options [ 'confirmation_email' ] ) ) {
            if( $this->options[ 'confirmation_email'] ) { $checked = 'checked="checked"'; } else { $checked= ''; }
        } else { 
            $checked = '';
        }
        printf(
            '<input type="checkbox" id="confirmation_email" name="doorbitch_options[confirmation_email]" %s />',
            $checked
        );
    }

    public function confirmation_email_use_html_callback ()
    {
        if( isset( $this->options[ 'confirmation_email_use_html' ] ) && $this->options[ 'confirmation_email_use_html' ] ) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        printf(
            '<input type="checkbox" id="confirmation_email_use_html" name="doorbitch_options[confirmation_email_use_html]" %s />', $checked
        );
    }

    public function confirmation_email_from_callback ()
    {
        $default = "no-reply@" . preg_replace( "/^https*\:\/\/(www)*/", "", get_home_url());
        printf(
            '<input type="text" id="confirmation_email_from" name="doorbitch_options[confirmation_email_from]" value="%s" />',
            isset( $this->options['confirmation_email_from'] ) ? esc_attr( $this->options['confirmation_email_from'] ) : $default
            );
    }

    public function confirmation_email_subject_callback ()
    {
        $default = "Thank you for registering!";
        printf (
            '<input type="text" id="confirmation_email_subject" name="doorbitch_options[confirmation_email_subject]" value="%s" />',
            isset( $this->options[ 'confirmation_email_subject' ] ) ? esc_attr( $this->options[ 'confirmation_email_subject' ] ) : $default
        );
    }

    public function form_html_callback()
    {
        $content = isset( $this->options[ 'form_html' ] ) ? $this->options[ 'form_html' ] : file_get_contents( DOORBITCH__PLUGIN_DIR . '/forms/default.html' );
        $wp_editor_settings = array(
            'media_buttons' => true,
            'textarea_name' => 'doorbitch_options[form_html]'
        );
        wp_editor( $content, 'form-html', $wp_editor_settings );
    }

    public function confirmation_email_content_callback ()
    {
        $content = isset( $this->options[ 'confirmation_email_content' ] ) ? $this->options[ 'confirmation_email_content' ] : file_get_contents(DOORBITCH__PLUGIN_DIR . '/email_templates/default.html' );
        $wp_editor_settings = array(
            'media_buttons' => true,
            'textarea_name' => 'doorbitch_options[confirmation_email_content]'
        );
        wp_editor( $content, 'confirmation_email_content', $wp_editor_settings );
    }

    public function debug_mode_callback()
    {
        if( isset ( $this->options[ 'debug_mode' ] ) && $this->options[ 'debug_mode' ] ) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        printf(
            '<input type="checkbox" id="debug_mode" name="doorbitch_options[debug_mode]" %s />',
            $checked
        );
    }


    private function display_records( $event ) {
        $entries = doorbitch::get_registrants( $event );
        ?>
        <table class="doorbitch-records">
            <?php
            if( empty( $entries ) ){
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
        // TODO: This is dropping attributes for input and other tags!
        // general:
        $permitted_attrs = array(
            'id' => array(), 'class' => array(), 'style' => array(),
        );
        $permitted_tags = array( 'h1','h2','h3','h4','h5','h6','p','ul','ol','li','i','b','span','br','label','input','select','option','table','tr','td','img' );
        $allowed = array_fill_keys( $permitted_tags, $permitted_attrs );

        // tag specific:
        $allowed[ 'label' ][] = array( 
            'for' => array(),
        );
        $allowed[ 'input' ][] = array(
            'class' => array(), 'id' => array(), 'name' => array(), 'value' => array(), 'type' => array(),
        );
        $allowed[ 'select' ][] = array(
            'class' => array(), 'id' => array(), 'name' => array(), 'value' => array(), 'type' => array(),
        );
        $allowed[ 'table' ][] = array(
            'class' => array(), 'id' => array(), 'border' => array(), 'cellpadding' => array(), 'cellspacing' => array(), 'width' => array(), 'bgcolor' => array(),
        );
        $allowed[ 'td' ][] = array(
            'colspan' => array(),
        );
        $allowed [ 'img' ][] = array (
            'src' => array(), 'title' => array(), 'width' => array(), 'height' => array(), 'alt' => array(),
        );
        return $allowed;
    }
}
