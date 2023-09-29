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

	public function __construct(
		int $id = null,
		string $title = '',
		int $author = null,
		string $status = 'publish',
		int $num_teams = null,
		int $wildcard_placement = null,
		DateTimeImmutable|false $date = false,
		DateTimeImmutable|false $date_gmt = false,
		string $html = '',
		string $img_url = '',
		array $matches = [],
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
		$this->num_teams = $num_teams;
		$this->wildcard_placement = $wildcard_placement;
		$this->html = $html;
		$this->img_url = $img_url;
		$this->matches = $matches;
	}

	public function get_num_rounds(): int {
		return ceil(log($this->num_teams, 2));
	}

	static public function get_post_type(): string {
		return 'bracket_template';
	}


	public function get_post_meta(): array {
		return [
			'num_teams' => $this->num_teams,
			'wildcard_placement' => $this->wildcard_placement,
			'html' => $this->html,
			'img_url' => $this->img_url,
		];
	}

	public function get_update_post_meta(): array {
		return [];
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Bracket_Template {
		$template = new Wp_Bracket_Builder_Bracket_Template();
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

		$matches = [];

		foreach ($data['matches'] as $match) {
			$matches[] = Wp_Bracket_Builder_Match::from_array($match);
		}
		$data['matches'] = $matches;

		foreach ($data as $key => $value) {
			if (property_exists($template, $key)) {
				$template->$key = $value;
			}
		}

		return $template;
	}
}
