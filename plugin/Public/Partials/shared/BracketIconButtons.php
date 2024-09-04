<?php
namespace WStrategies\BMB\Public\Partials\shared;

use WStrategies\BMB\Public\Partials\dashboard\DashboardCommon;

class BracketIconButtons {
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

  public static function share_bracket_btn($bracket): false|string {
    $play_link = get_permalink($bracket->id) . 'play';

    return DashboardCommon::icon_btn(
      'Share',
      'share.svg',
      classes: ['wpbb-share-bracket-button'],
      data: [
        'label' => 'Share',
        'play-bracket-url' => $play_link,
        'bracket-title' => $bracket->title,
      ]
    );
  }

  public static function lock_tournament_btn($bracket): false|string {
    if (!current_user_can('wpbb_edit_bracket', $bracket->id)) {
      return '';
    }

    return DashboardCommon::icon_btn(
      'Lock',
      'lock.svg',
      classes: ['wpbb-lock-tournament-button'],
      data: [
        'label' => 'Lock',
        'bracket-id' => $bracket->id,
        'bracket-title' => $bracket->title,
      ]
    );
  }

  public static function delete_bracket_btn($bracket): false|string {
    if (!current_user_can('wpbb_delete_bracket', $bracket->id)) {
      return '';
    }
    $bracket_id = $bracket->id;

    return DashboardCommon::icon_btn(
      'Delete',
      'trash.svg',
      classes: ['wpbb-delete-bracket-button'],
      data: [
        'label' => 'Delete',
        'bracket-id' => $bracket_id,
        'bracket-title' => $bracket->title,
      ]
    );
  }

  public static function scored_bracket_icon_buttons($bracket): false|string {
    ob_start(); ?>
    <?php echo self::most_popular_picks_btn($bracket); ?>
    <?php echo self::edit_bracket_btn($bracket); ?>
    <?php echo self::share_bracket_btn($bracket); ?>
    <?php echo self::duplicate_bracket_btn($bracket); ?>
    <?php echo self::delete_bracket_btn($bracket); ?>
    <?php return ob_get_clean();
  }

  public static function set_fee_btn($bracket): false|string {
    if (!current_user_can('wpbb_add_bracket_fee', $bracket->id)) {
      return '';
    }
    return DashboardCommon::icon_btn(
      'Set Fee',
      'dollar_shield.svg',
      classes: ['wpbb-set-tournament-fee-button'],
      data: [
        'label' => 'Set Fee',
        'bracket-id' => $bracket->id,
        'fee' => $bracket->fee,
      ]
    );
  }

  public static function private_bracket_icon_buttons($bracket): false|string {
    ob_start(); ?>
    <?php echo self::edit_bracket_btn($bracket); ?>
    <?php echo self::set_fee_btn($bracket); ?>
    <?php echo self::duplicate_bracket_btn($bracket); ?>
    <?php echo self::delete_bracket_btn($bracket); ?>
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
      'Edit',
      'pencil.svg',
      classes: ['wpbb-edit-bracket-button'],
      data: [
        'label' => 'Edit',
        'bracket-id' => $id,
        'bracket-title' => $title,
        'bracket-month' => $month,
        'bracket-year' => $year,
      ]
    );
  }

  public static function upcoming_bracket_icon_buttons($bracket) {
    ob_start(); ?>
    <?php echo self::set_fee_btn($bracket); ?>
    <?php echo self::share_bracket_btn($bracket); ?>
    <?php return ob_get_clean();
  }

  public static function live_bracket_icon_buttons($bracket): false|string {
    ob_start(); ?>
    <?php echo self::most_popular_picks_btn($bracket); ?>
    <?php echo self::edit_bracket_btn($bracket); ?>
    <?php echo self::set_fee_btn($bracket); ?>
    <?php echo self::share_bracket_btn($bracket); ?>
    <?php echo self::duplicate_bracket_btn($bracket); ?>
    <?php echo self::lock_tournament_btn($bracket); ?>
    <?php echo self::delete_bracket_btn($bracket); ?>
    <?php echo self::more_options_btn($bracket); ?>
    <?php return ob_get_clean();
  }

  public static function duplicate_bracket_btn($bracket): false|string {
    if (!current_user_can('wpbb_edit_bracket', $bracket->id)) {
      return '';
    }
    $copy_link = get_permalink($bracket->id) . 'copy';

    return DashboardCommon::icon_link('Duplicate', 'copy.svg', $copy_link);
  }

  public static function most_popular_picks_btn($bracket): false|string {
    $copy_link = get_permalink($bracket->id) . 'most-popular-picks';

    return DashboardCommon::icon_link(
      'Most Popular',
      'percent.svg',
      $copy_link
    );
  }

  public static function more_options_btn(): false|string {
    return DashboardCommon::icon_btn(
      '',
      'ellipsis',
    );

  }
}
