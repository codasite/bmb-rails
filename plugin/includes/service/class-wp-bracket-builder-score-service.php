<?php

require_once 'class-wp-bracket-builder-score-service-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/repository/class-wp-bracket-builder-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/repository/class-wp-bracket-builder-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/repository/class-wp-bracket-builder-bracket-template-repo.php';

class Wp_Bracket_Builder_Score_Service implements Wp_Bracket_Builder_Score_Service_Interface {
	/**
	 * This method scores bracket plays against tournament results
	 */

	/**
	 * @var Wp_Bracket_Builder_Bracket_Tournament
	 */
	public $tournament;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Play_Repo
	 */
	public $play_repo;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Tournament_Repo
	 */
	public $tournament_repo;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Template_Repo
	 */
	public $template_repo;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	public function __construct($tournament = null) {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->tournament = $tournament;
		$this->play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();
		$this->tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
		$this->template_repo = new Wp_Bracket_Builder_Bracket_Template_Repository();
	}

	public function score_tournament_plays(Wp_Bracket_Builder_Bracket_Tournament|int|null $tournament) {
		try {
			$this->score_plays($tournament);
		} catch (Exception $e) {
			// do nothing
		}
	}

	private function score_plays(Wp_Bracket_Builder_Bracket_Tournament|int|null $tournament) {
		$point_values = [1, 2, 4, 8, 16, 32];

		if (is_int($tournament)) {
			$tournament = $this->tournament_repo->get($tournament);
		}
		if (!$tournament instanceof Wp_Bracket_Builder_Bracket_Tournament) {
			throw new Exception('Cannot find tournament');
		}

		$tournament_data = $this->tournament_repo->get_tournament_data($tournament->id);
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
