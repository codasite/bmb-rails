<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-custom-post-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-match.php';

class Wp_Bracket_Builder_Bracket_Template extends Wp_Bracket_Builder_Post_Base {
	/**
	 * @var int
	 */
	public $num_teams;

	/**
	 * @var int
	 */
	public $wildcard_placement;

	/**
	 * @var string
	 * 
	 * HTML representation of the bracket. Used to generate bracket images.
	 */
	public $html;

	/**
	 * @var string
	 * 
	 * URL of the bracket image
	 */
	public $img_url;

	/**
	 * @var Wp_Bracket_Builder_Match[] Array of Wp_Bracket_Builder_Match objects
	 */
	public $matches;

	public function __construct(array $data = []) {

		parent::__construct($data);
		$this->num_teams = isset($data['num_teams']) ? (int) $data['num_teams'] : null;
		$this->wildcard_placement = isset($data['wildcard_placement']) ? (int) $data['wildcard_placement'] : null;
		$this->matches = $data['matches'] ?? [];
	}

	public function get_num_rounds(): int {
		if (!$this->num_teams) {
			return 0;
		}
		return ceil(log($this->num_teams, 2));
	}

	static public function get_post_type(): string {
		return 'bracket_template';
	}


	public function get_post_meta(): array {
		return [
			'num_teams' => $this->num_teams,
			'wildcard_placement' => $this->wildcard_placement,
		];
	}

	public function get_update_post_meta(): array {
		return [];
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Bracket_Template {
		if (!isset($data['num_teams'])) {
			throw new Exception('num_teams is required');
		}

		if (!isset($data['wildcard_placement'])) {
			throw new Exception('wildcard_placement is required');
		}

		if (!isset($data['author'])) {
			throw new Exception('author id is required');
		}

		if (!isset($data['title'])) {
			throw new Exception('title is required');
		}

		if (!isset($data['matches'])) {
			throw new Exception('matches is required');
		}

		$matches = [];

		foreach ($data['matches'] as $match) {
			$matches[] = Wp_Bracket_Builder_Match::from_array($match);
		}
		$data['matches'] = $matches;

		$template = new Wp_Bracket_Builder_Bracket_Template($data);

		return $template;
	}

	public function to_array(): array {
		$template = parent::to_array();
		$template['num_teams'] = $this->num_teams;
		$template['wildcard_placement'] = $this->wildcard_placement;
		$template['author'] = $this->author;
		$template['title'] = $this->title;

		if ($this->matches) {
			$matches = [];
			foreach ($this->matches as $match) {
				$matches[] = $match->to_array();
			}
			$template['matches'] = $matches;
		}

		return $template;
	}
}
