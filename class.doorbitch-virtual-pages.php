<?php

Class Doorbitch_Virtual_Pages
{
	public function __construct()
	{

		// doorbitch::debug( 'Initializing VPages' );
		function virtualpage_query_vars ( $vars ) {
			$vars[] = 'virtualpage';
			return $vars;
		}
		add_filter( 'query_vars', 'virtualpage_query_vars' );

		// Add redirects for virtual page urls to index.php?virtualpage=name
		// permalink settings in admin inteface must be saved. This can be done with flush_rewrite_rules() on theme activation.
		function virtualpage_add_rewrite_rules ()
		{
			// doorbitch::debug( 'Adding rewrite rules.' );
			add_rewrite_tag( '%virtualpage%', '([^&])' );
			add_rewrite_rule(
				'doorbitch/([^/]*)/?$',
				'index.php?virtualpage=$matches[1]',
				'top'
				// an alternative approach.
				// 'doorbitch/?$',
				// 'index.php?virtualpage=doorbitch',
				// 'top'
			);
		}
		add_action( 'init', 'virtualpage_add_rewrite_rules' );
		
		/*
		 * Assign Templates to the virtual pages:
		 */
		function virtualpage_template_include ( $template )
		{
			// doorbitch::debug( 'Adding virtual page templates' );
			global $wp_query;
			$new_template = '';

			if ( array_key_exists( 'virtualpage', $wp_query->query_vars ) ) {
				switch ( $wp_query->query_vars['virtualpage'] ) {
					// TODO: generalise this and make a function to register virtual pages;
					case 'registration':
						// check the required privileges for the page and redirect if necessary:
						if ( !current_user_can( 'edit_posts' ) ){ auth_redirect(); }
						$new_template = plugin_dir_path( __FILE__ ) . 'templates/doorbitch-frontend.php';
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

			return $template;
		}
		add_filter( 'template_include', 'virtualpage_template_include' );
	}
}