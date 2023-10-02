<?php
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once 'wp-bracket-builder-dashboard-common.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'public/partials/shared/wp-bracket-builder-pagination-widget.php';

$template_repo = new Wp_Bracket_Builder_Bracket_Template_Repository();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_template_id'])) {
	if (wp_verify_nonce($_POST['delete_template_nonce'], 'delete_template_action')) {
		$template_repo->delete($_POST['delete_template_id']);
	}
}
function host_tournament_btn(string $id) {
	ob_start();
?>
	<button data-template-id="<?php echo $id ?>" class="wpbb-host-tournament-button tw-border tw-border-solid tw-border-blue tw-bg-blue/15 tw-px-16 tw-py-12 tw-flex tw-gap-10 tw-items-center tw-justify-center tw-rounded-8 hover:tw-bg-blue tw-font-sans tw-text-white tw-uppercase tw-cursor-pointer">
		<?php echo file_get_contents(plugins_url('../../assets/icons/signal.svg', __FILE__)); ?>
		<span class="tw-font-700">Host Tournament</span>
	</button>
<?php
	return ob_get_clean();
}

function template_list_item(Wp_Bracket_Builder_Bracket_Template $template) {
	$name = $template->title;
	$id = $template->id;
	$num_teams = $template->num_teams;
	// This link leads to the Create Template page. It passes in the original template_id as a query param
	$duplicate_link = get_permalink($template->id) . 'copy';
	// This link executes a POST request to delete the template. It should prompt the user to confirm the deletion
	$delete_link = get_permalink() . 'templates/delete';
	// This link leads to the Play Bracket page. It passes in the template_id as a query param
	$template_play_link = get_permalink($template->id);
	ob_start();
?>
	<div class="tw-border-2 tw-border-white/15 tw-border-solid tw-p-30 tw-flex tw-flex-col tw-gap-10 tw-rounded-16">
		<span class="tw-font-500 tw-text-12"><?php echo esc_html($num_teams) ?>-Team Bracket</span>
		<div class="tw-flex tw-gap-10 tw-items-center tw-justify-between md:tw-justify-start">
			<h2 class="tw-text-white tw-font-700 tw-text-30"><?php echo esc_html($name) ?></h2>
			<div class="tw-flex tw-gap-10">
				<div id="wpbb-create-template-button-and-modal" data-template-id=<?php echo '"'. $id . '"'?> ></div>
				<?php echo duplicate_bracket_btn($duplicate_link, $id); ?>
				<?php echo delete_post_btn($delete_link, $id, 'delete_template_id', 'delete_template_action', 'delete_template_nonce'); ?>
			</div>
		</div>
		<div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16">
			<?php echo add_to_apparel_btn($template_play_link); ?>
			<?php echo host_tournament_btn($id); ?>
		</div>
	</div>
<?php
	return ob_get_clean();
}

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

$the_query = new WP_Query([
	'post_type' => Wp_Bracket_Builder_Bracket_Template::get_post_type(),
	'author' => get_current_user_id(),
	'posts_per_page' => 6,
	'paged' => $paged,
	'post_status' => 'any',
]);

$num_pages = $the_query->max_num_pages;

$templates = $template_repo->get_all($the_query);

$create_template_link = get_permalink(get_page_by_path('bracket-template-builder'));

?>
<div id="wpbb-host-tournament-modal" class="tw-absolute"></div>
<div class="tw-flex tw-flex-col tw-gap-30">
	<h1>My Templates</h1>
	<!-- this link leads to the Create Template page to create a new bracket from scratch -->
	<a href="<?php echo $create_template_link ?>" class="tw-flex tw-gap-16 tw-items-center tw-justify-center tw-border-solid border tw-border-white tw-rounded-8 tw-p-16 tw-bg-white/15 hover:tw-text-black hover:tw-bg-white">
		<?php echo file_get_contents(plugins_url('../../assets/icons/file_plus.svg', __FILE__)); ?>
		<span class="tw-font-700 tw-text-24">Create Bracket Template</span>
	</a>
	<div class="tw-flex tw-flex-col tw-gap-16">
		<?php foreach ($templates as $template) {
			echo template_list_item($template);
		} ?>
		<?php wpbb_pagination($paged, $num_pages); ?>
	</div>
</div>