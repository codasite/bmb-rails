<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-custom-post-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-match-pick.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-validation-exception.php';


class Wpbb_BracketTournament extends Wpbb_PostBase
{
	/**
	 * @var string
	 */
	public $date;

	/**
	 * @var ?int
	 */
	public $bracket_template_id;

	/**
	 * @var ?Wpbb_BracketTemplate
	 */
	public $bracket_template;

	/**
	 * @var Wpbb_MatchPick[]
	 */
	public $results;

	public function __construct(array $data = []) {
		parent::__construct($data);
		$this->date = $data['date'] ?? null;
		$this->bracket_template_id = (int)($data['bracket_template_id'] ?? null);
		$this->bracket_template = $data['bracket_template'] ?? null;
		$this->results = $data['results'] ?? [];
	}

	public function get_winning_team(): ?Wpbb_Team {
		if (!$this->results) {
			return null;
		}

		$winning_pick = $this->results[count($this->results) - 1];

		return $winning_pick->winning_team;
	}

	public function has_results(): bool {
		return count($this->results) > 0;
	}

	static public function get_post_type(): string {
		return 'bracket_tournament';
	}

	public function get_post_meta(): array {
		return [
			'bracket_template_id' => $this->bracket_template_id,
			'date' => $this->date,
		];
	}

	public function get_update_post_meta(): array {
		return [
			'date' => $this->date,
		];
	}

	/**
	 * @throws Wpbb_ValidationException
	 */
	static public function from_array($data): Wpbb_BracketTournament {
		if (!isset($data['bracket_template_id']) && !isset($data['bracket_template'])) {
			throw new Wpbb_ValidationException('bracket_template_id or bracket_template is required');
		}
		$requiredFields = ['author', 'date', 'title'];
		validateRequiredFields($data, $requiredFields);

		if (isset($data['results'])) {
			$results = [];
			foreach ($data['results'] as $result) {
				$results[] = Wpbb_MatchPick::from_array($result);
			}
			$data['results'] = $results;
		}

		if (isset($data['bracket_template'])) {
			$data['bracket_template'] = Wpbb_BracketTemplate::from_array($data['bracket_template']);
		}

		return new Wpbb_BracketTournament($data);
	}

	public function to_array(): array {
		$tournament = parent::to_array();
		$tournament['bracket_template_id'] = $this->bracket_template_id;
		$tournament['date'] = $this->date;
		if ($this->results) {
			$results = [];
			foreach ($this->results as $result) {
				$results[] = $result->to_array();
			}
			$tournament['results'] = $results;
		}
		return $tournament;
	}

	public function get_num_rounds(): int {
		return $this->bracket_template->get_num_rounds();
	}

	public function highest_possible_score() {
		$point_values = [1, 2, 4, 8, 16, 32];

		$score = 0;

		foreach ($this->results as $result) {
			$score += $point_values[$result->round_index];
		}

		return $score;
	}
}
