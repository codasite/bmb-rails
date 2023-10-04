<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-custom-post-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-match-pick.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-interface.php';

class Wp_Bracket_Builder_Bracket_Tournament extends Wp_Bracket_Builder_Post_Base {
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

	public function __construct(
		int $bracket_template_id = null,
		int $id = null,
		string $title = '',
		int $author = null,
		string $status = 'publish',
		DateTimeImmutable|false $date = false,
		DateTimeImmutable|false $date_gmt = false,
		Wp_Bracket_Builder_Bracket_Template $bracket_template = null,
		array $results = [],
		string $slug = '',
	) {
		parent::__construct(
			$id,
			$title,
			$author,
			$status,
			$date,
			$date_gmt,
			$slug,
		);
		$this->bracket_template_id = $bracket_template_id;
		$this->bracket_template = $bracket_template;
		$this->results = $results;
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
		];
	}

	public function get_update_post_meta(): array {
		return [];
	}

	static public function from_array($data) {

		if (!isset($data['bracket_template_id']) && !isset($data['bracket_template'])) {
			throw new Exception('bracket_template_id or bracket_template is required');
		}

		if (!isset($data['author'])) {
			throw new Exception('author id is required');
		}

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

		$tournament = new Wp_Bracket_Builder_Bracket_Tournament();

		foreach ($data as $key => $value) {
			if (property_exists($tournament, $key)) {
				$tournament->$key = $value;
			}
		}

		return $tournament;
	}

	public function to_array(): array {
		$tournament = parent::to_array();
		$tournament['bracket_template_id'] = $this->bracket_template_id;
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

	public function get_matches(): array {
		return $this->bracket_template->get_matches();
	}

	public function get_picks(): array {
		return $this->results;
	}

	public function get_title(): string {
		return $this->title;
	}

	public function get_date(): string {
		return '1992';
	}

	public function get_num_teams(): int {
		return $this->bracket_template->num_teams;
	}
}
