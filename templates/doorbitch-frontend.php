<?php
/**
 * The template for displaying the frontend form.
 *
 * @package WordPress
 * @subpackage Doorbitch
 * @since Doorbitch 0.0.2
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div class="page-content">
			<div class="panel-content">
				<div class="wrap">
					<!-- <h1>Doorbitch</h1> -->
					<header class="entry-header">
						<h2 class="entry-title"><?php //todo: use option for header ?>Registration</h2>
					</header><!-- Page Header -->
					<div class="entry-content">
						<form action="../form-submit.php" method="get">
							<?php 
							// require_once ( plugin_dir_path( __FILE__ ) . '../forms/doorbitch-form.php' ); 
							$form = get_option( 'bitch_frontend_form' );
							if ( $form == false || $form == '' ) {
								update_option( 'bitch_frontend_form', file_get_contents( plugin_dir_path( __FILE__ ) . '../forms/default.php' ) );
								$form = get_option( 'bitch_frontend_form' );
							}
							echo $form;

							?>
						</form>
					</div>
				</div>
			</div>
		</div>

	</main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer(); ?>
