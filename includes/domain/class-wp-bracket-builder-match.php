<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-team.php';

class Wp_Bracket_Builder_Match {
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
	public $team1;

	/**
	 * @var Wp_Bracket_Builder_Team
	 */
	public $team2;

	public function __construct(int $round_index, int $match_index, Wp_Bracket_Builder_Team $team1 = null, Wp_Bracket_Builder_Team $team2 = null, int $id = null) {
		$this->round_index = $round_index;
		$this->match_index = $match_index;
		$this->team1 = $team1;
		$this->team2 = $team2;
		$this->id = $id;
	}

	static public function from_array(array $data): Wp_Bracket_Builder_Match {
		if (!isset($data['round_index']) || !isset($data['match_index'])) {
			throw new InvalidArgumentException('round_index and match_index are required');
		}

		$match = new Wp_Bracket_Builder_Match($data['round_index'], $data['match_index']);


		if (isset($data['id'])) {
			$match->id = (int) $data['id'];
		}

		if (isset($data['team1'])) {
			$match->team1 = Wp_Bracket_Builder_Team::from_array($data['team1']);
		}

		if (isset($data['team2'])) {
			$match->team2 = Wp_Bracket_Builder_Team::from_array($data['team2']);
		}

		return $match;
	}
}
