<?php
namespace WStrategies\BMB\Includes\Repository;

use Exception;
use wpdb;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Utils;

/**
 * Repository for BracketMatches, Match Picks, and Teams
 */
class BracketTeamRepo {
  /**
   * @var wpdb
   */
  private $wpdb;

  public function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
  }

  /**
   * TEAMS
   */

  public function get(int|null $id): ?Team {
    if ($id === null) {
      return null;
    }

    $table_name = $this->team_table();
    $team = $this->wpdb->get_row(
      $this->wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
      ARRAY_A
    );
    return new Team($team);
  }

  public function get_all(): array {
    $table_name = $this->team_table();
    $team_results = $this->wpdb->get_results(
      "SELECT * FROM {$table_name}",
      ARRAY_A
    );
    $teams = [];
    foreach ($team_results as $team) {
      $teams[] = new Team($team);
    }
    return $teams;
  }

  public function add(int $bracket_id, ?Team $team): ?Team {
    if (empty($team)) {
      return $team;
    }

    $table_name = $this->team_table();
    $this->wpdb->insert($table_name, [
      'name' => $team->name,
      'bracket_id' => $bracket_id,
    ]);
    $team->id = $this->wpdb->insert_id;
    return $team;
  }

  public function update(int|null $id, Team|null $new_team): ?Team {
    if ($id === null || empty($new_team)) {
      return null;
    }
    $old_team = $this->get($id);
    if (!$old_team) {
      return null;
    }
    if ($new_team->name !== $old_team->name) {
      $this->wpdb->update(
        $this->team_table(),
        ['name' => $new_team->name],
        ['id' => $id]
      );
      $updated = $this->get($id);
      return $this->get($id);
    } else {
    }
    return $old_team;
  }

  public function team_table(): string {
    return $this->wpdb->prefix . 'bracket_builder_teams';
  }
}
