<?php

use PHPUnit\Util\Log\TeamCity;

require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-match-pick.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-team.php';

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

	/**
	 * @var int
	 */
	public $total_score;

	/**
	 * @var float
	 */
	public $accuracy_score;

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
		int $total_score = 0,
		float $accuracy_score = 0.00,
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
		$this->total_score = $total_score;
		$this->accuracy_score = $accuracy_score;
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
