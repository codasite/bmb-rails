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
   * @var Utils
   */
  private $utils;

  /**
   * @var wpdb
   */
  private $wpdb;

  public function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->utils = new Utils();
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

  public function update(int $id, Team $team): ?Team {
    throw new Exception('Not implemented');
  }

  public function team_table(): string {
    return $this->wpdb->prefix . 'bracket_builder_teams';
  }
}
