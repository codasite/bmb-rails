<?php

class Wp_Bracket_Builder_Bracket {
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
	public function equals(Wp_Bracket_Builder_Bracket $bracket): bool {
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

	public function __construct(string $name, int $depth, int $id = null, array $matches = []) {
		$this->id = $id;
		$this->depth = $depth;
		$this->name = $name;
		$this->matches = $matches;
	}

	public function from_array(array $data): Wp_Bracket_Builder_Round {
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
		if ($this->depth !== $round->depth) {
			return false;
		}
		return Wp_Bracket_Builder_Match::array_equals($this->matches, $round->matches);
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
	 * @var string
	 */
	public $name;

	/**
	 * @var Wp_Bracket_Builder_Team
	 */
	public $leftTeam;

	/**
	 * @var Wp_Bracket_Builder_Team
	 */
	public $rightTeam;

	/**
	 * @var Wp_Bracket_Builder_Team
	 */
	public $result;

	public function __construct(string $name, int $index, Wp_Bracket_Builder_Team $leftTeam, Wp_Bracket_Builder_Team $rightTeam, Wp_Bracket_Builder_Team $result = null, int $id = null) {
		$this->id = $id;
		$this->index = $index;
		$this->name = $name;
		$this->leftTeam = $leftTeam;
		$this->rightTeam = $rightTeam;
		$this->result = $result;
	}

	public function from_array(array $data): Wp_Bracket_Builder_Match {
		$match = new Wp_Bracket_Builder_Match($data['name'], $data['index'], Wp_Bracket_Builder_Team::from_array($data['leftTeam']), Wp_Bracket_Builder_Team::from_array($data['rightTeam']));

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
		if ($this->name !== $match->name) {
			return false;
		}
		if (!$this->leftTeam->equals($match->leftTeam)) {
			return false;
		}
		if (!$this->rightTeam->equals($match->rightTeam)) {
			return false;
		}
		if ($this->result !== null && $match->result !== null && !$this->result->equals($match->result)) {
			return false;
		}
		return true;
	}
}
