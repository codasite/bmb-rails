<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';

/**
 * This class creates a bracket pick object by submitting 
 */
// class Wp_Bracket_Builder_Bracket_Pick_Factory extends Wp_Bracket_Builder_Bracket_Base {


class Wp_Bracket_Builder_Bracket_Play extends Wp_Bracket_Builder_Post_Base {

	/**
	 * @var int
	 */
	public $tournament_id;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Tournament
	 */
	public $tournament;

	/**
	 * @var string
	 */
	public $img_url;

	/**
	 * @var string
	 */
	public $html;

	/**
	 * @var Wp_Bracket_Builder_Match_Pick[]
	 */
	public $picks;

	public function __construct(
		int $tournament_id,
		int $author,
		int $id = null,
		string $title = '',
		string $status = 'publish',
		string $html = '',
		string $img_url = '',
		DateTimeImmutable|false $date = false,
		DateTimeImmutable|false $date_gmt = false,
		array $picks = [],
		Wp_Bracket_Builder_Bracket_Tournament $tournament = null,
	) {
		parent::__construct(
			$id,
			$title,
			$author,
			$status,
			$date,
			$date_gmt,
		);
		$this->tournament_id = $tournament_id;
		$this->tournament = $tournament;
		$this->html = $html;
		$this->img_url = $img_url;
		$this->picks = $picks;
	}

	static public function get_post_type(): string {
		return 'bracket_play';
	}

	public function get_post_meta(): array {
		return [
			'html' => $this->html,
			'img_url' => $this->img_url,
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

		$play = new Wp_Bracket_Builder_Bracket_Play($data['tournament_id'], $data['author']);

		foreach ($data as $key => $value) {
			if (property_exists($play, $key)) {
				$play->$key = $value;
			}
		}

		return $play;
	}
}

class Wp_Bracket_Builder_Match_Pick {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var int
	 */
	public $round_index;

	/**
	 * @var int
	 */
	public $match_index;

	/** 
	 * @var Wp_Bracket_Builder_Team
	 */
	public $winning_team;

	/**
	 * @var int
	 */
	public $winning_team_id;

	public function __construct(
		int $round_index,
		int $match_index,
		int $winning_team_id,
		int $id = null,
		Wp_Bracket_Builder_Team $winning_team = null,
	) {
		$this->round_index = $round_index;
		$this->match_index = $match_index;
		$this->winning_team_id = $winning_team_id;
		$this->winning_team = $winning_team;
		$this->id = $id;
	}

	static public function from_array($data) {
		$pick = new Wp_Bracket_Builder_Match_Pick(
			$data['round_index'],
			$data['match_index'],
			$data['winning_team_id'],
		);

		if (isset($data['id'])) {
			$pick->id = (int) $data['id'];
		}

		if (isset($data['winning_team'])) {
			$pick->winning_team = Wp_Bracket_Builder_Team::from_array($data['winning_team']);
		}

		return $pick;
	}
}
