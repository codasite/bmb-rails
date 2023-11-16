<?php
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-user-profile-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket.php';
require_once WPBB_PLUGIN_DIR . 'public/partials/shared/wpbb-brackets-common.php';
require_once WPBB_PLUGIN_DIR . 'public/partials/shared/wpbb-pagination-widget.php';
require_once WPBB_PLUGIN_DIR . 'public/partials/shared/wpbb-partials-constants.php';

$user_profile_repo = new Wpbb_UserProfileRepo();
$user_profile = $user_profile_repo->get_by_post();
?>
<div class="wpbb-reset wpbb-faded-bracket-bg tw-pt-60 tw-pb-[150px] tw-px-20">
	<div class="wpbb-reset tw-max-w-screen-lg tw-mx-auto tw-flex-col tw-flex">
		<!-- Profile picture VIP, name -->
		<div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-30 tw-py-60 tw-self-center tw-items-center">
      <div class="tw-w-[150px] tw-h-[150px] md:tw-w-[200px] md:tw-h-[200px] tw-rounded-full tw-border-4 tw-border-white tw-overflow-hidden">
        <img src="<?php echo $user_profile->thumbnail_url; ?>" alt="PROFILE PICTURE" class="tw-object-cover tw-w-full tw-h-full tw-leading-8">
      </div>
      <div class="tw-flex tw-flex-col tw-items-center md:tw-items-start">
        <div class="tw-shrink">
          <span class="tw-text-20 md:tw-text-24 tw-bg-red tw-font-700 tw-rounded-8 tw-px-16 tw-py-4">VIP</span>
        </div>
        <h1 class="tw-text-48 md:tw-text-64 tw-font-700 tw-text-center"><?php echo $user_profile->author_display_name; ?></h1>
      </div>
		</div>
    <h1 class="tw-text-32 md:tw-text-48 tw-font-700 tw-py-30">Recent Play History</h1>

    <h1 class="tw-text-32 md:tw-text-48 tw-font-700">Brackets</h1>
    <div class="tw-flex tw-justify-start tw-gap-10 tw-py-24 tw-flex-wrap">
      <?php echo wpbb_bracket_sort_buttons(); ?>
    </div>
    <?php echo public_bracket_list($user_profile->author); ?>
  </div>
</div>
