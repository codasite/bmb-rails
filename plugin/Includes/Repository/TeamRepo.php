<?php
namespace WStrategies\BMB\Includes\Repository;

use wpdb;
use WStrategies\BMB\Includes\Domain\Team;

/**
 * Repository for BracketMatches, Match Picks, and Teams
 */
class TeamRepo implements CustomTableInterface {
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

    $table_name = self::table_name();
    $team = $this->wpdb->get_row(
      $this->wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
      ARRAY_A
    );
    return new Team($team);
  }

  public function get_all(): array {
    $table_name = self::table_name();
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

    $table_name = self::table_name();
    $this->wpdb->insert($table_name, [
      'id' => $team->id,
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
        self::table_name(),
        ['name' => $new_team->name],
        ['id' => $id]
      );
      $updated = $this->get($id);
      return $this->get($id);
    } else {
    }
    return $old_team;
  }

  public static function table_name(): string {
    return CustomTableNames::table_name('teams');
  }

  public static function create_table(): void {
    /**
     * Create the teams table
     */

    global $wpdb;
    $table_name = self::table_name();
    $charset_collate = $wpdb->get_charset_collate();
    $brackets_table = BracketRepo::table_name();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			bracket_id bigint(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$brackets_table}(id) ON DELETE CASCADE
		) $charset_collate;";

    // import dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  public static function drop_table(): void {
    global $wpdb;
    $table_name = self::table_name();
    $sql = "DROP TABLE IF EXISTS {$table_name}";
    $wpdb->query($sql);
  }
}
