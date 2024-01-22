<?php

namespace WStrategies\BMB\Public\Partials\shared;

use WStrategies\BMB\Public\Partials\dashboard\DashboardCommon;
use WStrategies\BMB\Public\Partials\shared\BracketsCommon;

class BracketListItem {
  public static function bracket_list_item($bracket): false|string {
    $title = $bracket->title;
    $num_teams = $bracket->num_teams;
    $num_plays = $bracket->num_plays;
    ob_start();
    ?>
    <div class="tw-border-2 tw-border-solid <?php echo $bracket->status ==
    'publish'
      ? 'tw-border-blue'
      : 'tw-border-white/15'; ?> tw-bg-dd-blue tw-flex tw-flex-col tw-gap-10 tw-p-30 tw-rounded-16">
      <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between sm:tw-items-center tw-gap-8">
        <span class="tw-font-500 tw-text-12"><?php echo esc_html(
          $num_teams
        ); ?>-Team Bracket</span>
        <div class="tw-flex tw-gap-4 tw-items-center">
          <?php echo BracketsCommon::get_bracket_tag($bracket->status); ?>
          <?php echo file_get_contents(
            WPBB_PLUGIN_DIR . 'Public/assets/icons/bar_chart.svg'
          ); ?>
          <span class="tw-font-500 tw-text-20 tw-text-white"><?php echo esc_html(
            $num_plays
          ); ?></span>
          <span class="tw-font-500 tw-text-20 tw-text-white/50">Plays</span>
        </div>
      </div>
      <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-gap-15">
        <h2 class="tw-text-white tw-font-700 tw-text-20 sm:tw-text-30"><?php echo esc_html(
          $title
        ); ?></h2>
        <div class="tw-flex tw-gap-10 tw-items-center">
          <?php echo self::get_bracket_icon_buttons($bracket); ?>
        </div>
      </div>
      <div class="tw-mt-10 tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16 tw-flex-wrap">
        <?php echo self::get_bracket_buttons($bracket); ?>
      </div>
    </div>
    <?php return ob_get_clean();
  }

  public static function share_bracket_btn($bracket): false|string {
    $play_link = get_permalink($bracket->id) . 'play';

    return DashboardCommon::icon_btn(
      'share.svg',
      'submit',
      classes: 'wpbb-share-bracket-button',
      attributes: "data-play-bracket-url='$play_link' data-bracket-title='$bracket->title'"
    );
  }

  public static function go_live_btn(string $id): false|string {
    if (!current_user_can('wpbb_edit_bracket', $id)) {
      return '';
    }
    ob_start();
    ?>
    <button data-bracket-id="<?php echo $id; ?>"
            class="wpbb-publish-bracket-button tw-border tw-border-solid tw-border-blue tw-bg-blue/15 tw-min-w-[190px] tw-px-16 tw-py-12 tw-flex tw-gap-10 tw-items-center tw-justify-center tw-rounded-8 hover:tw-bg-blue tw-font-sans tw-text-white tw-uppercase tw-cursor-pointer">
      <?php echo file_get_contents(
        WPBB_PLUGIN_DIR . 'Public/assets/icons/signal.svg'
      ); ?>
      <span class="tw-font-700">Go Live</span>
    </button>
    <?php return ob_get_clean();
  }

  public static function live_bracket_buttons($bracket): false|string {
    $bracket_play_link = get_permalink($bracket->id) . 'play';
    $bracket_score_link = get_permalink($bracket->id) . 'results';
    $leaderboard_link = get_permalink($bracket->id) . 'leaderboard';
    ob_start();
    ?>
    <?php echo BracketsCommon::play_bracket_btn($bracket_play_link, 'Play'); ?>
    <?php echo self::score_bracket_btn($bracket_score_link, $bracket); ?>
    <?php echo BracketsCommon::bracket_chat_btn($bracket->id); ?>
    <?php echo BracketsCommon::leaderboard_btn($leaderboard_link); ?>
    <?php return ob_get_clean();
  }

  public static function unpublish_bracket_btn($bracket): false|string {
    if (!current_user_can('wpbb_edit_bracket', $bracket->id)) {
      return '';
    }
    return DashboardCommon::icon_btn(
      'lock.svg',
      'submit',
      classes: 'wpbb-unpublish-bracket-button',
      attributes: "data-bracket-id='$bracket->id'"
    );
  }

