<?php
namespace WStrategies\BMB\Includes\Repository;

use DateTimeImmutable;
use Exception;
use wpdb;
use WStrategies\BMB\Includes\Domain\MatchResult;

class BracketResultsRepo implements CustomTableInterface {
  /**
   * @var TeamRepo
   */
  private $team_repo;

  /**
   * @var BracketRepo
   */
  private BracketRepo $bracket_repo;

  /**
   * @var wpdb
   */
  private $wpdb;

  public const RESULTS_NOTIFICATIONS_SENT_AT_META_KEY = 'results_notifications_sent_at';

  public function __construct(BracketRepo $bracket_repo, TeamRepo $team_repo) {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->bracket_repo = $bracket_repo;
    $this->team_repo = $team_repo;
  }

  public function insert_results(int $bracket_id, array $results): void {
    $this->wpdb->query('START TRANSACTION');
    try {
      foreach ($results as $result) {
        $this->insert_result($bracket_id, $result);
      }
      // assuming all went well, update the results_first_updated_at field
      $this->bracket_repo->update_custom_table_data(
        $bracket_id,
        [
          'results_first_updated_at' => (new DateTimeImmutable())->format(
            'Y-m-d H:i:s'
          ),
        ],
        false
      );
      $this->wpdb->query('COMMIT');
    } catch (Exception $e) {
      $this->wpdb->query('ROLLBACK');
      throw $e;
    }
  }

  public function insert_result(int $bracket_id, MatchResult $result): void {
    $table_name = self::table_name();
    $this->wpdb->insert($table_name, [
      'id' => $result->id,
      'bracket_id' => $bracket_id,
      'round_index' => $result->round_index,
      'match_index' => $result->match_index,
      'winning_team_id' => $result->winning_team_id,
      'winning_team_pick_percent' => $result->winning_team_pick_percent,
    ]);
  }

  public function update_results(
    int $bracket_id,
    array|null $new_results
  ): void {
    if ($new_results === null) {
      return;
    }

    $old_results = $this->get_results($bracket_id);

    if (empty($old_results)) {
      $this->insert_results($bracket_id, $new_results);
      return;
    }

    $this->wpdb->query('START TRANSACTION');

    try {
      foreach ($new_results as $new_result) {
        $pick_exists = false;
        foreach ($old_results as $old_result) {
          if (
            $new_result->round_index === $old_result->round_index &&
            $new_result->match_index === $old_result->match_index
          ) {
            $pick_exists = true;
            if ($new_result->winning_team_id !== $old_result->winning_team_id) {
              $this->wpdb->update(
                self::table_name(),
                [
                  'winning_team_id' => $new_result->winning_team_id,
                  'winning_team_pick_percent' =>
                    $new_result->winning_team_pick_percent,
                ],
                [
                  'id' => $old_result->id,
                ]
              );
            }
          }
        }
        if (!$pick_exists) {
          $this->insert_result($bracket_id, $new_result);
        }
      }
      $this->wpdb->query('COMMIT');
    } catch (Exception $e) {
      $this->wpdb->query('ROLLBACK');
      throw $e;
    }
  }

  public function get_results(int|null $bracket_id): array {
    $table_name = self::table_name();
    $where = $bracket_id ? "WHERE bracket_id = $bracket_id" : '';
    $sql = "SELECT * FROM $table_name $where ORDER BY round_index, match_index ASC";
    $data = $this->wpdb->get_results($sql, ARRAY_A);

    $bracket_results = [];
    foreach ($data as $result) {
      $winning_team_id = $result['winning_team_id'];
      $winning_team = $this->team_repo->get($winning_team_id);
      $bracket_results[] = new MatchResult([
        'round_index' => $result['round_index'],
        'match_index' => $result['match_index'],
        'winning_team_id' => $winning_team_id,
        'id' => $result['id'],
        'winning_team' => $winning_team,
        'updated_at' => isset($result['updated_at'])
          ? new DateTimeImmutable($result['updated_at'])
          : null,
        'winning_team_pick_percent' => $result['winning_team_pick_percent'],
      ]);
    }

    return $bracket_results;
  }

  public static function table_name(): string {
    return CustomTableNames::table_name('bracket_results');
  }

  public static function create_table(): void {
    /**
     * Create the match picks table. Rows in this table represent a user's pick for a match.
     * Holds a pointer to the bracket play this pick belongs to.
     */

    global $wpdb;
    $table_name = self::table_name();
    $brackets_table = BracketRepo::table_name();
    $teams_table = TeamRepo::table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			bracket_id bigint(20) UNSIGNED NOT NULL,
			round_index tinyint(4) NOT NULL,
			match_index tinyint(4) NOT NULL,
			winning_team_id bigint(20) UNSIGNED NOT NULL,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      /**
      * Percentage of players who picked this team to win the match.
      */
      winning_team_pick_percent DECIMAL(6, 5),
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$brackets_table}(id) ON DELETE CASCADE,
			FOREIGN KEY (winning_team_id) REFERENCES {$teams_table}(id) ON DELETE CASCADE
		) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  public static function drop_table(): void {
    global $wpdb;
    $table_name = self::table_name();
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
  }
}
