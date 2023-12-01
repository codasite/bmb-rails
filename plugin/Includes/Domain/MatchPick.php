<?php
namespace WStrategies\BMB\Includes\Domain;

class MatchPick {
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
   * @var Team
   */
  public $winning_team;

  /**
   * @var int
   */
  public $winning_team_id;

  public function __construct($data = []) {
    $this->round_index = (int) $data['round_index'];
    $this->match_index = (int) $data['match_index'];
    $this->winning_team_id = (int) $data['winning_team_id'];
    $this->winning_team = $data['winning_team'] ?? null;
    $this->id = isset($data['id']) ? (int) $data['id'] : null;
  }

  public static function from_array($data) {
    if (isset($data['winning_team'])) {
      $data['winning_team'] = Team::from_array($data['winning_team']);
    }

    return new MatchPick($data);
  }

  public function to_array(): array {
    return [
      'id' => $this->id,
      'round_index' => $this->round_index,
      'match_index' => $this->match_index,
      'winning_team_id' => $this->winning_team_id,
      'winning_team' => $this->winning_team
        ? $this->winning_team->to_array()
        : null,
    ];
  }
}
