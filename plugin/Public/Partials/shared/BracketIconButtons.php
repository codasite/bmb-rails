<?php
namespace WStrategies\BMB\Public\Partials\shared;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Public\Partials\dashboard\DashboardCommon;

class BracketIconButtons {
  private static array $OPTION_TO_BUTTON_METHOD = [
    BracketOptions::MOST_POPULAR_PICKS => 'most_popular_picks_btn',
    BracketOptions::EDIT_BRACKET => 'edit_bracket_btn',
    BracketOptions::SET_FEE => 'set_fee_btn',
    BracketOptions::SHARE_BRACKET => 'share_bracket_btn',
    BracketOptions::DUPLICATE_BRACKET => 'duplicate_bracket_btn',
    BracketOptions::LOCK_TOURNAMENT => 'lock_tournament_btn',
    BracketOptions::DELETE_BRACKET => 'delete_bracket_btn',
  ];

  public static function get_bracket_icon_buttons_for_status(
    $bracket
  ): false|string {
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

  public static function live_bracket_icon_buttons($bracket): false|string {
    return self::get_bracket_icon_buttons($bracket, [
      BracketOptions::MOST_POPULAR_PICKS,
      BracketOptions::EDIT_BRACKET,
      BracketOptions::SHARE_BRACKET,
      BracketOptions::DUPLICATE_BRACKET,
      BracketOptions::SET_FEE,
      BracketOptions::LOCK_TOURNAMENT,
      BracketOptions::DELETE_BRACKET,
    ]);
  }

  public static function private_bracket_icon_buttons($bracket): false|string {
    return self::get_bracket_icon_buttons($bracket, [
      BracketOptions::EDIT_BRACKET,
      BracketOptions::DUPLICATE_BRACKET,
      BracketOptions::SET_FEE,
      BracketOptions::DELETE_BRACKET,
    ]);
  }

  public static function scored_bracket_icon_buttons($bracket): false|string {
    return self::get_bracket_icon_buttons($bracket, [
      BracketOptions::MOST_POPULAR_PICKS,
      BracketOptions::EDIT_BRACKET,
      BracketOptions::SHARE_BRACKET,
      BracketOptions::DUPLICATE_BRACKET,
      BracketOptions::DELETE_BRACKET,
    ]);
  }

  public static function upcoming_bracket_icon_buttons($bracket): false|string {
    return self::get_bracket_icon_buttons($bracket, [
      BracketOptions::EDIT_BRACKET,
      BracketOptions::SHARE_BRACKET,
      BracketOptions::DUPLICATE_BRACKET,
      BracketOptions::SET_FEE,
      BracketOptions::DELETE_BRACKET,
    ]);
  }

  public static function get_bracket_icon_buttons(
    Bracket $bracket,
    array $options
  ): false|string {
    $visible_options = array_slice($options, 0, 3);
    ob_start();
    echo self::get_visible_option_buttons($bracket, $visible_options);
    if (count($options) > 0) {
      echo self::more_options_btn($bracket, $options);
    }
    return ob_get_clean();
  }

  public static function get_visible_option_buttons(
    Bracket $bracket,
    array $options
  ): false|string {
    $config = new MoreOptionsConfig($bracket, $options);
    ob_start();

    foreach (self::$OPTION_TO_BUTTON_METHOD as $option => $buttonMethod) {
      if ($config->should_show_option($option)) {
        echo self::{$buttonMethod}($bracket);
      }
    }

    return ob_get_clean();
  }

  public static function share_bracket_btn($bracket): false|string {
    if (
      !BracketOptionPermissions::user_can_perform_action(
        BracketOptions::SHARE_BRACKET,
        $bracket
      )
    ) {
      return '';
    }

    return DashboardCommon::icon_btn(
      'Share',
      'share.svg',
      classes: ['wpbb-share-bracket-button'],
      data: [
        'label' => 'Share',
        'play-bracket-url' => $bracket->url . 'play',
        'bracket-title' => $bracket->title,
        'bracket-id' => $bracket->id,
      ]
    );
  }

  public static function lock_tournament_btn($bracket): false|string {
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
    return DashboardCommon::icon_btn(
      'Delete',
      'trash.svg',
      classes: ['wpbb-delete-bracket-button'],
      data: [
        'label' => 'Delete',
        'bracket-id' => $bracket->id,
        'bracket-title' => $bracket->title,
      ]
    );
  }

  public static function set_fee_btn($bracket): false|string {
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

  public static function edit_bracket_btn($bracket): false|string {
    return DashboardCommon::icon_btn(
      'Edit',
      'pencil.svg',
      classes: ['wpbb-edit-bracket-button'],
      data: [
        'label' => 'Edit',
        'bracket-id' => $bracket->id,
        'bracket-title' => $bracket->title,
        'bracket-month' => $bracket->month,
        'bracket-year' => $bracket->year,
      ]
    );
  }

  public static function duplicate_bracket_btn($bracket): false|string {
    return DashboardCommon::icon_link(
      'Duplicate',
      'copy.svg',
      $bracket->url . 'copy'
    );
  }

  public static function most_popular_picks_btn($bracket): false|string {
    return DashboardCommon::icon_link(
      'Most Popular',
      'percent.svg',
      $bracket->url . 'most-popular-picks'
    );
  }

  public static function more_options_btn(
    $bracket,
    $options = []
  ): false|string {
    $config = new MoreOptionsConfig($bracket, $options);

    return DashboardCommon::icon_btn(
      '',
      'ellipsis',
      classes: ['wpbb-more-options-button'],
      data: [
        'most-popular-picks' => $config->should_show_option_string(
          BracketOptions::MOST_POPULAR_PICKS
        ),
        'share-bracket' => $config->should_show_option_string(
          BracketOptions::SHARE_BRACKET
        ),
        'edit-bracket' => $config->should_show_option_string(
          BracketOptions::EDIT_BRACKET
        ),
        'duplicate-bracket' => $config->should_show_option_string(
          BracketOptions::DUPLICATE_BRACKET
        ),
        'lock-tournament' => $config->should_show_option_string(
          BracketOptions::LOCK_TOURNAMENT
        ),
        'delete-bracket' => $config->should_show_option_string(
          BracketOptions::DELETE_BRACKET
        ),
        'bracket-id' => $bracket->id,
        'bracket-title' => $bracket->title,
        'bracket-year' => $bracket->year,
        'bracket-month' => $bracket->month,
        'fee' => $bracket->fee,
        'play-bracket-url' => $bracket->url,
        'copy-bracket-url' => $bracket->url . 'copy',
        'most-popular-picks-url' => $bracket->url . 'most-popular-picks',
      ]
    );
  }
}
