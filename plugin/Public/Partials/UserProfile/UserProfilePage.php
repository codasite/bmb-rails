<?php

namespace WStrategies\BMB\Public\Partials\UserProfile;

use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Repository\UserProfileRepo;
use WStrategies\BMB\Public\Partials\shared\BracketCards;
use WStrategies\BMB\Public\Partials\shared\BracketsCommon;
use WStrategies\BMB\Public\Partials\TemplateInterface;

class UserProfilePage implements TemplateInterface {
  public function __construct(
    private UserProfileRepo $user_profile_repo = new UserProfileRepo(),
    private PlayRepo $play_repo = new PlayRepo()
  ) {
  }
  public function render(): false|string {
    $user_profile = $this->user_profile_repo->get_by_post();
    $bio = $user_profile->get_bio();
    $plays = $this->play_repo->get_all([
      'posts_per_page' => 6,
      'tag_slug__in' => ['bmb_vip_profile'],
      'author' => $user_profile->author,
    ]);
    ob_start();
    ?>
		<div class="wpbb-reset wpbb-faded-bracket-bg tw-pt-60 tw-pb-[150px] tw-px-20">
			<div class="wpbb-reset tw-max-w-screen-xl tw-mx-auto tw-flex-col tw-flex">
				<div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-30 tw-pb-30 md:tw-py-60 tw-self-center tw-items-center <?= empty(
      $bio
    )
      ? 'md:tw-items-center'
      : 'md:tw-items-start' ?>">
					<?php if ($user_profile->thumbnail_url): ?>
						<div class="tw-shrink-0 tw-w-[150px] tw-h-[150px] md:tw-w-[200px] md:tw-h-[200px] tw-rounded-full tw-border-4 tw-bg-white tw-bg-cover tw-bg-center tw-bg-no-repeat" style="background-image: url(<?php echo $user_profile->thumbnail_url; ?>)">
						</div>
					<?php endif; ?>
					<div class="tw-flex tw-flex-col tw-items-center md:tw-items-start">
						<div class="tw-shrink tw-mb-20 md:tw-mb-4">
							<span class="tw-text-20 md:tw-text-24 tw-bg-red tw-font-700 tw-rounded-8 tw-px-16 tw-py-4">VIP</span>
						</div>
						<h1 class="tw-text-48 md:tw-text-64 tw-font-700 tw-text-center"><?php echo $user_profile->author_display_name; ?></h1>
						<?php if (!empty($bio)): ?>
							<div class="wpbb-user-profile-bio tw-max-w-[50em] tw-text-bluish">
								<?php echo $user_profile->get_bio(); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<h1 class="tw-text-32 md:tw-text-48 tw-font-700 tw-py-30">Recent Play History</h1>
				<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-10">
					<?php foreach ($plays as $play): ?>
						<?php echo BracketCards::vip_play_card($play); ?>
					<?php endforeach; ?>
				</div>
				<h1 class="tw-text-32 md:tw-text-48 tw-font-700 tw-pt-60">Brackets</h1>
				<div class="tw-flex tw-justify-start tw-gap-10 tw-py-24 tw-flex-wrap">
					<?php echo BracketsCommon::bracket_filter_buttons(); ?>
				</div>
				<?php echo BracketsCommon::public_bracket_list([
      'author' => $user_profile->author,
      'tags' => ['bmb_vip_profile'],
    ]); ?>
			</div>
			<div id='wpbb-public-bracket-modals'></div>
		</div>
		<?php return ob_get_clean();
  }
}
