<?php
    register_setting(
    'doorbitch_options_group', // Option group
    'doorbitch_options', // Option name
    array( $this, 'sanitize' ) // Sanitize
    );

    add_settings_section(
    'data-field-section', // ID
    'Data Fields', // Title
    array( $this, 'print_section_info' ), // Callback
    'doorbitch-settings-admin' // Page
    );  

    add_settings_field(
    'id_number', // ID
    'ID Number', // Title 
    array( $this, 'id_number_callback' ), // Callback
    'doorbitch-settings-admin', // Page
    'data-field-section' // Section           
    );      

    add_settings_field(
    'title', 
    'Title', 
    array( $this, 'title_callback' ), 
    'doorbitch-settings-admin', 
    'data-field-section'
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
