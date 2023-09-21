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
		$point_values = [1, 2, 4, 8, 16, 32];
		echo "Scoring tournament plays";
		$tournament_data = $this->tournament_repo->get_tournament_data($tournament->id);
		$tournament_id = $tournament_data['id'];

		if (!$tournament_id) {
			return;
		}
		echo "Tournament id: $tournament_id";
		$plays_table = $this->play_repo->plays_table();
		$picks_table = $this->play_repo->picks_table();
		$num_rounds = 4;

		$select = "SELECT count(*) as num_picks from $picks_table where bracket_play_id = (select id from $plays_table where bracket_tournament_id = $tournament_id)";
		$results = $this->wpdb->get_results($select, ARRAY_A);

		// $sql = "UPDATE $plays_table SET total_score = 10 WHERE bracket_tournament_id = $tournament_id";
		// $results = $this->wpdb->get_results($sql, ARRAY_A);
		print_r($results);
	}
}