  public static function scored_bracket_buttons($bracket): false|string {
    $bracket_play_link = get_permalink($bracket->id) . 'play';
    $bracket_score_link = get_permalink($bracket->id) . 'results';
    $leaderboard_link = get_permalink($bracket->id) . 'leaderboard';
    ob_start();
    ?>
    <?php echo DashboardCommon::add_to_apparel_btn($bracket_play_link); ?>
    <?php echo self::score_bracket_btn($bracket_score_link, $bracket); ?>
    <?php echo BracketsCommon::bracket_chat_btn($bracket->id); ?>
    <?php echo BracketsCommon::leaderboard_btn($leaderboard_link); ?>
    <?php return ob_get_clean();
  }

  public static function get_bracket_icon_buttons($bracket): false|string {
    switch ($bracket->status) {
      case 'publish':
        return self::live_bracket_icon_buttons($bracket);
      case 'archive':
      case 'private':
        return self::private_bracket_icon_buttons($bracket);
      case 'score':
      case 'complete':
        return self::scored_bracket_icon_buttons($bracket);
      case 'upcoming':
        return self::upcoming_bracket_icon_buttons($bracket);
      default:
        return '';
    }
  }

  public static function delete_bracket_btn($bracket): false|string {
    if (!current_user_can('wpbb_delete_bracket', $bracket->id)) {
      return '';
    }
    $bracket_id = $bracket->id;

    return DashboardCommon::icon_btn(
      'trash.svg',
      'submit',
      classes: 'wpbb-delete-bracket-button',
      attributes: "data-bracket-id='$bracket_id' data-bracket-title='$bracket->title'"
    );
  }

  public static function scored_bracket_icon_buttons($bracket): false|string {
    ob_start(); ?>
    <?php echo self::edit_bracket_btn($bracket); ?>
    <?php echo self::share_bracket_btn($bracket); ?>
    <?php echo self::duplicate_bracket_btn($bracket); ?>
    <?php echo self::delete_bracket_btn($bracket); ?>
    <?php return ob_get_clean();
  }

  public static function private_bracket_buttons($bracket): false|string {
    $bracket_play_link = get_permalink($bracket->id) . 'play';
    ob_start();
    ?>
    <?php echo DashboardCommon::add_to_apparel_btn($bracket_play_link); ?>
    <?php echo self::go_live_btn($bracket->id); ?>
    <?php return ob_get_clean();
  }

  public static function edit_bracket_btn($bracket): false|string {
    if (!current_user_can('wpbb_edit_bracket', $bracket->id)) {
      return '';
    }
    $id = $bracket->id;
    $title = $bracket->title;
    $month = $bracket->month;
    $year = $bracket->year;

    return DashboardCommon::icon_btn(
      'pencil.svg',
      'submit',
      classes: 'wpbb-edit-bracket-button',
      attributes: "data-bracket-id='$id' data-bracket-title='$title' data-bracket-month='$month' data-bracket-year='$year'"
    );
  }

  public static function private_bracket_icon_buttons($bracket): false|string {
    ob_start(); ?>
    <?php echo self::edit_bracket_btn($bracket); ?>
    <?php echo self::duplicate_bracket_btn($bracket); ?>
    <?php echo self::delete_bracket_btn($bracket); ?>
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

  public static function duplicate_bracket_btn($bracket): false|string {
    if (!current_user_can('wpbb_edit_bracket', $bracket->id)) {
      return '';
    }
    $copy_link = get_permalink($bracket->id) . 'copy';

    return DashboardCommon::icon_link('copy.svg', $copy_link);
  }

  public static function completed_bracket_buttons($bracket): false|string {
    $play_link = get_permalink($bracket->id) . 'play';
    $leaderboard_link = get_permalink($bracket->id) . 'leaderboard';
    ob_start();
    ?>
    <?php echo DashboardCommon::add_to_apparel_btn($play_link); ?>
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
      <?php echo file_get_contents(
        WPBB_PLUGIN_DIR . 'Public/assets/icons/trophy_24.svg'
      ); ?>
      <span class="tw-font-500">Score tournament</span>
    </a>
    <?php return ob_get_clean();
  }

  public static function live_bracket_icon_buttons($bracket): false|string {
    ob_start(); ?>
    <?php echo self::edit_bracket_btn($bracket); ?>
    <?php echo self::share_bracket_btn($bracket); ?>
    <?php echo self::duplicate_bracket_btn($bracket); ?>
    <?php echo self::unpublish_bracket_btn($bracket); ?>
    <?php echo self::delete_bracket_btn($bracket); ?>
    <?php return ob_get_clean();
  }

  private static function upcoming_bracket_icon_buttons($bracket) {
    ob_start(); ?>
    <?php echo self::share_bracket_btn($bracket); ?>
    <?php return ob_get_clean();
  }
}
