<?php

class Wpbb_BracketConfig {
  /**
   * @var int
   */
  public $play_id;

  /**
   * @var string
   */
  public $theme_mode;

  /**
   * @var string
   */
  public $img_url;

  /**
   * @var string
   */
  public $bracket_placement;

  public function __construct(
    string $play_id,
    string $theme_mode,
    string $bracket_placement,
    string $img_url
  ) {
    $this->play_id = $play_id;
    $this->theme_mode = $theme_mode;
    $this->img_url = $img_url;
    $this->bracket_placement = $bracket_placement;
  }
}
