<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'shared/wp-bracket-builder-partials-common.php';

/**
 * Icon Buttons DO something (make post request, execute JS, etc.)
 */
function icon_btn($icon_path, $type = '') {
	ob_start();
?>
	<button <?php echo !empty($type) ? "type=$type" : '' ?> class="tw-h-40 tw-w-40 tw-p-8 tw-bg-white/15 tw-border-none tw-text-white tw-flex tw-flex-col tw-items-center tw-justify-center tw-rounded-8 hover:tw-cursor-pointer hover:tw-bg-white hover:tw-text-black">
		<?php echo file_get_contents(plugins_url($icon_path, __FILE__)); ?>
	</button>
<?php
}

/**
 * Icon Links GO somewhere. (To another page, etc.)
 */
function icon_link($icon_path, $endpoint) {
	ob_start();
?>
	<a class="tw-h-40 tw-w-40 tw-p-8 tw-bg-white/15 tw-border-none tw-text-white tw-flex tw-flex-col tw-rounded-8 tw-items-center tw-justify-center hover:tw-cursor-pointer hover:tw-bg-white hover:tw-text-black" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url($icon_path, __FILE__)); ?>
	</a>
<?php
	return ob_get_clean();
}

/**
 * This link will take the user to the Template Builder page
 */
function duplicate_bracket_btn($endpoint, $post_id) {
	return icon_link('../../assets/icons/copy.svg', $endpoint);
}

/**
 * This button will execute JS to open up the share dialog
 */
function share_tournament_btn($endpoint, $tournament_id) {
	return icon_btn('../../assets/icons/link.svg');
}

/**
 * This button sends a POST request to delete the template
 */
function delete_post_btn($endpoint, $post_id, $post_id_field, $nonce_action, $nonce_name) {
	ob_start();
?>
	<form method="post" action="<?php echo esc_url($endpoint) ?>">
		<input type="hidden" name="<?php echo $post_id_field ?>" value="<?php echo esc_attr($post_id) ?>">
		<?php wp_nonce_field($nonce_action, $nonce_name); ?>
		<?php echo icon_btn('../../assets/icons/trash.svg', 'submit'); ?>
	</form>
<?php
	return ob_get_clean();
}



/**
 * This button goes to the Play Bracket page
 */
function add_to_apparel_btn($endpoint) {
	ob_start();
?>
	<a class="wpbb-add-apparel-btn tw-border tw-border-solid tw-border-transparent tw-bg-clip-padding tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/plus.svg', __FILE__)); ?>
		<span class="tw-font-700 tw-text-white">Add to Apparel</span>
	</a>
<?php
	return gradient_border_wrap(ob_get_clean(), array('wpbb-add-apparel-gradient-border', 'tw-rounded-8'));
}
