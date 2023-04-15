<?php

class Wp_Bracket_Builder_Sport {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var Wp_Bracket_Builder_Team[]
	 */
	public $teams;

	public function __construct(string $name, int $id = null, array $teams = null) {
		$this->id = $id;
		$this->name = $name;
		$this->teams = $teams;
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Sport {
		$sport = new Wp_Bracket_Builder_Sport($data['name']);

		if (isset($data['id'])) {
			$sport->id = (int) $data['id'];
		}

		if (isset($data['teams'])) {
			$sport->teams = array_map(function ($team) {
				return Wp_Bracket_Builder_Team::from_array($team);
			}, $data['teams']);
		}

		return $sport;
	}
	public function equals(Wp_Bracket_Builder_Sport $sport): bool {
		if ($this->id !== $sport->id) {
			return false;
		}
		if ($this->name !== $sport->name) {
			return false;
		}
		return Wp_Bracket_Builder_Team::array_equals($this->teams, $sport->teams);
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

	public function __construct(string $name, int $id = null) {
		$this->id = $id;
		$this->name = $name;
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Team {
		$team = new Wp_Bracket_Builder_Team($data['name']);

		if (isset($data['id'])) {
			$team->id = (int)$data['id'];
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
	public static function array_equals(array $teams1, array $teams2): bool {
		if (count($teams1) !== count($teams2)) {
			return false;
		}
		// create an associative array mapping team objcets to their ids
		$teams2 = array_reduce($teams2, function ($acc, $team) {
			$acc[$team->id] = $team;
			return $acc;
		}, []);
		foreach ($teams1 as $team) {
			if (!isset($teams2[$team->id])) {
				return false;
			}
			if (!$team->equals($teams2[$team->id])) {
				return false;
			}
		}
		return true;
	}
}

class Wp_Bracket_Builder_Tournament {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var Wp_Bracket_Builder_Sport
	 */
	public $sport;

	/**
	 * @var int
	 */
	public $wildcard_teams;

	/**
	 * @var Wp_Bracket_Builder_Round[]
	 */
	public $rounds;

	public function __construct(
		int $id = null,
		string $name,
		Wp_Bracket_Builder_Sport $sport = null,
		array $rounds = [],
		int $wildcard_teams = 0
	) {
		$this->id = $id;
		$this->name = $name;
		$this->sport = $sport;
		$this->wildcard_teams = $wildcard_teams;
		$this->rounds = $rounds;
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


	public function __construct(string $name, int $id = null) {
		$this->id = $id;
		$this->name = $name;
	}
}

class Wp_Bracket_Builder_Bracket {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var Wp_Bracket_Builder_Tournament
	 */
	public $tournament;

	/**
	 * @var int
	 */
	public $customer_id;

	/**
	 * @var Wp_Bracket_Builder_Prediction[]
	 */
	public $predictions;

	public function __construct(int $id = null, int $customer_id = null, Wp_Bracket_Builder_Tournament $tournament = null, array $predictions = []) {
		$this->id = $id;
		$this->customer_id = $customer_id;
		$this->tournament = $tournament;
	}
}

class Wp_Bracket_Builder_Prediction {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var Wp_Bracket_Builder_Team
	 */
	public $team;

	/**
	 * @var Wp_Bracket_Builder_Round
	 */
	public $round;

	/**
	 * @var int
	 */
	public $left;

	/**
	 * @var int
	 */
	public $right;

	/**
	 * @var int
	 */
	public $in_order;

	public function __construct(int $id = null, Wp_Bracket_Builder_Team $team = null, Wp_Bracket_Builder_Round $round = null, int $left = null, int $right = null, int $in_order = null) {
		$this->id = $id;
		$this->team = $team;
		$this->round = $round;
		$this->left = $left;
		$this->right = $right;
		$this->in_order = $in_order;
	}
}
