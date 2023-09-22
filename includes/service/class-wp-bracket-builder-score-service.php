<?php
require_once plugin_dir_path(dirname(__FILE__)) . '/domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/repository/class-wp-bracket-builder-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/repository/class-wp-bracket-builder-bracket-tournament-repo.php';

class Wp_Bracket_Builder_Score_Service {
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
	 * @var wpdb
	 */
	private $wpdb;

	public function __construct($tournament = null) {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->tournament = $tournament;
		$this->play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();
		$this->tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
	}

	public function score_tournament_plays(Wp_Bracket_Builder_Bracket_Tournament|int|null $tournament) {
		// $point_values = [1, 2, 4, 8, 16, 32];
		$point_values = [1, 1, 1, 1, 1, 1];
		echo "Scoring tournament plays";
		$tournament_data = $this->tournament_repo->get_tournament_data($tournament->id);
		$tournament_id = $tournament_data['id'];

		if (!$tournament_id) {
			return;
		}
		$plays_table = $this->play_repo->plays_table();
		$picks_table = $this->play_repo->picks_table();
		$results_table = $this->tournament_repo->results_table();
		$numRounds = 3;

		$num_correct_arr = [];
		for ($i = 0; $i < $numRounds; $i++) {
			$num_correct_arr[] = "COALESCE(SUM(CASE WHEN p1.round_index = $i THEN 1 ELSE 0 END), 0) AS round{$i}correct";
		}
		$num_correct_select = implode(', ', $num_correct_arr);

		$total_score_arr = [];
		for ($i = 0; $i < $numRounds; $i++) {
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
																										AND p2.bracket_tournament_id = 3
				GROUP BY p1.bracket_play_id
		) agg ON p0.id = agg.bracket_play_id
		SET p0.total_score = COALESCE($total_score_exp, 0);
		";

		echo $sql;

		// $sql = "
		// UPDATE $plays_table p0
		// LEFT JOIN (
		// 		SELECT p1.bracket_play_id,
		// 					COALESCE(SUM(CASE WHEN p1.round_index = 0 THEN 1 ELSE 0 END), 0) AS round1correct,
		// 					COALESCE(SUM(CASE WHEN p1.round_index = 1 THEN 1 ELSE 0 END), 0) AS round2correct,
		// 					COALESCE(SUM(CASE WHEN p1.round_index = 2 THEN 1 ELSE 0 END), 0) AS round3correct
		// 		FROM $picks_table p1
		// 		JOIN $results_table p2 ON p1.round_index = p2.round_index
		// 																								AND p1.match_index = p2.match_index
		// 																								AND p1.winning_team_id = p2.winning_team_id
		// 																								AND p2.bracket_tournament_id = 3
		// 		GROUP BY p1.bracket_play_id
		// ) agg ON p0.id = agg.bracket_play_id
		// SET p0.total_score = COALESCE(agg.round1correct + agg.round2correct + agg.round3correct, 0);
		// ";

		$results = $this->wpdb->get_results($sql, ARRAY_A);
		print_r($results);
	}
}
