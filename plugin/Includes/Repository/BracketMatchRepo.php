<?php
namespace WStrategies\BMB\Includes\Repository;

use wpdb;
use WStrategies\BMB\Includes\Domain\BracketMatch;

class BracketMatchRepo implements CustomTableInterface {
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

  public function insert_matches(int $bracket_id, array $matches): void {
    $table_name = self::table_name();
    foreach ($matches as $match) {
      // Skip if match is null
      if ($match === null) {
        continue;
      }
      // First, insert teams
      $team1 = $this->team_repo->add($bracket_id, $match->team1);
      $team2 = $this->team_repo->add($bracket_id, $match->team2);

      $this->wpdb->insert($table_name, [
        'bracket_id' => $bracket_id,
        'round_index' => $match->round_index,
        'match_index' => $match->match_index,
        'team1_id' => $team1?->id,
        'team2_id' => $team2?->id,
      ]);
      $match->id = $this->wpdb->insert_id;
    }
  }

  public function get_matches(int $bracket_id): array {
    $table_name = self::table_name();
    $where = $bracket_id ? "WHERE bracket_id = $bracket_id" : '';
    $match_results = $this->wpdb->get_results(
      "SELECT * FROM {$table_name} $where ORDER BY round_index, match_index ASC",
      ARRAY_A
    );
    $matches = [];
    foreach ($match_results as $match) {
      $team1 = $this->team_repo->get($match['team1_id']);
      $team2 = $this->team_repo->get($match['team2_id']);

      $matches[] = new BracketMatch([
        'round_index' => $match['round_index'],
        'match_index' => $match['match_index'],
        'team1' => $team1,
        'team2' => $team2,
        'id' => $match['id'],
      ]);
    }

    return $matches;
  }

  public static function table_name(): string {
    return CustomTableNames::table_name('matches');
  }

  public static function create_table(): void {
    global $wpdb;
    $table_name = self::table_name();
    $brackets_table = BracketRepo::table_name();
    $teams_table = BracketTeamRepo::table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	 		bracket_id bigint(20) UNSIGNED NOT NULL,
			round_index tinyint(4) NOT NULL,
			match_index tinyint(4) NOT NULL,
			team1_id bigint(20) UNSIGNED,
			team2_id bigint(20) UNSIGNED,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$brackets_table}(id) ON DELETE CASCADE,
			FOREIGN KEY (team1_id) REFERENCES {$teams_table}(id) ON DELETE SET NULL,
			FOREIGN KEY (team2_id) REFERENCES {$teams_table}(id) ON DELETE SET NULL
		) $charset_collate;";

    // import dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  public static function drop_table(): void {
    global $wpdb;
    $table_name = self::table_name();
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
  }
}
