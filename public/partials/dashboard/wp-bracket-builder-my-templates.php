<?php
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

function template_list_item($template) {
	$name = $template['name'];
	$id = $template['id'];
	$num_teams = $template['num_teams'];
	$share_link = get_permalink() . 'templates/' . $id;
	$delete_link = get_permalink() . 'templates/' . $id . '/delete';
	$template_play_link = get_permalink() . 'templates/' . $id . '/play';
	$template_host_link = get_permalink() . 'templates/' . $id . '/host';
	ob_start();
?>
	<div class="wpbb-template-list-item wpbb-padding-30 wpbb-flex-col wpbb-gap-16 wpbb-border-radius-16">
		<span class="wpbb-font-weight-500 wpbb-font-size-12"><?php echo esc_html($num_teams) ?>-Team Bracket</span>
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