<?php

class Wp_Bracket_Builder_Bracket_Base {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var Wp_Bracket_Builder_Round[]
	 */
	public $rounds;

	public function __construct(string $name, int $id = null, array $rounds = []) {
		$this->id = $id;
		$this->name = $name;
		$this->rounds = $rounds;
	}

	public function equals(Wp_Bracket_Builder_Bracket_Base $bracket): bool {
		if ($this->id !== $bracket->id) {
			return false;
		}
		if ($this->name !== $bracket->name) {
			return false;
		}
		return Wp_Bracket_Builder_Round::array_equals($this->rounds, $bracket->rounds);
		return true;
	}

	public function get_team_map(): array {
		// return an array of team ids to teams
		$team_map = [];
		foreach ($this->rounds as $round) {
			foreach ($round->matches as $match) {
				$team_map[$match->team1->id] = $match->team1;
				$team_map[$match->team2->id] = $match->team2;
			}
		}
		return $team_map;
	}

	public function get_match_parent(int $round_idx, int $match_idx): ?Wp_Bracket_Builder_Match {
		// return the match that is the parent of the given match
		// return null if the match is not found
		if ($round_idx >= count($this->rounds)) {
			return null;
		}
		if ($match_idx >= count($this->rounds[$round_idx]->matches)) {
			return null;
		}
		if ($round_idx === 0) {
			return null;
		}
		$parent_match_idx = (int) floor($match_idx / 2);
		return $this->rounds[$round_idx - 1]->matches[$parent_match_idx];
	}
}

class Wp_Bracket_Builder_Round {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var Wp_Bracket_Builder_Match[]
	 */
	public $matches;

	/**
	 * @var int
	 */
	public $depth;

	public function __construct(int $depth, string $name = null, int $id = null, array $matches = []) {
		// public function __construct(string $name,  int $id = null, array $matches = []) {
		$this->id = $id;
		$this->depth = $depth;
		$this->name = $name;
		$this->matches = $matches;
	}

	static public function from_array(array $data): Wp_Bracket_Builder_Round {
		$round = new Wp_Bracket_Builder_Round($data['depth']);

		if (isset($data['name'])) {
			$round->name = $data['name'];
		}

		if (isset($data['id'])) {
			$round->id = (int) $data['id'];
		}

		if (isset($data['matches'])) {
			$round->matches = array_map(function ($index, $match) {
				if (empty($match)) {
					return null;
				}
				$match['index'] = $index;
				return Wp_Bracket_Builder_Match::from_array($match);
			}, array_keys($data['matches']), $data['matches']);
		}

		return $round;
	}

	public function equals(Wp_Bracket_Builder_Round $round): bool {
		if ($this->id !== $round->id) {
			return false;
		}
		if ($this->name !== $round->name) {
			return false;
		}
		// if ($this->depth !== $round->depth) {
		// 	return false;
		// }
		return Wp_Bracket_Builder_Match::array_equals($this->matches, $round->matches);
	}

	static function array_equals(array $rounds1, array $rounds2): bool {
		if (count($rounds1) !== count($rounds2)) {
			return false;
		}
		for ($i = 0; $i < count($rounds1); $i++) {
			if (!$rounds1[$i]->equals($rounds2[$i])) {
				return false;
			}
		}
		return true;
	}
}

class Wp_Bracket_Builder_Match {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var int
	 */
	public $index;

	/**
	 * @var Wp_Bracket_Builder_Team
	 */
	public $team1;

	/**
	 * @var Wp_Bracket_Builder_Team
	 */
	public $team2;

	/**
	 * @var Wp_Bracket_Builder_Team
	 */
	public $result;

	public function __construct(int $index, Wp_Bracket_Builder_Team $team1 = null, Wp_Bracket_Builder_Team $team2 = null, Wp_Bracket_Builder_Team $result = null, int $id = null) {
		$this->id = $id;
		$this->index = $index;
		$this->team1 = $team1;
		$this->team2 = $team2;
		$this->result = $result;
	}

	static public function from_array(array $data): Wp_Bracket_Builder_Match {
		$match = new Wp_Bracket_Builder_Match($data['index']);

		if (isset($data['id'])) {
			$match->id = (int) $data['id'];
		}

		if (isset($data['team1'])) {
			$match->team1 = Wp_Bracket_Builder_Team::from_array($data['team1']);
		}

		if (isset($data['team2'])) {
			$match->team2 = Wp_Bracket_Builder_Team::from_array($data['team2']);
		}

		if (isset($data['result'])) {
			$match->result = Wp_Bracket_Builder_Team::from_array($data['result']);
		}

		return $match;
	}

	public function equals(Wp_Bracket_Builder_Match $match): bool {
		if ($this->id !== $match->id) {
			return false;
		}
		if ($this->index !== $match->index) {
			return false;
		}
		if (!$this->team1->equals($match->team1)) {
			return false;
		}
		if (!$this->team2->equals($match->team2)) {
			return false;
		}
		if ($this->result !== null && $match->result !== null && !$this->result->equals($match->result)) {
			return false;
		}
		return true;
	}

	static function array_equals(array $matches1, array $matches2): bool {
		if (count($matches1) !== count($matches2)) {
			return false;
		}
		for ($i = 0; $i < count($matches1); $i++) {
			if (!$matches1[$i]->equals($matches2[$i])) {
				return false;
			}
		}
		return true;
	}
}

class Wp_Bracket_Builder_Team {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var int
	 */
	public $seed;


	public function __construct(string $name = null, int $id = null, int $seed = null) {
		$this->id = $id;
		$this->name = $name;
		$this->seed = $seed;
	}

	static public function from_array(array $data): Wp_Bracket_Builder_Team {
		$team = new Wp_Bracket_Builder_Team();

		if (isset($data['name'])) {
			$team->name = $data['name'];
		}

		if (isset($data['id'])) {
			$team->id = (int) $data['id'];
		}

		if (isset($data['seed'])) {
			$team->seed = (int) $data['seed'];
		}

		return $team;
	}

	public function equals(Wp_Bracket_Builder_Team $team): bool {
		if ($this->id !== $team->id) {
			return false;
		}
		if ($this->name !== $team->name) {
			return false;
		}
		return true;
	}
}
