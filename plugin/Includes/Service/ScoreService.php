<?php
namespace WStrategies\BMB\Includes\Service;

use DateTimeImmutable;
use Exception;
use wpdb;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketMatchPickRepo;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketResultsRepo;
use WStrategies\BMB\Includes\Utils;

class ScoreService implements ScoreServiceInterface {
  /**
   * This method scores bracket plays against bracket results
   */

  /**
   * @var BracketPlayRepo
   */
  public $play_repo;

  /**
   * @var BracketRepo
   */
  public $bracket_repo;

  /**
   * @var wpdb
   */
  private $wpdb;

  /**
   * @var Utils
   */
  private $utils;

  /**
   * @var bool
   */
  private $ignore_late_plays;

  public function __construct($opts = []) {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->play_repo = new BracketPlayRepo();
    $this->bracket_repo = new BracketRepo();
    $this->utils = new Utils();
    $this->ignore_late_plays = $opts['ignore_late_plays'] ?? true;
  }

  /**
   * @param Bracket|int|null $bracket
   * @return int returns the number of plays scored
   */
  public function score_bracket_plays(Bracket|int|null $bracket): int {
    try {
      $bracket = $this->get_bracket($bracket);
      $affected_rows = $this->score_plays($bracket);
      if ($bracket->status === 'complete') {
        $this->set_winners($bracket);
      }
      return $affected_rows;
    } catch (Exception $e) {
      return 0;
    }
  }

  private function get_bracket(Bracket|int|null $bracket) {
    if (is_int($bracket)) {
      $bracket = $this->bracket_repo->get($bracket);
    }
    if (!$bracket instanceof Bracket) {
      throw new Exception('Cannot find bracket');
    }
    return $bracket;
  }

  private function score_plays(Bracket $bracket): int {
    $point_values = [1, 2, 4, 8, 16, 32];

    $bracket_data = $this->bracket_repo->get_custom_table_data($bracket->id);
    $bracket_id = $bracket_data['id'];

    if (!$bracket_id) {
      throw new Exception('Cannot find bracket id');
    }

    $num_rounds = $bracket->get_num_rounds();

    if (!$num_rounds || $num_rounds < 1) {
      throw new Exception('Cannot find number of rounds');
    }

    $high_score = $bracket->highest_possible_score();

    $plays_table = $this->play_repo->table_name();

    $join = $this->get_join_clause($bracket_id, $num_rounds);
    $total_score_exp = $this->get_total_score_exp($num_rounds, $point_values);
    $where = $this->get_where_clause(
      $bracket_id,
      $bracket->results_first_updated_at
    );

    $sql = "
        UPDATE $plays_table p0
        $join
        SET p0.total_score = COALESCE($total_score_exp, 0),
          p0.accuracy_score = COALESCE($total_score_exp, 0) / $high_score
        $where
      ";

    $this->wpdb->query($sql);
    return $this->wpdb->rows_affected;
  }

  private function get_join_clause($bracket_id, $num_rounds): string {
    $picks_table = BracketMatchPickRepo::table_name();
    $results_table = BracketResultsRepo::table_name();
    $posts_table = $this->wpdb->posts;

    $num_correct_select = $this->get_num_correct_select($num_rounds);

    $join = "
        LEFT JOIN (
          SELECT p1.bracket_play_id, $num_correct_select
          FROM $picks_table p1
          JOIN $results_table p2 ON p1.round_index = p2.round_index
          AND p1.match_index = p2.match_index
          AND p1.winning_team_id = p2.winning_team_id
          AND p2.bracket_id = $bracket_id
          GROUP BY p1.bracket_play_id
        ) agg ON p0.id = agg.bracket_play_id
    ";

    if ($this->ignore_late_plays) {
      $join .= "
        JOIN $posts_table p3 ON p0.post_id = p3.ID
      ";
    }

    return $join;
  }

  private function get_num_correct_select($num_rounds): string {
    $num_correct_arr = [];

    for ($i = 0; $i < $num_rounds; $i++) {
      $num_correct_arr[] = "COALESCE(SUM(CASE WHEN p1.round_index = $i THEN 1 ELSE 0 END), 0) AS round{$i}correct";
    }

    $num_correct_select = implode(', ', $num_correct_arr);
    return $num_correct_select;
  }

  private function get_total_score_exp($num_rounds, $point_values): string {
    $total_score_arr = [];

    for ($i = 0; $i < $num_rounds; $i++) {
      $total_score_arr[] = "agg.round{$i}correct * {$point_values[$i]}";
    }

    $total_score_exp = implode(' + ', $total_score_arr);
    return $total_score_exp;
  }

  private function get_where_clause(
    int $bracket_id,
    DateTimeImmutable|false $first_updated = false
  ): string {
    $where = " WHERE p0.bracket_id = $bracket_id";

    if ($this->ignore_late_plays && $first_updated) {
      $updated = $first_updated->format('Y-m-d H:i:s');
      $where .= " AND p3.post_date_gmt <= '$updated'";
    }

    return $where;
  }

  public function set_winners(Bracket $bracket) {
    $plays_table = $this->play_repo->table_name();
    $bracket_id = $bracket->id;
    $sql = "
        UPDATE {$plays_table} 
        SET is_winner = 1
        WHERE bracket_post_id = {$bracket_id}
        AND total_score = (
            SELECT MAX(total_score)
            FROM {$plays_table}
            WHERE bracket_post_id = {$bracket_id}
        )
      ";
    $this->wpdb->query($sql);
    return $this->wpdb->rows_affected;
  }
}
