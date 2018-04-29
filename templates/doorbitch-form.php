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
				<h1>Doorbitch</h1>
				<form action="../form-action.php" method="get">
					<table>
						<tr>
							<td>
								<label for="fname">Name</label>
								<input type="text" name="fname" >
							</td>
						</tr>
						<tr>
							<td>
								<label for="18-25">18-25</label>
								<input type="radio" name="18-25">
								<label for="26-30">26-30</label>
								<input type="radio" name="26-30">
								<label for="30-35">30-35</label>
								<input type="radio" name="30-35">
								<label for="35+">35+</label>
								<input type="radio" name="35+">
							</td>
						</tr>
						<tr>
							<td>
								<input type="submit" value="Submit" >
							</td>
						</tr>
					</table>
				</form>
			</div>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
