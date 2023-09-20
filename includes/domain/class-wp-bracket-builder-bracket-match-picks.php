<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-team.php';

class Wp_Bracket_Builder_Match_Pick {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var int
	 */
	public $round_index;

	/**
	 * @var int
	 */
	public $match_index;

	/** 
	 * @var Wp_Bracket_Builder_Team
	 */
	public $winning_team;

	/**
	 * @var int
	 */
	public $winning_team_id;

	public function __construct(
		int $round_index,
		int $match_index,
		int $winning_team_id,
		int $id = null,
		Wp_Bracket_Builder_Team $winning_team = null,
	) {
		$this->round_index = $round_index;
		$this->match_index = $match_index;
		$this->winning_team_id = $winning_team_id;
		$this->winning_team = $winning_team;
		$this->id = $id;
	}

	static public function from_array($data) {
		$pick = new Wp_Bracket_Builder_Match_Pick(
			$data['round_index'],
			$data['match_index'],
			$data['winning_team_id'],
		);

		if (isset($data['id'])) {
			$pick->id = (int) $data['id'];
		}

		if (isset($data['winning_team'])) {
			$pick->winning_team = Wp_Bracket_Builder_Team::from_array($data['winning_team']);
		}

		return $pick;
	}
}
