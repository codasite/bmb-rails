<?php
namespace WStrategies\BMB\Includes\Repository;

use wpdb;
use WStrategies\BMB\Includes\Domain\MatchPick;

class BracketMatchPickRepo implements CustomTableInterface {
  /**
   * @var BracketTeamRepo
   */
  private $team_repo;

  /**
   * @var wpdb
   */
  private $wpdb;

  public function __construct(BracketTeamRepo $team_repo) {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->team_repo = $team_repo;
  }

  public function get_pick(int $pick_id): ?MatchPick {
    $table_name = self::table_name();
    $sql = "SELECT * FROM $table_name WHERE id = $pick_id";
    $data = $this->wpdb->get_row($sql, ARRAY_A);
    if (!$data) {
      return null;
    }
    $winning_team_id = $data['winning_team_id'];
    $winning_team = $this->team_repo->get($winning_team_id);
    return new MatchPick([
      'round_index' => $data['round_index'],
      'match_index' => $data['match_index'],
      'winning_team_id' => $winning_team_id,
      'id' => $data['id'],
      'winning_team' => $winning_team,
    ]);
  }

  public function get_picks(int $play_id): array {
    $table_name = self::table_name();
    $where = $play_id ? "WHERE bracket_play_id = $play_id" : '';
    $sql = "SELECT * FROM $table_name $where ORDER BY round_index, match_index ASC";
    $data = $this->wpdb->get_results($sql, ARRAY_A);

    $picks = [];
    foreach ($data as $pick) {
      $winning_team_id = $pick['winning_team_id'];
      $winning_team = $this->team_repo->get($winning_team_id);
      $picks[] = new MatchPick([
        'round_index' => $pick['round_index'],
        'match_index' => $pick['match_index'],
        'winning_team_id' => $winning_team_id,
        'id' => $pick['id'],
        'winning_team' => $winning_team,
      ]);
    }
    return $picks;
  }

  public function insert_picks(int $play_id, array $picks): void {
    foreach ($picks as $pick) {
      $this->insert_pick($play_id, $pick);
    }
  }

  public function insert_pick(int $play_id, MatchPick $pick): void {
    $table_name = self::table_name();
    $this->wpdb->insert($table_name, [
      'id' => $pick->id,
      'bracket_play_id' => $play_id,
      'round_index' => $pick->round_index,
      'match_index' => $pick->match_index,
      'winning_team_id' => $pick->winning_team_id,
    ]);
  }

  public static function table_name(): string {
    return CustomTableNames::table_name('match_picks');
  }

  public static function create_table(): void {
    /**
     * Create the match picks table. Rows in this table represent a user's pick for a match.
     * Holds a pointer to the bracket play this pick belongs to.
     */

    global $wpdb;
    $table_name = self::table_name();
    $plays_table = PlayRepo::table_name();
    $teams_table = BracketTeamRepo::table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			bracket_play_id bigint(20) UNSIGNED NOT NULL,
			round_index tinyint(4) NOT NULL,
			match_index tinyint(4) NOT NULL,
			winning_team_id bigint(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_play_id) REFERENCES {$plays_table}(id) ON DELETE CASCADE,
			FOREIGN KEY (winning_team_id) REFERENCES {$teams_table}(id) ON DELETE CASCADE
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
