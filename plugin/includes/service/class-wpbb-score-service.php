<?php

require_once 'class-wpbb-score-service-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  '/domain/class-wpbb-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  '/domain/class-wpbb-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  '/repository/class-wpbb-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  '/repository/class-wpbb-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  '/repository/class-wpbb-bracket-template-repo.php';

class Wpbb_Score_Service implements Wpbb_Score_Service_Interface
{
  /**
   * This method scores bracket plays against tournament results
   */

  /**
   * @var Wpbb_BracketTournament
   */
  public $tournament;

  /**
   * @var Wpbb_BracketPlayRepo
   */
  public $play_repo;

  /**
   * @var Wpbb_BracketTournamentRepo
   */
  public $tournament_repo;

  /**
   * @var Wpbb_BracketTemplateRepo
   */
  public $template_repo;

  /**
   * @var wpdb
   */
  private $wpdb;

  public function __construct($tournament = null)
  {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->tournament = $tournament;
    $this->play_repo = new Wpbb_BracketPlayRepo();
    $this->tournament_repo = new Wpbb_BracketTournamentRepo();
    $this->template_repo = new Wpbb_BracketTemplateRepo();
  }

  public function score_tournament_plays(
    Wpbb_BracketTournament|int|null $tournament
  ) {
    try {
      $this->score_plays($tournament);
    } catch (Exception $e) {
      // do nothing
    }
  }

  private function score_plays(Wpbb_BracketTournament|int|null $tournament)
  {
    $point_values = [1, 2, 4, 8, 16, 32];

    if (is_int($tournament)) {
      $tournament = $this->tournament_repo->get($tournament);
    }
    if (!$tournament instanceof Wpbb_BracketTournament) {
      throw new Exception('Cannot find tournament');
    }

    $tournament_data = $this->tournament_repo->get_tournament_data(
      $tournament->id
    );
    $tournament_id = $tournament_data['id'];

    if (!$tournament_id) {
      throw new Exception('Cannot find tournament id');
    }

    $num_rounds = $tournament->get_num_rounds();

    if (!$num_rounds || $num_rounds < 1) {
      throw new Exception('Cannot find number of rounds');
    }

    $high_score = $tournament->highest_possible_score();

    $plays_table = $this->play_repo->plays_table();
    $picks_table = $this->play_repo->picks_table();
    $results_table = $this->tournament_repo->results_table();

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
																										AND p2.bracket_tournament_id = $tournament_id
				GROUP BY p1.bracket_play_id
		) agg ON p0.id = agg.bracket_play_id
		SET p0.total_score = COALESCE($total_score_exp, 0),
				p0.accuracy_score = COALESCE($total_score_exp, 0) / $high_score;
		";

    $results = $this->wpdb->get_results($sql, ARRAY_A);
  }
}
