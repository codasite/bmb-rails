<?php
namespace WStrategies\BMB\Includes\Domain;

class Team {
  /**
   * @var int
   */
  public $id;

  /**
   * @var string
   */
  public $name;

  public function __construct($args = []) {
    $this->id = isset($args['id']) ? (int) $args['id'] : null;
    $this->name = $args['name'] ?? null;
  }

  public static function from_array(array $data): Team {
    $team = new Team($data);

    return $team;
  }

  public function to_array(): array {
    return [
      'id' => $this->id,
      'name' => $this->name,
    ];
  }

  /**
   * Returns a map of team ids to teams
   * @param array<Team> $teams
   * @return array<int, Team>
   */
  public static function get_team_id_map(array $teams): array {
    $team_id_map = [];

    foreach ($teams as $team) {
      $team_id_map[$team->id] = $team;
    }

    return $team_id_map;
  }

  public function equals(Team|int $team): bool {
    if ($team instanceof Team) {
      return $this->id === $team->id;
    } elseif (is_int($team)) {
      return $this->id === $team;
    }
  }
}
