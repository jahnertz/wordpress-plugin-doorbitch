<?php
/**
 * The template for displaying the frontend form.
 *
 * @package WordPress
 * @subpackage Doorbitch
 * @since Doorbitch 0.0.2
 */

get_header(); 
// TODO: redirect user if they are not logged in.
// if ( current_user_can( 'edit_posts' ) )
// {
// 	echo 'user is logged in';
// } else {
// 	auth_redirect();
// }

if ( !empty($_POST) ) {
	global $doorbitch;
	$options = doorbitch::get_options();

	$dataset = '';
	foreach ($_POST as $item => $data ) {
		$dataset = $dataset . $item . ':' . $data . ', ';
	}
	doorbitch::debug( $dataset );
	$success = doorbitch::add_data( $options[ 'current_event' ], $dataset );
} else {
	doorbitch::debug( 'There is no post data' );
}
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div class="page-content">
			<div class="panel-content">
				<div class="wrap">
					<header class="entry-header">
						<h2 class="entry-title"><?php //todo: use option for header ?>Registration</h2>
					</header><!-- Page Header -->
					<div class="entry-content">
						<?php if ( $success ) {
							?>
							<h3>Success!</h3>
							<?php
						}?>
						<form action="" method="post">
							<?php 
							$options = get_option( 'doorbitch_options' );
							if ( $options == false ) {
								$options = array();
							}
							if ( ! array_key_exists( 'form_html', $options ) || $options[ 'form_html' ] == '' ) {
								$options[ 'form_html' ] = file_get_contents( DOORBITCH__PLUGIN_DIR . '/forms/default.php' );
								update_option( 'doorbitch_options', $options );
							}
							echo $options[ 'form_html' ];

							?>
						</form>
					</div>
				</div>
			</div>
		</div>

	</main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer(); ?>
