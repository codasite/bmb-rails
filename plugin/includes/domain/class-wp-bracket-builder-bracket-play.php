<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-match-pick.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-team.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-interface.php';

/**
 * This class creates a bracket pick object by submitting 
 */
// class Wp_Bracket_Builder_Bracket_Pick_Factory extends Wp_Bracket_Builder_Bracket_Base {


class Wp_Bracket_Builder_Bracket_Play extends Wp_Bracket_Builder_Post_Base implements Wp_Bracket_Builder_Bracket_Interface {

	/**
	 * @var int
	 */
	public $tournament_id;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Tournament
	 */
	public $tournament;

	/**
	 * @var Wp_Bracket_Builder_Match_Pick[]
	 */
	public $picks;

	/**
	 * @var int
	 */
	public $total_score;

	/**
	 * @var float
	 */
	public $accuracy_score;

	/**
	 * @var int
	 */
	public $busted_id;

	public function __construct(array $data = []) {
		parent::__construct($data);

		if (!isset($data['tournament_id'])) {
			throw new Exception('tournament_id is required');
		}

		parent::__construct($data);
		$this->tournament_id = $data['tournament_id'];
		$this->tournament = $data['tournament'] ?? null;
		$this->picks = $data['picks'] ?? [];
		$this->total_score = $data['total_score'] ?? 0;
		$this->accuracy_score = $data['accuracy_score'] ?? 0;
		$this->busted_id = $data['busted_id'] ?? null;
	}

	static public function get_post_type(): string {
		return 'bracket_play';
	}

	public function get_winning_team(): ?Wp_Bracket_Builder_Team {
		if (count($this->picks) === 0) {
			return null;
		}
		return $this->picks[count($this->picks) - 1]->winning_team;
	}

	public function get_post_meta(): array {
		return [
			'bracket_tournament_id' => $this->tournament_id,
		];
	}

	public function get_update_post_meta(): array {
		return [];
	}

	static public function from_array($data): Wp_Bracket_Builder_Bracket_Play {
		if (!isset($data['tournament_id'])) {
			throw new Exception('tournament_id is required');
		}

		if (!isset($data['author'])) {
			throw new Exception('author is required');
		}

		if (!isset($data['picks'])) {
			throw new Exception('picks is required');
		}

		$picks = [];
		foreach ($data['picks'] as $pick) {
			$picks[] = Wp_Bracket_Builder_Match_Pick::from_array($pick);
		}
		$data['picks'] = $picks;

		$play = new Wp_Bracket_Builder_Bracket_Play($data);

		return $play;
	}

	public function get_matches(): array {
		return $this->tournament->get_matches();
	}

	public function get_picks(): array {
		return $this->picks;
	}

	public function get_title(): string {
		return $this->tournament->title;
	}

	public function get_date(): string {
		return '1993';
	}

	public function get_num_teams(): int {
		return $this->tournament->get_num_teams();
	}
}
