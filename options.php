<div class="wrap">
	<h1>Doorbitch</h1>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'doorbitch-option-group' );
		do_settings_sections( 'doorbitch-option-group' );
		submit_button();
		?>
	</form>
</div>