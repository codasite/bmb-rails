<?php
require_once plugin_dir_path(dirname(__FILE__)) . '/domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/domain/class-wp-bracket-builder-bracket-tournament.php';

class Wp_Bracket_Builder_Score_Service {
	/**
	 * This method scores bracket plays against tournament results
	 */

	/**
	 * @var Wp_Bracket_Builder_Bracket_Tournament
	 */
	public $tournament;

	public function __construct($tournament = null) {
		$this->tournament = $tournament;
	}

	public function score_tournament_plays(Wp_Bracket_Builder_Bracket_Tournament|int|null $tournament) {
		//TODO: score plays in SQL
	}
}
