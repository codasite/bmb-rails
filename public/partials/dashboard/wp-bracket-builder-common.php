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
	<!-- <a class="wpbb-bracket-action-icon-btn wpbb-flex-col wpbb-border-radius-8 wpbb-align-center wpbb-justify-center"> -->
	<a class="tw-h-40 tw-w-40 tw-p-8 tw-bg-white/15 tw-border-none tw-text-white tw-flex tw-flex-col tw-rounded-8 tw-items-center tw-justify-center hover:tw-cursor-pointer hover:tw-bg-white hover:tw-text-black">
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

function delete_bracket_btn($endpoint, $post_id) {
	ob_start();
?>
	<form method="post" action="<?php echo esc_url($endpoint) ?>">
		<input type="hidden" name="delete_template_id" value="<?php echo esc_attr($post_id) ?>">
		<?php wp_nonce_field('delete_template_action', 'delete_template_nonce'); ?>
		<button type="submit" class="tw-h-40 tw-w-40 tw-p-8 tw-bg-white/15 tw-border-none tw-text-white tw-flex tw-flex-col tw-rounded-8 tw-items-center tw-justify-center hover:tw-cursor-pointer hover:tw-bg-white hover:tw-text-black">
			<?php echo file_get_contents(plugins_url('../../assets/icons/trash.svg', __FILE__)); ?>
		</button>
	</form>
<?php
	return ob_get_clean();
}

function add_to_apparel_btn($endpoint) {
	ob_start();
?>
	<a class="wpbb-add-apparel-btn tw-border tw-border-solid tw-border-transparent tw-bg-clip-padding tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/plus.svg', __FILE__)); ?>
		<span class="tw-font-700 tw-text-white">Add to Apparel</span>
	</a>
<?php
	return gradient_border_wrap(ob_get_clean(), array('wpbb-add-apparel-gradient-border', 'wpbb-border-radius-8'));
}

function play_tournament_btn($endpoint, $tournament_id) {
	ob_start();
?>
	<a class="tw-border-green tw-border-solid tw-border tw-bg-green/15 tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8 tw-text-white" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/play.svg', __FILE__)); ?>
		<span class="wpbb-font-weight-500">Play Tournament</span>
	</a>
<?php
	return ob_get_clean();
}


function view_leaderboard_btn($endpoint, $variant = 'primary') {
	$label = 'View Leaderboard';
	$final = false;

	$base_cls = array('tw-flex', 'tw-justify-center', 'sm:tw-justify-start', 'tw-items-center', 'tw-text-white', 'tw-rounded-8', 'tw-border', 'tw-border-solid', 'tw-px-16', 'tw-py-12');

	$cls_list = array(
		'primary' => array_merge($base_cls, array('tw-border-white/50', 'tw-bg-white/15', 'tw-gap-10', 'tw-px-16', 'tw-py-12')),
		'compact' => array_merge($base_cls, array('tw-border-white/50', 'tw-bg-white/15', 'tw-gap-4', 'sm:tw-px-8', 'sm:tw-py-4')),
		'final' => array_merge($base_cls, array('wpbb-view-final-leaderboard-btn', 'tw-border-transparent', 'tw-bg-clip-padding', 'tw-gap-10', 'tw-px-16', 'tw-py-12')),
	);

	if ($variant === 'final') {
		$label = 'View Final Leaderboard';
		$final = true;
	}

	ob_start();
?>
	<a class="<?php echo implode(' ', $cls_list[$variant]) ?>" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/trend_up.svg', __FILE__)); ?>
		<!-- <span class="wpbb-font-weight-500 wpbb-font-size-16"><?php echo esc_html($label) ?></span> -->
		<span class="tw-font-500 tw-text-16"><?php echo esc_html($label) ?></span>
	</a>
<?php
	$btn = ob_get_clean();
	return $final ? gradient_border_wrap($btn, array('wpbb-leaderboard-gradient-border wpbb-border-radius-8')) : $btn;
}

function view_leaderboard_btn_old($endpoint, $variant = 'primary') {
	$size = 'md';
	$gap = '10';
	$label = 'View Leaderboard';
	$final = false;
	switch ($variant) {
		case 'compact':
			$size = 'sm';
			$gap = '4';
			break;
		case 'final';
			$label = 'View Final Leaderboard';
			$final = true;
			break;
	}
	ob_start();
?>
	<a class="wpbb-view<?php echo $final ? '-final' : ''; ?>-leaderboard-btn wpbb-flex wpbb-gap-<?php echo $gap ?> wpbb-align-center wpbb-color-white wpbb-btn-padding-<?php echo $size ?> wpbb-border-radius-8 wpbb-border-grey-50 wpbb-bg-grey-15" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/trend_up.svg', __FILE__)); ?>
		<!-- <span class="wpbb-font-weight-500 wpbb-font-size-16"><?php echo esc_html($label) ?></span> -->
		<span class="tw-font-500 tw-text-16"><?php echo esc_html($label) ?></span>
	</a>
<?php
	$btn = ob_get_clean();
	return $final ? gradient_border_wrap($btn, array('wpbb-leaderboard-gradient-border wpbb-border-radius-8')) : $btn;
}

function view_play_btn($endpoint) {
	ob_start();
?>
	<a class="wpbb-view-play-btn wpbb-flex wpbb-align-center wpbb-color-white wpbb-btn-padding-md wpbb-border-radius-8 wpbb-border-green wpbb-bg-green-15" href="<?php echo esc_url($endpoint) ?>">
		<span class="wpbb-font-weight-700">View Play</span>
	</a>
<?php
	return ob_get_clean();
}
