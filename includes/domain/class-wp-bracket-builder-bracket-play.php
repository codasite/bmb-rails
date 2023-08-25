<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-base.php';

/**
 * This class creates a bracket pick object by submitting 
 */
// class Wp_Bracket_Builder_Bracket_Pick_Factory extends Wp_Bracket_Builder_Bracket_Base {


class Wp_Bracket_Builder_Bracket_Pick extends Wp_Bracket_Builder_Bracket_Base {

	/**
	 * @var int
	 */
	public $customer_id;

	/**
	 * @var int
	 */
	public $bracket_id;

	/**
	 * @var string
	 */
	public $img_url;

	/**
	 * @var string
	 */
	public $html;

	public function __construct(int $bracket_id, string $name, int $id = null, int $customer_id = null, string $html = null, string $img_url = null,  array $rounds = []) {
		parent::__construct($name, $id, $rounds);
		$this->customer_id = $customer_id;
		$this->bracket_id = $bracket_id;
		$this->html = $html;
		$this->img_url = $img_url;
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Bracket_Pick {
		$bracket_pick = new Wp_Bracket_Builder_Bracket_Pick($data['bracket_id'], $data['name']);

		if (isset($data['id'])) {
			$bracket_pick->id = (int) $data['id'];
		}

		if (isset($data['html'])) {
			$bracket_pick->html = $data['html'];
		}

		if (isset($data['img_url'])) {
			$bracket_pick->img_url = $data['img_url'];
		}

		if (isset($data['rounds'])) {
			$bracket_pick->rounds = array_map(function ($index, $round) {
				$round['depth'] = $index;
				return Wp_Bracket_Builder_Round::from_array($round);
			}, array_keys($data['rounds']), $data['rounds']);
		}

		return $bracket_pick;
	}

	public static function from_post(WP_Post $post): Wp_Bracket_Builder_Bracket_Pick | null {
		// $bracket_pick = new Wp_Bracket_Builder_Bracket_Pick($post->post_parent, $post->post_title, $post->post_author, $post->post_content, get_the_post_thumbnail_url($post->ID));

		// bail if post is not a bracket pick
		if ($post->post_type !== 'bracket_pick') {
			return null;
		}

		// name is store in the title field
		$pick_id = $post->ID;
		$name = $post->post_title;
		$bracket_id = $post->post_parent;
		$img_url = get_post_meta($post->ID, 'bracket_pick_images', true);
		$html = get_post_meta($post->ID, 'bracket_pick_html', true);

		$bracket_pick = new Wp_Bracket_Builder_Bracket_Pick($bracket_id, $name, $pick_id, null, $html, $img_url);

		return $bracket_pick;
	}

	/**
	 * This function returns an array of the bracket pick object to be used when inserting/updating a bracket pick post.
	 * The array returned from this function DOES NOT include field that are stored as post meta.
	 * 
	 * @return array
	 */
	public function to_post_array(): array {
		$pick = $this;
		$post_array = [
			'post_type' => 'bracket_pick',
			'post_parent' => $pick->bracket_id,
			'post_title' => $pick->name,
			'post_status' => 'publish',
		];

		if ($pick->id) {
			$post_array['ID'] = $pick->id;
		}

		return $post_array;
	}
}


class Wp_Bracket_Builder_Match_Pick {
	public $match_id;
	public $pick_id;
	public $winner_id;

	public function __construct(int $match_id, int $pick_id, int $winner_id) {
		$this->match_id = $match_id;
		$this->pick_id = $pick_id;
		$this->winner_id = $winner_id;
	}
}
