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
	// TODO: add validation!
	$submission_errors = array();
	$dataset = '';
	foreach ($_POST as $item => $data ) {
		$dataset = $dataset . $item . ':' . $data . ', ';
	}
	$doorbitch->debug( $dataset );
	// validate the data:
	if ( ! isset( $_POST[ 'disclaimer' ] ) || $_POST[ 'disclaimer' ] != 'on' ) {
		array_push( $submission_errors, 'You must agree to the disclaimer to register.' );
	}
	if ( empty( $submission_errors ) ) {
		$success = $doorbitch->add_data( $options[ 'current_event' ], $dataset );
		if ( $success ){ array_push( $submission_errors, 'The data could not be saved.' ); }
	}
} else {
	$doorbitch->debug( 'There is no post data' );
}
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div class="page-content">
			<div class="panel-content">
				<div class="wrap">
					<?php if ( empty( $submission_errors ) && $success == true ) {
						?>
						<div class='notification submission_errors'>
							<h3>Success!</h3>
						</div>
						<?php
						}
						elseif ( ! empty( $submission_errors ) ) {
							?>
							<div class='notification failure'>
								<h3>Sorry!</h3>
								<?php
									foreach ( $submission_errors as $error ) {
										echo( $error );
									}
								?>
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
