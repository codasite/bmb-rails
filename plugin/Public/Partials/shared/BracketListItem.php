<?php

namespace WStrategies\BMB\Public\Partials\shared;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Public\Partials\dashboard\DashboardCommon;
use WStrategies\BMB\Features\MobileApp\MobileAppUtils;

class BracketListItem {
  public static function bracket_list_item(Bracket $bracket): false|string {
    $play_repo = PlayRepo::getInstance();
    $user_play = $play_repo->get_play_by_user_and_bracket(
      get_current_user_id(),
      $bracket->id
    );
    ob_start();
    ?>
    <div class="tw-border-2 tw-border-solid <?php echo $bracket->status ==
    'publish'
      ? 'tw-border-blue'
      : 'tw-border-white/15'; ?> tw-bg-dd-blue tw-flex tw-flex-col tw-gap-10 tw-p-30 tw-rounded-16">
      <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between sm:tw-items-center tw-gap-8">
        <div class="tw-flex tw-gap-8 tw-items-center">
          <?php echo $bracket->has_fee() ? self::paid_bracket_tag() : ''; ?>
          <?php echo $bracket->is_voting ? self::voting_bracket_tag() : ''; ?>
          <span class="tw-font-500 tw-text-12"><?php echo esc_html(
            (string) $bracket->num_teams
          ); ?>-Team Bracket</span>
        </div>
        <div class="tw-flex tw-gap-4 tw-items-center">
          <?php echo BracketsCommon::get_bracket_status_tag($bracket); ?>
          <?php echo PartialsCommon::icon('bar_chart'); ?>
          <span class="tw-font-500 tw-text-20 tw-text-white"><?php echo esc_html(
            (string) $bracket->num_plays
          ); ?></span>
          <span class="tw-font-500 tw-text-20 tw-text-white/50">Plays</span>
        </div>
      </div>
      <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-gap-15">
        <h2 class="tw-text-white tw-font-700 tw-text-20 sm:tw-text-30"><?php echo esc_html(
          $bracket->title
        ); ?></h2>
        <div class="tw-flex tw-gap-10 tw-items-start tw-flex-wrap">
          <?php echo BracketIconButtons::get_bracket_icon_buttons($bracket); ?>
        </div>
      </div>
      <div class="tw-mt-10 tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16 tw-flex-wrap">
        <?php echo self::get_bracket_buttons($bracket); ?>
      </div>
        <?php if ($user_play !== null): ?>
            <?php echo MyScoreWidget::my_score($user_play, [
              'float_right' => false,
            ]); ?>
      <?php endif; ?>
    </div>
    <?php return ob_get_clean();
  }

  public static function paid_bracket_tag(): false|string {
    ob_start(); ?>
    <div class="tw-text-white tw-bg-blue tw-px-8 tw-py-4 tw-flex tw-items-center tw-rounded-8">
      <?php echo PartialsCommon::icon('currency_dollar'); ?>
    </div>
    <?php return ob_get_clean();
  }

  public static function voting_bracket_tag(): false|string {
    ob_start(); ?>
    <div class="wpbb-voting-bracket-tag tw-px-8 tw-py-4 tw-flex tw-items-center tw-gap-4 tw-rounded-8">
      <?php echo PartialsCommon::icon('percent_16'); ?>
      <span class="tw-text-14 tw-text-dd-blue tw-font-600">Voting</span>
    </div>
    <?php return ob_get_clean();
  }

  public static function get_bracket_buttons($bracket): false|string {
    switch ($bracket->status) {
      case 'publish':
        return self::live_bracket_buttons($bracket);
      case 'archive':
      case 'private':
        return self::private_bracket_buttons($bracket);
      case 'score':
        return self::scored_bracket_buttons($bracket);
      case 'complete':
        return self::completed_bracket_buttons($bracket);
      case 'upcoming':
        return BracketsCommon::public_bracket_upcoming_buttons($bracket);
      default:
        return '';
    }
  }

  public static function go_live_btn(Bracket $bracket): false|string {
    if (
      !current_user_can('wpbb_edit_bracket', $bracket->id) ||
      (MobileAppUtils::is_mobile_app_request() &&
        !current_user_can('wpbb_share_bracket', $bracket->id))
    ) {
      return '';
    }
    ob_start();
    ?>
    <button data-bracket-id="<?php echo $bracket->id; ?>" data-go-live-url="<?php echo esc_url($bracket->url) . 'go-live'; ?>"
            class="wpbb-publish-bracket-button tw-border tw-border-solid tw-border-blue tw-bg-blue/15 tw-min-w-[190px] tw-px-16 tw-py-12 tw-flex tw-gap-10 tw-items-center tw-justify-center tw-rounded-8 hover:tw-bg-blue tw-font-sans tw-text-white tw-uppercase tw-cursor-pointer">
      <?php echo PartialsCommon::icon('signal'); ?>
      <span class="tw-font-700">Go Live</span>
    </button>
    <?php return ob_get_clean();
  }

