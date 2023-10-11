<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-match-pick.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-team.php';

/**
 * This class creates a bracket pick object by submitting
 */
// class Wp_Bracket_Builder_Bracket_Pick_Factory extends Wp_Bracket_Builder_Bracket_Base {


class Wpbb_BracketPlay extends Wpbb_PostBase
{

	/**
	 * @var int
	 */
	public $tournament_id;

	/**
	 * @var Wpbb_BracketTournament
	 */
	public $tournament;

	/**
	 * @var Wpbb_MatchPick[]
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

	public function get_winning_team(): ?Wpbb_Team {
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

	/**
	 * @throws Wpbb_ValidationException
	 */
	static public function from_array($data): Wpbb_BracketPlay {
		validateRequiredFields($data, ['tournament_id', 'author', 'picks']);
		$picks = [];
		foreach ($data['picks'] as $pick) {
			$picks[] = Wpbb_MatchPick::from_array($pick);
		}
		$data['picks'] = $picks;

		$play = new Wpbb_BracketPlay($data);

		return $play;
	}
}
