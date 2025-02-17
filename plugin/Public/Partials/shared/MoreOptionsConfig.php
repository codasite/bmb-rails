<?php

namespace WStrategies\BMB\Public\Partials\shared;

class MoreOptionsConfig {
  private $bracket;
  private $options;

  public function __construct($bracket, $options) {
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
