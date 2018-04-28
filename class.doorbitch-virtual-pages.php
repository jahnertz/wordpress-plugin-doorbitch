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
				'vp/([^/]*)/?$',
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
				// TODO: dynamically add virtualpages according to the plugin's existing templates
				switch ( $wp_query->query_vars['virtualpage'] ) {
					case 'doorbitch':
						$new_template = plugin_dir_path( __FILE__ ) . 'templates/doorbitch-form.php';
						doorbitch::debug( 'Including doorbitch template:' . $new_template );
						break;
				}

				if ( $new_template != '' ) {
					doorbitch::debug( 'Using vpage template' );
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