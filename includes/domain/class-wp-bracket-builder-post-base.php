<?php

class Wp_Bracket_Builder_Post_Base {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var int
	 * 
	 * ID of the user who created the bracket
	 */
	public $author;

	/**
	 * @var string
	 */
	public $status;

	/**
	 * @var DateTime
	 * 
	 * Date the bracket was created
	 */
	public $date;

	/**
	 * @var DateTime
	 * 
	 * Date the bracket was created in GMT
	 */
	public $date_gmt;

	public function __construct(
		int $id = null,
		string $title = '',
		int $author = null,
		string $status = 'draft',
		DateTime $date = null,
		DateTime $date_gmt = null,
	) {
		$this->id = $id;
		$this->title = $title;
		$this->author = $author;
		$this->status = $status;
		$this->date = $date;
		$this->date_gmt = $date_gmt;
	}

	// public function equals(Wp_Bracket_Builder_Bracket_Base $bracket): bool {
	// 	if ($this->id !== $bracket->id) {
	// 		return false;
	// 	}
	// 	if ($this->title !== $bracket->title) {
	// 		return false;
	// 	}
	// 	return Wp_Bracket_Builder_Round::array_equals($this->rounds, $bracket->rounds);
	// 	return true;
	// }

	// 	public function get_team_map(): array {
	// 		// return an array of team ids to teams
	// 		$team_map = [];
	// 		foreach ($this->rounds as $round) {
	// 			foreach ($round->matches as $match) {
	// 				$team_map[$match->team1->id] = $match->team1;
	// 				$team_map[$match->team2->id] = $match->team2;
	// 			}
	// 		}
	// 		return $team_map;
	// 	}

	// 	public function get_match_parent(int $round_idx, int $match_idx): ?Wp_Bracket_Builder_Match {
	// 		// return the match that is the parent of the given match
	// 		// return null if the match is not found
	// 		if ($round_idx >= count($this->rounds)) {
	// 			return null;
	// 		}
	// 		if ($match_idx >= count($this->rounds[$round_idx]->matches)) {
	// 			return null;
	// 		}
	// 		if ($round_idx === 0) {
	// 			return null;
	// 		}
	// 		$parent_match_idx = (int) floor($match_idx / 2);
	// 		return $this->rounds[$round_idx - 1]->matches[$parent_match_idx];
	// 	}

	// 	public function fill_in_results(array $match_results_map): void {
	// 		$team_map = $this->get_team_map();
	// 		$rounds = $this->rounds;

	// 		foreach ($rounds as $i => $round) {
	// 			foreach ($round->matches as $j => $match) {
	// 				$match_id = $match->id;
	// 				if (isset($match_results_map[$match_id])) {
	// 					$team_id = $match_results_map[$match_id];
	// 					$result = $team_map[$team_id];
	// 					$match->result = $result;
	// 					$parent = $this->get_match_parent($i, $j);
	// 					if ($parent) {
	// 						$left_child = $j % 2 === 0;
	// 						if ($left_child) {
	// 							$parent->team1 = $result;
	// 						} else {
	// 							$parent->team2 = $result;
	// 						}
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}
	// }

	// class Wp_Bracket_Builder_Round {
	// 	/**
	// 	 * @var int
	// 	 */
	// 	public $id;

	// 	/**
	// 	 * @var string
	// 	 */
	// 	public $name;

	// 	/**
	// 	 * @var Wp_Bracket_Builder_Match[]
	// 	 */
	// 	public $matches;

	// 	/**
	// 	 * @var int
	// 	 */
	// 	public $depth;

	// 	/**
	// 	 * @var int
	// 	 */
	// 	public $round_index;

	// 	public function __construct(int $depth, int $round_index, string $name = null, int $id = null, array $matches = []) {
	// 		// public function __construct(string $name,  int $id = null, array $matches = []) {
	// 		$this->id = $id;
	// 		$this->depth = $depth;
	// 		$this->round_index = $round_index;
	// 		$this->name = $name;
	// 		$this->matches = $matches;
	// 	}

	// 	static public function from_array(array $data): Wp_Bracket_Builder_Round {
	// 		$round = new Wp_Bracket_Builder_Round($data['depth'], $data['round_index']);

	// 		if (isset($data['name'])) {
	// 			$round->name = $data['name'];
	// 		}

	// 		if (isset($data['id'])) {
	// 			$round->id = (int) $data['id'];
	// 		}

	// 		if (isset($data['matches'])) {
	// 			$round->matches = array_map(function ($index, $match) {
	// 				if (empty($match)) {
	// 					return null;
	// 				}
	// 				$match['index'] = $index;
	// 				return Wp_Bracket_Builder_Match::from_array($match);
	// 			}, array_keys($data['matches']), $data['matches']);
	// 		}

	// 		return $round;
	// 	}

	// public function equals(Wp_Bracket_Builder_Round $round): bool {
	// 	if ($this->id !== $round->id) {
	// 		return false;
	// 	}
	// 	if ($this->name !== $round->name) {
	// 		return false;
	// 	}
	// 	// if ($this->depth !== $round->depth) {
	// 	// 	return false;
	// 	// }
	// 	return Wp_Bracket_Builder_Match::array_equals($this->matches, $round->matches);
	// }

	// static function array_equals(array $rounds1, array $rounds2): bool {
	// 	if (count($rounds1) !== count($rounds2)) {
	// 		return false;
	// 	}
	// 	for ($i = 0; $i < count($rounds1); $i++) {
	// 		if (!$rounds1[$i]->equals($rounds2[$i])) {
	// 			return false;
	// 		}
	// 	}
	// 	return true;
	// }
}


// public function equals(Wp_Bracket_Builder_Match $match): bool {
// 	if ($this->id !== $match->id) {
// 		return false;
// 	}
// 	if ($this->index !== $match->index) {
// 		return false;
// 	}
// 	if (!$this->team1->equals($match->team1)) {
// 		return false;
// 	}
// 	if (!$this->team2->equals($match->team2)) {
// 		return false;
// 	}
// 	if ($this->result !== null && $match->result !== null && !$this->result->equals($match->result)) {
// 		return false;
// 	}
// 	return true;
// }

// static function array_equals(array $matches1, array $matches2): bool {
// 	if (count($matches1) !== count($matches2)) {
// 		return false;
// 	}
// 	for ($i = 0; $i < count($matches1); $i++) {
// 		if (!$matches1[$i]->equals($matches2[$i])) {
// 			return false;
// 		}
// 	}
// 	return true;
// }
