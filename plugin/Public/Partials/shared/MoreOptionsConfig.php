<?php

namespace WStrategies\BMB\Public\Partials\shared;

use WStrategies\BMB\Includes\Domain\Bracket;

class MoreOptionsConfig {
  private Bracket $bracket;
  private array $options;

  public function __construct(Bracket $bracket, array $options) {
    $this->bracket = $bracket;
    $this->options = $options;
  }

  public function should_show_option($optionName) {
    return in_array($optionName, $this->options) &&
      BracketOptionPermissions::user_can_perform_action(
        $optionName,
        $this->bracket
      );
  }

  public function should_show_option_string($optionName) {
    return $this->should_show_option($optionName) ? 'true' : 'false';
  }
}
