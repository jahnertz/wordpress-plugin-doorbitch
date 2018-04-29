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
								<input type="text" name="fname"><br>
							</td>
						</tr>
						<tr>
							<td>
								<input type="submit" value="Submit">
							</td>
						</tr>
					</table>
				</form>
			</div>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
