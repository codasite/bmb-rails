<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-custom-post-interface.php';

class Wp_Bracket_Builder_Bracket_Template extends Wp_Bracket_Builder_Post_Base implements Wp_Bracket_Builder_Custom_Post_Interface {
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
		string $status = 'draft',
		int $num_teams = null,
		int $wildcard_placement = null,
		DateTimeImmutable|false $date = false,
		DateTimeImmutable|false $date_gmt = false,
		string $html = '',
		string $img_url = '',
		array $matches = []
	) {
		parent::__construct(
			$id,
			$title,
			$author,
			$status,
			$date,
			$date_gmt,
		);
		$this->num_teams = $num_teams;
		$this->wildcard_placement = $wildcard_placement;
		$this->html = $html;
		$this->img_url = $img_url;
		$this->matches = $matches;
	}

	static public function get_post_type(): string {
		return 'bracket_template';
	}

	public function get_post_data(): array {
		return [
			'post_title' => $this->title,
			'post_author' => $this->author,
			'post_status' => $this->status,
			'post_type' => self::get_post_type(),
		];
	}

	public function get_post_meta(): array {
		return [
			'num_teams' => $this->num_teams,
			'wildcard_placement' => $this->wildcard_placement,
			'html' => $this->html,
			'img_url' => $this->img_url,
			'matches' => $this->matches,
		];
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Bracket_Template {
		$template = new Wp_Bracket_Builder_Bracket_Template();
		$matches = [];

		if (isset($data['matches'])) {
			foreach ($data['matches'] as $match) {
				$matches[] = Wp_Bracket_Builder_Match::from_array($match);
			}
			unset($data['matches']);
		}
		// $data['id'],
		// $data['title'],
		// $data['author'],
		// $data['status'],
		// $data['num_teams'],
		// $data['wildcard_placement'],
		// false,
		// false,
		// $data['html'],
		// $data['img_url'],
		// );

		foreach ($data as $key => $value) {
			if (property_exists($template, $key)) {
				$template->$key = $value;
			}
		}

		$template->matches = $matches;

		return $template;
	}

	// public static function from_wp_post(WP_Post $post) {
	// 	$bracket = new Wp_Bracket_Builder_Bracket_Template(
	// 		$post->ID,
	// 		$post->post_title,
	// 		$post->post_author,
	// 		$post->post_status,
	// 		get_post_meta($post->ID, 'num_teams', true),
	// 		get_post_meta($post->ID, 'wildcard_placement', true),
	// 		get_post_datetime($post->ID, 'date', 'local'),
	// 		get_post_datetime($post->ID, 'date_gmt', 'gmt'),
	// 		get_post_meta($post->ID, 'html', true),
	// 		get_post_meta($post->ID, 'img_url', true),
	// 	);

	// 	return $bracket;
	// }

	// public static function from_array(array $data): Wp_Bracket_Builder_Bracket_Template {
	// 	$bracket = new Wp_Bracket_Builder_Bracket_Template(
	// 		$data['title'],
	// 		$data['num_rounds'],
	// 		$data['num_wildcards'],
	// 		$data['wildcard_placement'],
	// 		$data['active'],
	// 	);

	// 	if (isset($data['id'])) {
	// 		$bracket->id = (int) $data['id'];
	// 	}

	// 	if (isset($data['date'])) {
	// 		$bracket->date = new DateTime($data['date']);
	// 	}

	// 	if (isset($data['rounds'])) {
	// 		$bracket->rounds = array_map(function ($index, $round) {
	// 			$round['depth'] = $index;
	// 			return Wp_Bracket_Builder_Round::from_array($round);
	// 		}, array_keys($data['rounds']), $data['rounds']);
	// 	}

	// 	if (isset($data['id'])) {
	// 		$bracket->id = (int) $data['id'];
	// 	}

	// 	return $bracket;
	// }
}

class Wp_Bracket_Builder_Match {
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
	public $team1;

	/**
	 * @var Wp_Bracket_Builder_Team
	 */
	public $team2;

	public function __construct(int $round_index, int $match_index, Wp_Bracket_Builder_Team $team1 = null, Wp_Bracket_Builder_Team $team2 = null, int $id = null) {
		$this->id = $id;
		$this->round_index = $round_index;
		$this->match_index = $match_index;
		$this->team1 = $team1;
		$this->team2 = $team2;
	}

	static public function from_array(array $data): Wp_Bracket_Builder_Match {
		$match = new Wp_Bracket_Builder_Match($data['round_index'], $data['match_index']);

		if (isset($data['id'])) {
			$match->id = (int) $data['id'];
		}

		if (isset($data['team1'])) {
			$match->team1 = Wp_Bracket_Builder_Team::from_array($data['team1']);
		}

		if (isset($data['team2'])) {
			$match->team2 = Wp_Bracket_Builder_Team::from_array($data['team2']);
		}

		return $match;
	}
}

class Wp_Bracket_Builder_Team {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;


	public function __construct(string $name = null, int $id = null) {
		$this->id = $id;
		$this->name = $name;
	}

	static public function from_array(array $data): Wp_Bracket_Builder_Team {
		$team = new Wp_Bracket_Builder_Team();

		// if (isset($data['name'])) {
		// 	$team->name = $data['name'];
		// }

		// if (isset($data['id'])) {
		// 	$team->id = (int) $data['id'];
		// }

		foreach ($data as $key => $value) {
			if (property_exists($team, $key)) {
				$team->$key = $value;
			}
		}

		return $team;
	}

	public function equals(Wp_Bracket_Builder_Team $team): bool {
		if ($this->id !== $team->id) {
			return false;
		}
		// if ($this->name !== $team->name) {
		// 	return false;
		// }
		return true;
	}
}
