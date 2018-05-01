<?php
/**
 * The template for displaying pages
 *
 * @package WordPress
 * @subpackage Doorbitch
 * @since Doorbitch 0.0.2
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<div class="page-content">
				<!-- <h1>Doorbitch</h1> -->
				<?php 
				// require_once ( plugin_dir_path( __FILE__ ) . '../forms/doorbitch-form.php' ); 
				$form = get_option( 'bitch_frontend_form' );
				if ( $form == '' ) {
					update_option( 'bitch_frontend_form', file_get_contents( plugin_dir_path( __FILE__ ) . '../forms/doorbitch-form.php' ) );
					$form = get_option( 'bitch_frontend_form' );
				}
				echo $form;

				?>
			</div>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
