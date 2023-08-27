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
		Wp_Bracket_Builder_Bracket_Tournament $tournament,
		string $title = '',
		int $id = null,
		int $author = null,
		string $html = '',
		string $img_url = '',
		DateTimeImmutable|false $date = false,
		DateTimeImmutable|false $date_gmt = false,
		array $picks = [],
	) {
		parent::__construct(
			$id,
			$title,
			$author,
			'publish',
			$date,
			$date_gmt,
		);
		$this->tournament = $tournament;
		$this->html = $html;
		$this->img_url = $img_url;
		$this->picks = $picks;
	}

	// public static function from_array(array $data): Wp_Bracket_Builder_Bracket_Play {
	// 	$bracket_pick = new Wp_Bracket_Builder_Bracket_Play($data['bracket_id'], $data['name']);

	// 	if (isset($data['id'])) {
	// 		$bracket_pick->id = (int) $data['id'];
	// 	}

	// 	if (isset($data['html'])) {
	// 		$bracket_pick->html = $data['html'];
	// 	}

	// 	if (isset($data['img_url'])) {
	// 		$bracket_pick->img_url = $data['img_url'];
	// 	}

	// 	if (isset($data['rounds'])) {
	// 		$bracket_pick->rounds = array_map(function ($index, $round) {
	// 			$round['depth'] = $index;
	// 			return Wp_Bracket_Builder_Round::from_array($round);
	// 		}, array_keys($data['rounds']), $data['rounds']);
	// 	}

	// 	return $bracket_pick;
	// }

	// public static function from_post(WP_Post $post): Wp_Bracket_Builder_Bracket_Play | null {
	// 	// $bracket_pick = new Wp_Bracket_Builder_Bracket_Play($post->post_parent, $post->post_title, $post->post_author, $post->post_content, get_the_post_thumbnail_url($post->ID));

	// 	// bail if post is not a bracket pick
	// 	if ($post->post_type !== 'bracket_pick') {
	// 		return null;
	// 	}

	// 	// name is store in the title field
	// 	$pick_id = $post->ID;
	// 	$name = $post->post_title;
	// 	$bracket_id = $post->post_parent;
	// 	$img_url = get_post_meta($post->ID, 'bracket_pick_images', true);
	// 	$html = get_post_meta($post->ID, 'bracket_pick_html', true);

	// 	$bracket_pick = new Wp_Bracket_Builder_Bracket_Play($bracket_id, $name, $pick_id, null, $html, $img_url);

	// 	return $bracket_pick;
	// }

	/**
	 * This function returns an array of the bracket pick object to be used when inserting/updating a bracket pick post.
	 * The array returned from this function DOES NOT include field that are stored as post meta.
	 * 
	 * @return array
	 */
	// public function to_post_array(): array {
	// 	$pick = $this;
	// 	$post_array = [
	// 		'post_type' => 'bracket_pick',
	// 		'post_parent' => $pick->bracket_id,
	// 		'post_title' => $pick->name,
	// 		'post_status' => 'publish',
	// 	];

	// 	if ($pick->id) {
	// 		$post_array['ID'] = $pick->id;
	// 	}

	// 	return $post_array;
	// }
}


class Wp_Bracket_Builder_Match_Pick {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var Wp_Bracket_Builder_Match
	 */
	public $match;

	/** 
	 * @var Wp_Bracket_Builder_Team
	 */
	public $winner;

	public function __construct(
		Wp_Bracket_Builder_Match $match,
		Wp_Bracket_Builder_Team $winner,
		int $id = null,
	) {
		$this->id = $id;
		$this->match = $match;
		$this->winner = $winner;
	}
}
