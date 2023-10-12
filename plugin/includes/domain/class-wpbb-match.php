<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-team.php';

class Wpbb_Match {
  /**
   * @var int
   */
  public $id;

  /**
   * @var int
   */
  public $round_index;

  /**
   * @var int
   */
  public $match_index;

  /**
   * @var Wpbb_Team
   */
  public $team1;

  /**
   * @var Wpbb_Team
   */
  public $team2;

  public function __construct($args) {
    $this->round_index = (int) $args['round_index'];
    $this->match_index = (int) $args['match_index'];
    $this->team1 = $args['team1'] ?? null;
    $this->team2 = $args['team2'] ?? null;
    $this->id = $args['id'] ?? null;
  }

  public static function from_array(array $data): Wpbb_Match {
    if (!isset($data['round_index']) || !isset($data['match_index'])) {
      throw new InvalidArgumentException(
        'round_index and match_index are required'
      );
    }

    if (isset($data['team1'])) {
      $data['team1'] = Wpbb_Team::from_array($data['team1']);
    }

    if (isset($data['team2'])) {
      $data['team2'] = Wpbb_Team::from_array($data['team2']);
    }

    $match = new Wpbb_Match($data);

    return $match;
  }

  public function to_array(): array {
    return [
      'id' => $this->id,
      'round_index' => $this->round_index,
      'match_index' => $this->match_index,
      'team1' => $this->team1 ? $this->team1->to_array() : null,
      'team2' => $this->team2 ? $this->team2->to_array() : null,
    ];
  }
}
