<?php
namespace WStrategies\BMB\Includes\Repository;

use wpdb;
use WStrategies\BMB\Includes\Domain\Pick;

class PickRepo implements CustomTableInterface {
  /**
   * @var TeamRepo
   */
  private $team_repo;

  /**
   * @var wpdb
   */
  private $wpdb;

  public function __construct(TeamRepo $team_repo) {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->team_repo = $team_repo;
  }

  public function get_pick(int $pick_id): ?Pick {
    $table_name = self::table_name();
    $sql = "SELECT * FROM $table_name WHERE id = $pick_id";
    $data = $this->wpdb->get_row($sql, ARRAY_A);
    if (!$data) {
      return null;
    }
    return $this->pick_from_row($data);
  }

  public function get_picks(int $play_id): array {
    $table_name = self::table_name();
    $where = $play_id ? "WHERE bracket_play_id = $play_id" : '';
    $sql = "SELECT * FROM $table_name $where ORDER BY round_index, match_index ASC";
    $data = $this->wpdb->get_results($sql, ARRAY_A);

    $picks = [];
    foreach ($data as $pick) {
      $picks[] = $this->pick_from_row($pick);
    }
    return $picks;
  }

  public function get_most_popular_picks(int $bracket_id): array {
    $picks_table_name = self::table_name();
    $plays_table_name = PlayRepo::table_name();
    $query = $wpdb->prepare(
      "
    SELECT
        pick.round_index AS round_index,
        pick.match_index AS match_index,
	pick.winning_team_id AS winning_team_id,
        COUNT(*) AS occurrence_count
    FROM
        $picks_table_name pick
    JOIN
	$plays_table_name play ON pick.bracket_play_id = play.id
    WHERE
        play.bracket_id = %d
        AND play.is_tournament_entry = 1
    GROUP BY
        pick.round_index,
        pick.match_index,
        pick.winning_team_id
    ORDER BY
        pick.round_index,
        pick.match_index,
        occurrence_count DESC
",
      $bracket_id
    );
    $data = $this->wpdb->get_results($query, ARRAY_A);
    $picks = [];
    foreach ($data as $pick) {
      $winning_team_id = $row['winning_team_id'];
      $winning_team = $this->team_repo->get($winning_team_id);
      $picks[] = new Pick([
        'round_index' => $row['round_index'],
        'match_index' => $row['match_index'],
        'winning_team_id' => $winning_team_id,
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

  public function insert_pick(int $play_id, Pick $pick): void {
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
    $teams_table = TeamRepo::table_name();
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

  private function pick_from_row(array $row): Pick {
    $winning_team_id = $row['winning_team_id'];
    $winning_team = $this->team_repo->get($winning_team_id);
    return new Pick([
      'round_index' => $row['round_index'],
      'match_index' => $row['match_index'],
      'winning_team_id' => $winning_team_id,
      'id' => $row['id'],
      'winning_team' => $winning_team,
    ]);
  }
}
