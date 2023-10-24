<?php

require_once 'class-wpbb-score-service-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  '/domain/class-wpbb-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  '/domain/class-wpbb-bracket.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  '/repository/class-wpbb-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  '/repository/class-wpbb-bracket-repo.php';

class Wpbb_Score_Service implements Wpbb_Score_Service_Interface {
  /**
   * This method scores bracket plays against bracket results
   */

  /**
   * @var Wpbb_BracketPlayRepo
   */
  public $play_repo;

  /**
   * @var Wpbb_BracketRepo
   */
  public $bracket_repo;

  /**
   * @var wpdb
   */
  private $wpdb;

  /**
   * @var Wpbb_Utils
   */
  private $utils;

  /**
   * @var bool
   */
  private $only_score_printed_plays;

  public function __construct($opts = []) {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->play_repo = new Wpbb_BracketPlayRepo();
    $this->bracket_repo = new Wpbb_BracketRepo();
    $this->bracket_repo = new Wpbb_BracketRepo();
    $this->utils = new Wpbb_Utils();
    $this->only_score_printed_plays = $opts['only_score_printed_plays'] ?? true;
  }

  /**
   * @param Wpbb_Bracket|int|null $bracket
   * @return int returns the number of plays scored
   */
  public function score_bracket_plays(Wpbb_Bracket|int|null $bracket): int {
    try {
      $affected_rows = $this->score_plays($bracket);
      return $affected_rows;
    } catch (Exception $e) {
      return 0;
    }
  }

  private function score_plays(Wpbb_Bracket|int|null $bracket) {
    $point_values = [1, 2, 4, 8, 16, 32];

    if (is_int($bracket)) {
      $bracket = $this->bracket_repo->get($bracket);
    }
    if (!$bracket instanceof Wpbb_Bracket) {
      throw new Exception('Cannot find bracket');
    }

    $bracket_data = $this->bracket_repo->get_bracket_data($bracket->id);
    $bracket_id = $bracket_data['id'];

    if (!$bracket_id) {
      throw new Exception('Cannot find bracket id');
    }

    $num_rounds = $bracket->get_num_rounds();

    if (!$num_rounds || $num_rounds < 1) {
      throw new Exception('Cannot find number of rounds');
    }

    $high_score = $bracket->highest_possible_score();

    $plays_table = $this->play_repo->plays_table();
    $picks_table = $this->play_repo->picks_table();
    $results_table = $this->bracket_repo->results_table();

    $num_correct_arr = [];

    for ($i = 0; $i < $num_rounds; $i++) {
      $num_correct_arr[] = "COALESCE(SUM(CASE WHEN p1.round_index = $i THEN 1 ELSE 0 END), 0) AS round{$i}correct";
    }

    $num_correct_select = implode(', ', $num_correct_arr);

    $total_score_arr = [];

    for ($i = 0; $i < $num_rounds; $i++) {
      $total_score_arr[] = "agg.round{$i}correct * {$point_values[$i]}";
    }

    $total_score_exp = implode(' + ', $total_score_arr);

    $sql = "
    UPDATE $plays_table p0
    LEFT JOIN (
    		SELECT p1.bracket_play_id,
    						$num_correct_select
    		FROM $picks_table p1
    		JOIN $results_table p2 ON p1.round_index = p2.round_index
    																								AND p1.match_index = p2.match_index
    																								AND p1.winning_team_id = p2.winning_team_id
    																								AND p2.bracket_id = $bracket_id
    		GROUP BY p1.bracket_play_id
    ) agg ON p0.id = agg.bracket_play_id
    SET p0.total_score = COALESCE($total_score_exp, 0),
    		p0.accuracy_score = COALESCE($total_score_exp, 0) / $high_score
    WHERE p0.bracket_id = $bracket_id
    ";

    $sql = $this->only_score_printed_plays
      ? $sql . ' AND p0.is_printed = 1'
      : $sql;

    $this->wpdb->query($sql);
    return $this->wpdb->rows_affected;
  }
}
