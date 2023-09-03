<?php

/**
 * This button goes to the View Play page
 */
function view_play_btn($endpoint) {
	ob_start();
?>
	<a class="tw-flex tw-items-center tw-text-white tw-px-16 tw-py-12 tw-rounded-8 tw-border tw-border-solid tw-border-transparent tw-bg-dd-blue/80 tw-bg-clip-padding tw-h-full" href="<?php echo esc_url($endpoint) ?>">
		<span class="tw-font-700">View Play</span>
	</a>
<?php
	// return ob_get_clean();
	return gradient_border_wrap(ob_get_clean(), array('wpbb-add-apparel-gradient-border', 'tw-rounded-8'));
}

/**
 * This is a utility wrapper for buttons that have a gradient border
 */
function gradient_border_wrap($content, $class_arr = array()) {
	$classes = implode(' ', $class_arr);
	ob_start();
?>
	<div class="<?php echo esc_attr($classes) ?>">
		<?php echo $content; ?>
	</div>
<?php
	return ob_get_clean();
}