  public static function scored_bracket_buttons($bracket): false|string {
    $bracket_score_link = get_permalink($bracket->id) . 'results/update';
    $leaderboard_link = get_permalink($bracket->id) . 'leaderboard';
    ob_start();
    ?>
    <?php echo BracketsCommon::view_results_btn($bracket); ?>
    <?php echo $bracket->is_voting
      ? BracketListItem::complete_round_button($bracket)
      : BracketListItem::score_bracket_btn($bracket_score_link, $bracket); ?>
    <?php echo BracketsCommon::bracket_chat_btn($bracket->id); ?>
    <?php echo BracketsCommon::leaderboard_btn($leaderboard_link); ?>
    <?php return ob_get_clean();
  }

  public static function private_bracket_buttons($bracket): false|string {
    $bracket_play_link = get_permalink($bracket->id) . 'play';
    ob_start();
    ?>
    <?php echo DashboardCommon::add_to_apparel_btn($bracket_play_link); ?>
    <?php echo self::go_live_btn($bracket); ?>
    <?php return ob_get_clean();
  }

  public static function completed_bracket_buttons($bracket): false|string {
    $play_link = get_permalink($bracket->id) . 'play';
    $leaderboard_link = get_permalink($bracket->id) . 'leaderboard';
    ob_start();
    ?>
    <?php echo BracketsCommon::view_results_btn($bracket); ?>
    <?php echo BracketsCommon::bracket_chat_btn($bracket->id); ?>
    <?php echo BracketsCommon::leaderboard_btn($leaderboard_link); ?>
    <?php return ob_get_clean();
  }

  public static function score_bracket_btn($endpoint, $bracket): false|string {
    if (!current_user_can('wpbb_edit_bracket', $bracket->id)) {
      return '';
    }
    ob_start();
    ?>
    <a
      class="tw-border tw-border-solid tw-border-yellow tw-bg-yellow/15 hover:tw-bg-yellow hover:tw-text-dd-blue tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8 tw-text-white"
      href="<?php echo esc_url($endpoint); ?>">
      <?php echo PartialsCommon::icon('trophy_24'); ?>
      <span class="tw-font-500">Update Results</span>
    </a>
    <?php return ob_get_clean();
  }

  public static function complete_round_button(Bracket $bracket): false|string {
    if (!current_user_can('wpbb_edit_bracket', $bracket->id)) {
      return '';
    }
    ob_start();
    ?>
    <button
      class="wpbb-complete-round-btn tw-text-white tw-border tw-border-solid tw-border-transparent tw-bg-clip-padding tw-px-16 tw-py-12 tw-flex tw-items-center tw-justify-center tw-gap-10 tw-rounded-8 hover:tw-cursor-pointer tw-h-full tw-bg-dd-blue/80 hover:tw-bg-transparent hover:tw-text-dd-blue tw-w-full"
      data-bracket-id="<?php echo $bracket->id; ?>" data-live-round-index="<?php echo $bracket->live_round_index; ?>" data-is-final-round="<?php echo $bracket->live_round_index_is_final() ? 'true' : 'false'; ?>"
    >
      <?php echo PartialsCommon::icon('arrow_right'); ?>
    <span class="tw-font-sans tw-font-700 tw-uppercase">Close round <?php echo $bracket->live_round_index +
      1; ?></span>
    </button>
    <?php return PartialsCommon::gradient_border_wrap(ob_get_clean(), [
      'wpbb-complete-round-gradient-border',
      'tw-rounded-8',
    ]);
  }

  public static function live_bracket_buttons($bracket): false|string {
    $bracket_score_link = get_permalink($bracket->id) . 'results/update';
    $leaderboard_link = get_permalink($bracket->id) . 'leaderboard';
    ob_start();
    ?>
    <?php echo BracketsCommon::play_bracket_btn($bracket, [
      'label' => 'Play',
    ]); ?>
    <?php echo $bracket->is_voting
      ? BracketListItem::complete_round_button($bracket)
      : BracketListItem::score_bracket_btn($bracket_score_link, $bracket); ?>
    <?php echo BracketsCommon::bracket_chat_btn($bracket->id); ?>
    <?php echo BracketsCommon::leaderboard_btn($leaderboard_link); ?>
    <?php return ob_get_clean();
  }
}
