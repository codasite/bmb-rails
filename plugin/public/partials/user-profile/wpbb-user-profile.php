<?php
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket.php';
require_once WPBB_PLUGIN_DIR . 'public/partials/shared/wpbb-brackets-common.php';
require_once WPBB_PLUGIN_DIR . 'public/partials/shared/wpbb-pagination-widget.php';
require_once WPBB_PLUGIN_DIR . 'public/partials/shared/wpbb-partials-constants.php';

// RECENT PLAY HISTORY
// PLAY CARDS

// BRACKETS
// sort buttons
// bracket cards
// pagination

$user_profile = get_post();
print_r($user_profile);
?>
<div class="wpbb-reset wpbb-faded-bracket-bg tw-pt-60 tw-pb-[150px] tw-px-20">
	<div class="wpbb-reset tw-max-w-screen-lg tw-mx-auto">
		<!-- Profile picture VIP, name -->
		<div class="tw-flex tw-gap-30 tw-py-60 tw-self-center tw-w-full">
		</div>
    <h1 class="tw-text-32 md:tw-text-48 tw-font-700">Brackets</h1>
    <div class="tw-flex tw-justify-start tw-gap-10 tw-py-24 tw-flex-wrap">
      <?php echo wpbb_bracket_sort_buttons(); ?>
    </div>
    <?php echo public_bracket_list($user_profile->post_author); ?>
  </div>
</div>
