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

  public function __construct(array $data = []) {
    parent::__construct($data);

    if (!isset($data['bracket_id'])) {
      throw new Exception('bracket_id ');
    }

    parent::__construct($data);
    $this->bracket_id = $data['bracket_id'];
    $this->bracket = $data['bracket'] ?? null;
    $this->picks = $data['picks'] ?? [];
    $this->total_score = $data['total_score'] ?? null;
    $this->accuracy_score = $data['accuracy_score'] ?? null;
    $this->busted_id = $data['busted_id'] ?? null;
    $this->is_printed = $data['is_printed'] ?? false;
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
    validateRequiredFields($data, ['bracket_id', 'author', 'picks']);
    $picks = [];
    foreach ($data['picks'] as $pick) {
      $picks[] = Wpbb_MatchPick::from_array($pick);
    }
    $data['picks'] = $picks;

    return new Wpbb_BracketPlay($data);
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
    // return $this->bracket->date;
    return $this->bracket->get_date();
  }

  public function get_num_teams(): int {
    return $this->bracket->get_num_teams();
  }
}
