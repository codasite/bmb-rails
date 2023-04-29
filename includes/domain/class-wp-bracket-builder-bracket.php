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
}

class Wp_Bracket_Builder_Bracket extends Wp_Bracket_Builder_Bracket_Base {
	public static function from_array(array $data): Wp_Bracket_Builder_Bracket {
		$bracket = new Wp_Bracket_Builder_Bracket($data['name']);

		if (isset($data['id'])) {
			$bracket->id = (int) $data['id'];
		}

		if (isset($data['rounds'])) {
			$bracket->rounds = array_map(function ($round) {
				return Wp_Bracket_Builder_Round::from_array($round);
			}, $data['rounds']);
		}

		return $bracket;
	}
}

class Wp_Bracket_Builder_User_Bracket extends Wp_Bracket_Builder_Bracket_Base {

	/**
	 * @var int
	 */
	public $customer_id;

	/**
	 * @var int
	 */
	public $bracket_id;

	public function __construct(int $customer_id, int $bracket_id, string $name = null, int $id = null, array $rounds = []) {
		// call parent constructor
		parent::__construct($name, $id, $rounds);
		$this->customer_id = $customer_id;
		$this->bracket_id = $bracket_id;
		$this->rounds = $rounds;
	}

	public static function from_array(array $data): Wp_Bracket_Builder_User_Bracket {
		$user_bracket = new Wp_Bracket_Builder_User_Bracket($data['customer_id'], $data['bracket_id'], $data['name']);

		if (isset($data['id'])) {
			$user_bracket->id = (int) $data['id'];
		}

		if (isset($data['rounds'])) {
			$user_bracket->rounds = array_map(function ($round) {
				return Wp_Bracket_Builder_Round::from_array($round);
			}, $data['rounds']);
		}

		return $user_bracket;
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

	// /**
	//  * @var int
	//  */
	// public $depth;

	// public function __construct(string $name, int $depth, int $id = null, array $matches = []) {
	public function __construct(string $name,  int $id = null, array $matches = []) {
		$this->id = $id;
		// $this->depth = $depth;
		$this->name = $name;
		$this->matches = $matches;
	}

	static public function from_array(array $data): Wp_Bracket_Builder_Round {
		$round = new Wp_Bracket_Builder_Round($data['name'], $data['depth']);

		if (isset($data['id'])) {
			$round->id = (int) $data['id'];
		}

		if (isset($data['matches'])) {
			$round->matches = array_map(function ($match) {
				return Wp_Bracket_Builder_Match::from_array($match);
			}, $data['matches']);
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

	public function __construct(int $index, Wp_Bracket_Builder_Team $team1, Wp_Bracket_Builder_Team $team2, Wp_Bracket_Builder_Team $result = null, int $id = null) {
		$this->id = $id;
		$this->index = $index;
		$this->team1 = $team1;
		$this->team2 = $team2;
		$this->result = $result;
	}

	static public function from_array(array $data): Wp_Bracket_Builder_Match {
		$match = new Wp_Bracket_Builder_Match($data['name'], $data['index'], Wp_Bracket_Builder_Team::from_array($data['team1']), Wp_Bracket_Builder_Team::from_array($data['team2']));

		if (isset($data['id'])) {
			$match->id = (int) $data['id'];
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

	public function __construct(string $name, int $id = null) {
		$this->id = $id;
		$this->name = $name;
	}

	static public function from_array(array $data): Wp_Bracket_Builder_Team {
		$team = new Wp_Bracket_Builder_Team($data['name']);

		if (isset($data['id'])) {
			$team->id = (int) $data['id'];
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
