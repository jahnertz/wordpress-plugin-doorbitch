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
global $doorbitch;
$options = get_option( DOORBITCH__OPTIONS );

if ( !empty($_POST) ) {

	$dataset = '';
	foreach ($_POST as $item => $data ) {
		$dataset = $dataset . $item . ':' . $data . ', ';
	}
	$doorbitch->debug( $dataset );
	$success = $doorbitch->add_data( $options[ 'current_event' ], $dataset );
} else {
	$doorbitch->debug( 'There is no post data' );
}
// Clear the post data:
	unset( $_POST );
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div class="page-content">
			<div class="panel-content">
				<div class="wrap">
					<?php if ( isset( $success ) && $success == true ) {
						?>
						<div class='notification success'>
							<h3>Success!</h3>
						</div>
						<?php
						}
						elseif ( $success == false ) {
							?>
							<div class='notification failure'>
								<h3>Sorry!</h3>
								<p>The data could not be saved.</p>
							</div>
							<?php
						}
					?>
					<header class="entry-header">
					</header><!-- Page Header -->
					<div class="entry-content">
						<form action="" method="post">
							<?php 
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
