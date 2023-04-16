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

	// /**
	//  * @var Wp_Bracket_Builder_Team[]
	//  */
	// public $teams;

	public function __construct(string $name, int $id = null, array $teams = null) {
		$this->id = $id;
		$this->name = $name;
		// $this->teams = $teams;
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Bracket {
		$bracket = new Wp_Bracket_Builder_Bracket($data['name']);

		if (isset($data['id'])) {
			$bracket->id = (int) $data['id'];
		}

		// if (isset($data['teams'])) {
		// 	$bracket->teams = array_map(function ($team) {
		// 		return Wp_Bracket_Builder_Team::from_array($team);
		// 	}, $data['teams']);
		// }

		return $bracket;
	}
	public function equals(Wp_Bracket_Builder_Bracket $bracket): bool {
		if ($this->id !== $bracket->id) {
			return false;
		}
		if ($this->name !== $bracket->name) {
			return false;
		}
		// return Wp_Bracket_Builder_Team::array_equals($this->teams, $bracket->teams);
		return true;
	}
}
