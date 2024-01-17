<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

use WStrategies\BMB\Includes\Repository\UserProfileRepo;
use WStrategies\BMB\Includes\Service\BracketLeaderboardService;

class ProfilePage {
  static function render() {
    $profile_repo = new UserProfileRepo();
    $leaderboard_service = new BracketLeaderboardService();
    $user = $profile_repo->get_by_user();
    $num_plays = $user->get_num_plays();
    $wins = $user->get_tournament_wins();
    $accuracy = $user->get_total_accuracy() * 100;
    ob_start();
    ?>
    <div class="tw-flex tw-flex-col tw-gap-15">
      <h1 class="sm:tw-mb-16 tw-text-32 sm:tw-text-48 lg:tw-text-64">My Profile</h1>
      <!-- <h3 class="tw-text-white/50">Overall Tournament Score</h3> -->
      <div class="tw-flex tw-gap-10 tw-flex-wrap">
        <!-- <div class="tw-flex tw-flex-col tw-w-[340px] tw-h-[308px] tw-p-30 tw-border-2 tw-border-solid tw-border-white/10 tw-rounded-16 tw-justify-between tw-bg-green/15 ">
          <?php echo file_get_contents(
            WPBB_PLUGIN_DIR . 'Public/assets/icons/pie.svg'
          ); ?>
          <div class="tw-flex tw-flex-col tw-gap-4">
            <h1><?php echo $accuracy; ?>%</h1>
            <h3 class="tw-text-20 tw-text-white/50">Accuracy Score</h3>
          </div>
        </div> -->
        <div class="tw-flex tw-flex-col tw-w-[340px] tw-h-[308px] tw-p-30 tw-border-2 tw-border-solid tw-border-white/10 tw-rounded-16 tw-justify-end">
          <div class="tw-flex tw-flex-col tw-gap-4">
            <h1 class="tw-text-48 sm:tw-text-64"><?php echo $wins; ?></h1>
            <h3 class="tw-text-20 tw-text-white/50 tw-font-500">Tournament Wins</h3>
          </div>
        </div>
        <div class="tw-flex tw-flex-col tw-w-[340px] tw-h-[308px] tw-p-30 tw-border-2 tw-border-solid tw-border-white/10 tw-rounded-16 tw-justify-between">
          <a href="<?php echo get_permalink() .
            'play-history'; ?>" class="tw-flex tw-gap-16 tw-items-center hover:tw-text-blue">
            <?php echo file_get_contents(
              WPBB_PLUGIN_DIR . 'Public/assets/icons/arrow_up_right.svg'
            ); ?>
            <span class="tw-font-500">View My Play History</span>
          </a>
          <div class="tw-flex tw-flex-col tw-gap-4">
            <!-- This is the number of tournaments the user has played -->
            <h1 class="tw-text-48 sm:tw-text-64"><?php echo $num_plays; ?></h1>
            <h3 class="tw-text-20 tw-text-white/50 tw-font-500">Total Tournaments Played</h3>
          </div>
        </div>
      </div>
    </div>
    <?php return ob_get_clean();
  }
}
