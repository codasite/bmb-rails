<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-match-pick.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-team.php';

class Wpbb_BracketPlay extends Wpbb_PostBase
{

	/**
	 * @var int
	 */
	public $tournament_id;

	/**
	 * @var int
	 */
	public $template_id;

	/**
	 * @var Wpbb_BracketTournament | null
	 */
	public mixed $tournament;

	/**
	 * @var Wpbb_BracketTemplate
	 */
	public $template;

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

		if (!(isset($data['tournament_id']) || isset($data['template_id']))) {
			throw new Exception('tournament_id or template_id is required');
		}

		parent::__construct($data);
		$this->tournament_id = $data['tournament_id'] ?? null;
		$this->template_id = $data['template_id'] ?? null;
		$this->tournament = $data['tournament'] ?? null;
		$this->template = $data['template'] ?? null;
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
			'bracket_template_id' => $this->template_id,
		];
	}

	public function get_update_post_meta(): array {
		return [];
	}

	/**
	 * @throws Wpbb_ValidationException
	 */
	static public function from_array($data): Wpbb_BracketPlay {
		if (!(isset($data['tournament_id']) || isset($data['template_id']))) {
			throw new Wpbb_ValidationException('tournament_id or template_id is required');
		}
		validateRequiredFields($data, ['author', 'picks']);
		$picks = [];
		foreach ($data['picks'] as $pick) {
			$picks[] = Wpbb_MatchPick::from_array($pick);
		}
		$data['picks'] = $picks;

		return new Wpbb_BracketPlay($data);
	}
}
