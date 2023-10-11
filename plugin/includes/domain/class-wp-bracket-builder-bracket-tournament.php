<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-custom-post-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-match-pick.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-validation-exception.php';


class Wp_Bracket_Builder_Bracket_Tournament extends Wp_Bracket_Builder_Post_Base
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
	 * @var ?Wp_Bracket_Builder_Bracket_Template
	 */
	public $bracket_template;

	/**
	 * @var Wp_Bracket_Builder_Match_Pick[]
	 */
	public $results;

	public function __construct(array $data = []) {
		parent::__construct($data);
		$this->date = $data['date'] ?? null;
		$this->bracket_template_id = (int)($data['bracket_template_id'] ?? null);
		$this->bracket_template = $data['bracket_template'] ?? null;
		$this->results = $data['results'] ?? [];
	}

	public function get_winning_team(): ?Wp_Bracket_Builder_Team {
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
	static public function from_array($data): Wp_Bracket_Builder_Bracket_Tournament {
		if (!isset($data['bracket_template_id']) && !isset($data['bracket_template'])) {
			throw new Wpbb_ValidationException('bracket_template_id or bracket_template is required');
		}
		$requiredFields = ['author', 'date', 'title'];
		validateRequiredFields($data, $requiredFields);

		if (isset($data['results'])) {
			$results = [];
			foreach ($data['results'] as $result) {
				$results[] = Wp_Bracket_Builder_Match_Pick::from_array($result);
			}
			$data['results'] = $results;
		}

		if (isset($data['bracket_template'])) {
			$data['bracket_template'] = Wp_Bracket_Builder_Bracket_Template::from_array($data['bracket_template']);
		}

		return new Wp_Bracket_Builder_Bracket_Tournament($data);
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
