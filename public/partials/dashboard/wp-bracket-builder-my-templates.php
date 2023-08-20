<?php
require_once('wp-bracket-builder-common.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_template_id'])) {
	echo 'delete template_id: ' . $_POST['delete_template_id'];
	echo get_query_var(('delete'));
	if (wp_verify_nonce($_POST['delete_template_nonce'], 'delete_template_action')) {
		echo 'nonce verified';
	}
}
$templates = array(
	array(
		"name" => "Lakeside High Football",
		"id" => 1,
		"num_teams" => 16,
	),
	array(
		"name" => "Springfield 2024",
		"id" => 2,
		"num_teams" => 6,
	),
);


function host_tournament_btn($endpoint) {
	ob_start();
?>
	<a class="wpbb-bracket-action-btn wpbb-host-tournament-btn wpbb-flex wpbb-gap-10 wpbb-align-center wpbb-border-radius-8" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/signal.svg', __FILE__)); ?>
		<span class="wpbb-font-weight-500 wpbb-color-white">Host Tournament</span>
	</a>
<?php
	return ob_get_clean();
}



function template_list_item($template) {
	$name = $template['name'];
	$id = $template['id'];
	$num_teams = $template['num_teams'];
	$share_link = get_permalink() . 'templates/' . $id;
	$delete_link = get_permalink() . 'templates/delete';
	$template_play_link = get_permalink() . 'templates/' . $id . '/play';
	$template_host_link = get_permalink() . 'templates/' . $id . '/host';
	ob_start();
?>
	<div class="wpbb-template-list-item wpbb-border-grey-15-2 wpbb-padding-30 wpbb-flex-col wpbb-gap-10 wpbb-border-radius-16">
		<span class="wpbb-font-weight-500 wpbb-font-size-12"><?php echo esc_html($num_teams) ?>-Team Bracket</span>
		<div class="wpbb-flex wpbb-gap-10 wpbb-align-center">
			<h2 class="wpbb-color-white wpbb-font-weight-700 wpbb-font-size-30"><?php echo esc_html($name) ?></h2>
			<?php echo duplicate_bracket_btn($share_link, $id); ?>
			<?php echo delete_bracket_btn($delete_link, $id); ?>
		</div>
		<div class="wpbb-template-buttons wpbb-flex wpbb-gap-16">
			<?php echo add_apparel_btn($template_play_link); ?>
			<?php echo host_tournament_btn($template_host_link); ?>
		</div>
	</div>
<?php
	return ob_get_clean();
}

// function template_share_btn($template) {
// 	$id = $template['id'];
// 	return get_permalink() . 'templates/' . $id;
// }

// function get_template_delete
?>
<div class="wpbb-my-templates wpbb-flex-col wpbb-gap-30">
	<h1>My Templates</h1>
	<a href="#" class="wpbb-create-template-link wpbb-block wpbb-flex wpbb-gap-16 wpbb-align-center wpbb-justify-center wpbb-border-radius-8">
		<?php echo file_get_contents(plugins_url('../../assets/icons/file_plus.svg', __FILE__)); ?>
		<span class="wpbb-font-weight-700 wpbb-font-size-24">Create Bracket Template</span>
	</a>
	<div class="wpbb-templates-list wpbb-flex-col wpbb-gap-16">
		<?php foreach ($templates as $template) {
			echo template_list_item($template);
		} ?>
	</div>

</div>