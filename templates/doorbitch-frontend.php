<?php
/**
 * The template for displaying the frontend form.
 *
 * @package WordPress
 * @subpackage Doorbitch
 * @since Doorbitch 0.0.2
 */

get_header(); 
global $doorbitch;
$options = get_option( DOORBITCH__OPTIONS );
$success = NULL;

if ( ! empty( $_POST ) ) {
	$submission_errors = array();
	// TODO: actually validate the data.
	// TODO: This is a hard coded last minute fix. validation should be done according to 'required' classes in the form.
	// Because I hate myself:
	if ( ! isset( $_POST[ 'disclaimer' ] ) || $_POST[ 'disclaimer' ] != 'on' ) {
		array_push( $submission_errors, 'You must agree to the disclaimer to register.' );
	}
	if ( ! isset( $_POST[ 'name' ] ) || $_POST[ 'name' ] == '' ) {
		array_push( $submission_errors, 'Please provide your name' );
	}
	if ( ! isset( $_POST[ 'age' ] ) ) {
		array_push( $submission_errors, 'Please provide your age' );
	}
	if ( ! isset( $_POST[ 'city' ] ) ) {
		array_push( $submission_errors, 'Please provide your city' );
	}
	if ( ! isset( $_POST[ 'email' ] ) ) {
		array_push( $submission_errors, 'Please provide your email' );
	} elseif ( ! is_email( $_POST[ 'email' ] ) ) {
		array_push( $submission_errors, 'Please provide a valid email address' );
	}
	if ( empty( $submission_errors ) ) {
		// $dataset = '';
		foreach ($_POST as $item => $data ) {
			$dataset = $dataset . $item . ':' . $data . ', ';
		}
		$doorbitch->debug( $dataset );
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
					<?php
					if ( empty( $submission_errors ) ) {
						if ( $success == true ) {	
							?>
							<div class='notification submission_errors'>
								<h3>Success!</h3>
							</div>
							<?php
						}
					}
					else {
						?>
						<div class='notification failure'>
							<h3>Sorry!</h3>
							<ul>
								<?php
								foreach ( $submission_errors as $error ) {
									echo( '<li>' . $error . '</li>' );
								}
								?>
							</ul>
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
