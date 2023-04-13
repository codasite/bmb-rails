<?php

class Wp_Bracket_Builder_Sport {
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var Wp_Bracket_Builder_Team[]
	 */
	private $teams;

	public function __construct(string $name, array $teams) {
		$this->name = $name;
		$this->teams = $teams;
	}
}

class Wp_Bracket_Builder_Team {
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var Wp_Bracket_Builder_Sport
	 */
	private $sport;

	public function __construct(string $name, Wp_Bracket_Builder_Sport $sport) {
		$this->name = $name;
		$this->sport = $sport;
	}
}

class Wp_Bracket_Builder_Tournament {
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var Wp_Bracket_Builder_Sport
	 */
	private $sport;
	
	/**
	 * @var int
	 */
	private $wildcard_teams;

	/**
	 * @var Wp_Bracket_Builder_Round[]
	 */
	private $rounds;

	public function __construct(
		string $name, 
		Wp_Bracket_Builder_Sport $sport, 
		array $rounds,
		int $wildcard_teams = 0) {
		$this->name = $name;
		$this->sport = $sport;
		$this->wildcard_teams = $wildcard_teams;
		$this->rounds = $rounds;
	}
}

class Wp_Bracket_Builder_Round {

	/**
	 * @var string
	 */
	private $name;


	public function __construct(string $name) {
		$this->name = $name;
	}
}

class Wp_Bracket_Builder_Bracket {
	/**
	 * @var Wp_Bracket_Builder_Tournament
	 */
	private $tournament;

	/**
	 * @var int
	 */
	private $customer_id;

	/**
	 * @var Wp_Bracket_Builder_Prediction[]
	 */
	private $predictions;

	public function __construct(int $customer_id, Wp_Bracket_Builder_Tournament $tournament, array $predictions) {
		$this->customer_id = $customer_id;
		$this->tournament = $tournament;
	}
}

class Wp_Bracket_Builder_Prediction {
	/**
	 * @var Wp_Bracket_Builder_Team
	 */
	private $team;

	/**
	 * @var Wp_Bracket_Builder_Round
	 */
	private $round;

	/**
	 * @var int
	 */
	private $left;

	/**
	 * @var int
	 */
	private $right;

	/**
	 * @var int
	 */
	private $in_order;

	public function __construct(Wp_Bracket_Builder_Team $team, Wp_Bracket_Builder_Round $round, int $left, int $right, int $in_order) {
		$this->team = $team;
		$this->round = $round;
		$this->left = $left;
		$this->right = $right;
		$this->in_order = $in_order;
	}
}
