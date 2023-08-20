<?php

function icon_btn($icon_path, $endpoint) {
	ob_start();
?>
	<button class="wpbb-bracket-action-icon-btn wpbb-flex-col wpbb-border-radius-8">
		<?php echo file_get_contents(plugins_url($icon_path, __FILE__)); ?>
	</button>
<?php

}

function duplicate_bracket_btn($endpoint, $post_id) {
	ob_start();
?>
	<a class="wpbb-bracket-action-icon-btn wpbb-flex-col wpbb-border-radius-8 wpbb-align-center wpbb-justify-center">
		<?php echo file_get_contents(plugins_url('../../assets/icons/copy.svg', __FILE__)); ?>
	</a>
<?php
	return ob_get_clean();
}

function share_tournament_btn($endpoint, $tournament_id) {
	ob_start();
?>
	<button class="wpbb-bracket-action-icon-btn wpbb-flex-col wpbb-border-radius-8 wpbb-align-center wpbb-justify-center">
		<?php echo file_get_contents(plugins_url('../../assets/icons/link.svg', __FILE__)); ?>
	</button>
<?php
	return ob_get_clean();
}

function delete_bracket_btn($endpoint, $post_id) {
	ob_start();
?>
	<form method="post" action="<?php echo esc_url($endpoint) ?>">
		<input type="hidden" name="delete_template_id" value="<?php echo esc_attr($post_id) ?>">
		<?php wp_nonce_field('delete_template_action', 'delete_template_nonce'); ?>
		<button type="submit" class="wpbb-bracket-action-icon-btn wpbb-flex-col wpbb-border-radius-8 wpbb-align-center wpbb-justify-center" value="">
			<?php echo file_get_contents(plugins_url('../../assets/icons/trash.svg', __FILE__)); ?>
		</button>
	</form>
<?php
	return ob_get_clean();
}

function add_apparel_btn($endpoint) {
	ob_start();
?>
	<a class="wpbb-bracket-action-btn wpbb-add-apparel-btn wpbb-flex wpbb-gap-10 wpbb-align-center wpbb-border-radius-8" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/plus.svg', __FILE__)); ?>
		<span class="wpbb-font-weight-700 wpbb-color-white">Add to Apparel</span>
	</a>
<?php
	return ob_get_clean();
}

function play_tournament_btn($endpoint, $tournament_id) {
	ob_start();
?>
	<a class="wpbb-bracket-action-btn wpbb-play-tournament-btn wpbb-flex wpbb-gap-10 wpbb-align-center wpbb-border-radius-8 wpbb-color-white" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/play.svg', __FILE__)); ?>
		<span class="wpbb-font-weight-500">Play Tournament</span>
	</a>
<?php
	return ob_get_clean();
}
