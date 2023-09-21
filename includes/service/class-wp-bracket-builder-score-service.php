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
		echo "Scoring tournament plays";
		$tournament_data = $this->tournament_repo->get_tournament_data($tournament->id);
		$tournament_id = $tournament_data['id'];

		if (!$tournament_id) {
			return;
		}
		$plays_table = $this->play_repo->plays_table();

		$sql = "UPDATE $plays_table SET score = 10 WHERE bracket_tournament_id = $tournament_id";
		$results = $this->wpdb->get_results($sql, ARRAY_A);
		print_r($results);
	}
}
