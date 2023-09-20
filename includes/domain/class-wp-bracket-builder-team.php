<?php

class Wp_Bracket_Builder_Team {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;


	public function __construct(string $name = null, int $id = null) {
		$this->id = $id;
		$this->name = $name;
	}

	static public function from_array(array $data): Wp_Bracket_Builder_Team {
		$team = new Wp_Bracket_Builder_Team();

		foreach ($data as $key => $value) {
			if (property_exists($team, $key)) {
				$team->$key = $value;
			}
		}

		return $team;
	}

	public function equals(Wp_Bracket_Builder_Team $team): bool {
		if ($this->id !== $team->id) {
			return false;
		}
		// if ($this->name !== $team->name) {
		// 	return false;
		// }
		return true;
	}
}
