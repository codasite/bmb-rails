<?php

require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-match-pick.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-team.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-bracket-interface.php';

class Wpbb_BracketPlay extends Wpbb_PostBase implements
  Wpbb_PostBracketInterface {
  /**
   * @var int
   */
  public $bracket_id;

  /**
   * @var Wpbb_Bracket| null
   */
  public mixed $bracket;

  /**
   * @var Wpbb_MatchPick[]
   */
  public $picks;

  /**
   * @var int
   */
  public $total_score;

  /**
   * @var float
   */
  public $accuracy_score;

  /**
   * @var int
   */
  public $busted_id;

  /**
   * @var bool
   */
  public $is_printed;

  /**
   * @var Wpbb_BracketPlay
   */
  public $busted_play;

  public function __construct(array $data = []) {
    parent::__construct($data);

    if (!isset($data['bracket_id'])) {
      throw new Exception('bracket_id ');
    }

    parent::__construct($data);
    $this->bracket_id = isset($data['bracket_id'])
      ? (int) $data['bracket_id']
      : null;
    $this->bracket = $data['bracket'] ?? null;
    $this->picks = $data['picks'] ?? [];
    $this->total_score = $data['total_score'] ?? null;
    $this->accuracy_score = $data['accuracy_score'] ?? null;
    $this->busted_id = isset($data['busted_id'])
      ? (int) $data['busted_id']
      : null;
    $this->is_printed = isset($data['is_printed'])
      ? (bool) $data['is_printed']
      : false;
    $this->busted_play = $data['busted_play'] ?? null;
  }

  public static function get_post_type(): string {
    return 'bracket_play';
  }

  public function get_winning_team(): ?Wpbb_Team {
    if (count($this->picks) === 0) {
      return null;
    }
    return $this->picks[count($this->picks) - 1]->winning_team;
  }

  public function get_post_meta(): array {
    return [
      'bracket_id' => $this->bracket_id,
    ];
  }

  public function get_update_post_meta(): array {
    return [];
  }

  /**
   * @throws Wpbb_ValidationException
   */
  public static function from_array($data): Wpbb_BracketPlay {
    validateRequiredFields($data, ['bracket_id', 'author']);
    $picks = [];
    foreach ($data['picks'] as $pick) {
      $picks[] = Wpbb_MatchPick::from_array($pick);
    }
    $data['picks'] = $picks;

    return new Wpbb_BracketPlay($data);
  }

  public function to_array(): array {
    $play = parent::to_array();
    $play['bracket_id'] = $this->bracket_id;
    $play['bracket'] = $this->bracket->to_array();
    $play['total_score'] = $this->total_score;
    $play['accuracy_score'] = $this->accuracy_score;
    $play['busted_id'] = $this->busted_id;
    if (!empty($this->busted_play)) {
      $play['busted_play'] = $this->busted_play->to_array();
    }
    $play['is_printed'] = $this->is_printed;
    if ($this->picks) {
      $play['picks'] = [];
      foreach ($this->picks as $pick) {
        $play['picks'][] = $pick->to_array();
      }
    }
    return $play;
  }

  public function get_post_id(): int {
    return $this->id;
  }

  public function get_matches(): array {
    return $this->bracket->get_matches();
  }

  public function get_picks(): array {
    return $this->picks;
  }

  public function get_title(): string {
    return $this->bracket->title;
  }

  public function get_date(): string {
    return $this->bracket->get_date();
  }

  public function get_num_teams(): int {
    return $this->bracket->get_num_teams();
  }
}
