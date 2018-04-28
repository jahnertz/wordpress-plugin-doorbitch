<?php

Class Doorbitch_Virtual_Pages
{
	public function __construct()
	{

		doorbitch::debug_add( 'Initializing VPages' );
		function virtualpage_query_vars ( $vars ) {
			$vars[] = 'virtualpage';
			return $vars;
		}
		add_filter( 'query_vars', 'virtualpage_query_vars' );

		// Add redirects for virtual page urls to index.php?virtualpage=name
		// permalink settings in admin inteface must be saved. This can be done with flush_rewrite_rules() on theme activation.
		function virtualpage_add_rewrite_rules ()
		{
			add_rewrite_tag( '%virtualpage%', '([^&])' );
			add_rewrite_rule(
			  'vp/([^/]*)/?$',
			  'index.php?virtualpage=$matches[1]',
			  'top'
			);
			// An alternative approach.
			// add_rewrite_rule(
			// 	'doorbitch/?$',
			// 	'index.php?virtualpage=doorbitch',
			// 	'top'
			// );
		}
		add_action( 'init', 'virtualpage_add_rewrite_rules' );

		/*
		 * Assign Templates to the virtual pages:
		 */
		function virtualpage_template_include ( $template )
		{
			global $wp_query;
			$new_template = '';

			if ( array_key_exists( 'virtualpage', $wp_query->query_vars ) ) {
				switch ( $wp_query->query_vars['virtualpage'] ) {
					case 'doorbitch':
						$new_template = locate_template( array( plugin_dir_path() . 'templates/doorbitch-form.php' ) );
						break;
				}

				if ( $new_template != '' ) {
					return $new_template;
				}
				else {
					// This is not a valid virtual page: set header and template to 404 page.
					$wp_query->set_404();
					status_header( 404 );
					return get_404_template();
				}
			}

			return template;
		}
		add_filter( 'template_include', 'virtualpage_template_include' );
	}
}