<?php

Class Doorbitch_Router
{

	public function __construct()
	{
		doorbitch::debug( 'Initializing Router' );

		function doorbitch_router_query_vars ( $vars ) {
			$vars[] = 'virtualpage';
			return $vars;
		}
		add_filter( 'query_vars', 'doorbitch_router_query_vars' );

		// Add redirects for virtual page urls to index.php?virtualpage=name
		// permalink settings in admin inteface must be saved. This can be done with flush_rewrite_rules() on theme activation.
		function add_rewrite_rules ()
		{
			// doorbitch::debug( 'Adding rewrite rules.' );
			add_rewrite_tag( '%virtualpage%', '([^&])' );
			add_rewrite_rule(
				'([^/]*)/?$',
				'index.php?virtualpage=$matches[1]',
				'top'
				// an alternative approach.
				// 'doorbitch/?$',
				// 'index.php?virtualpage=doorbitch',
				// 'top'
			);
		}
		add_action( 'init', 'add_rewrite_rules' );
		
		// Assign Templates to the virtual pages:
		function virtualpage_template_include ( $template )
		{
			// doorbitch::debug( 'Adding virtual page templates' );
			global $wp_query;
			$new_template = '';

			$options = get_option( DOORBITCH__OPTIONS );
			$route = $options[ 'form_url' ];

			if ( array_key_exists( 'virtualpage', $wp_query->query_vars ) ) {
				switch ( $wp_query->query_vars['virtualpage'] ) {
					case $route:
						// check the required privileges for the page and redirect if necessary:
						if ( !current_user_can( 'edit_posts' ) ){ auth_redirect(); }
						$new_template = plugin_dir_path( __FILE__ ) . 'templates/doorbitch-frontend.php';
						// Hide the admin bar:
						add_filter( 'show_admin_bar', '__return_false' );
						function set_title() {
							return "Registration";
						}
						add_filter( 'wp_title', 'set_title' );
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