<?php

Class Doorbitch_Router
{

	public function __construct()
	{
		Doorbitch::debug('***Initializing router***');
		function doorbitch_router_query_vars ( $vars ) {
			doorbitch::debug( 'adding query vars' );
			$vars[] = 'virtualpage';
			return $vars;
		}
		add_filter( 'query_vars', 'doorbitch_router_query_vars' );

		// Add redirects for virtual page urls to index.php?virtualpage=name
		// permalink settings in admin inteface must be saved. this is done by calling flush_rewrite_rules() on theme activation.
		function add_rewrite_rules ()
		{
			doorbitch::debug( 'Adding rewrite rules.' );
			add_rewrite_tag( '%virtualpage%', '([^&])' );
			add_rewrite_rule(
				'([^/]*)/?$',
				'index.php?virtualpage=$matches[1]',
				'top'
			);
		}
		add_action( 'init', 'add_rewrite_rules' );
		
		// Assign Templates to the virtual pages:
		function virtualpage_template_include ( $template )
		{
			doorbitch::debug( 'Adding virtual page templates' );
			global $wp_query;
			$new_template = '';

			$options = get_option( DOORBITCH__OPTIONS );
			$route = $options[ 'form_url' ];
			doorbitch::debug( 'Route: ' . get_site_url() . '/' . $route );

			if ( array_key_exists( 'virtualpage', $wp_query->query_vars ) ) {
				doorbitch::debug( 'virtualpage key exists in $wp_query->query_vars' );
				switch ( $wp_query->query_vars['virtualpage'] ) {
					case $route:
						// check if requiring auth is set and redirect if necessary.
						if ( $options[ 'require_auth' ] && !current_user_can( 'edit_posts' )) {
							auth_redirect();
							break;
						}

						$new_template = plugin_dir_path( __FILE__ ) . 'templates/doorbitch-frontend.php';
						// Hide the admin bar:
						// TODO: get this from options.
						add_filter( 'show_admin_bar', '__return_false' );
						function set_title() {
							// TODO: get this from options.
							return "Registration";
						}
						add_filter( 'wp_title', 'set_title' );
						break;
				}

				if ( $new_template != '' ) {
					return $new_template;
				}
				else {
					doorbitch::debug( 'Not a valid route, redirecting to 404');
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