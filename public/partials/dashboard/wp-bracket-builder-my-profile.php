<?php
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/domain/class-wp-bracket-builder-user-profile.php';
/**
 * The template for displaying the user's profile.
 *
 * @package WP_Bracket_Builder
 */

// // Exit if accessed directly.
// defined('ABSPATH') || exit;

$user = Wp_Bracket_Builder_User_Profile::get_current();
$num_plays = $user->get_num_plays();
$wins = $user->get_tournament_wins();
$accuracy = $user->get_total_accuracy() * 100;

?>
<div class="tw-flex tw-flex-col tw-gap-15">
	<h1 class="tw-mb-16">My Profile</h1>
	<!-- <h3 class="tw-text-white/50">Overall Tournament Score</h3> -->
	<div class="tw-flex tw-gap-10 tw-flex-wrap">
		<!-- <div class="tw-flex tw-flex-col tw-w-[340px] tw-h-[308px] tw-p-30 tw-border-2 tw-border-solid tw-border-white/10 tw-rounded-16 tw-justify-between tw-bg-green/15 ">
			<?php echo file_get_contents(plugins_url('../../assets/icons/pie.svg', __FILE__)); ?>
			<div class="tw-flex tw-flex-col tw-gap-4">
				<h1><?php echo $accuracy ?>%</h1>
				<h3 class="tw-text-20 tw-text-white/50">Accuracy Score</h3>
			</div>
		</div> -->
		<!-- <div class="tw-flex tw-flex-col tw-w-[340px] tw-h-[308px] tw-p-30 tw-border-2 tw-border-solid tw-border-white/10 tw-rounded-16 tw-justify-end">
			<div class="tw-flex tw-flex-col tw-gap-4">
				<h1><?php echo $wins ?></h1>
				<h3 class="tw-text-20 tw-text-white/50">Tournament Wins</h3>
			</div>
		</div> -->
		<div class="tw-flex tw-flex-col tw-w-[340px] tw-h-[308px] tw-p-30 tw-border-2 tw-border-solid tw-border-white/10 tw-rounded-16 tw-justify-between">
			<a href="<?php echo get_permalink() . 'play-history'; ?>" class="tw-flex tw-gap-16 tw-items-center hover:tw-text-blue">
				<?php echo file_get_contents(plugins_url('../../assets/icons/arrow_up_right.svg', __FILE__)); ?>
				<span class="tw-font-500">View My Play History</span>
			</a>
			<div class="tw-flex tw-flex-col tw-gap-4">
				<!-- This is the number of tournaments the user has played -->
				<h1><?php echo $num_plays; ?></h1>
				<h3 class="tw-text-20 tw-text-white/50">Total Tournaments Played</h3>
			</div>
		</div>
	</div>
</div>