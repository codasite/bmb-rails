<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-bracket-play.php';
class Wp_Bracket_Builder_User_Profile {

	/**
	 * The user object.
	 * 
	 * @var WP_User
	 */
	private $wp_user;

	public function __construct(WP_User $wp_user) {
		$this->wp_user = $wp_user;
	}

	static public function get_current() {
		$user = wp_get_current_user();
		return new self($user);
	}

	public function __get($key) {
		return $this->wp_user->$key;
	}

	public function get_num_plays() {
		$query = new WP_Query(
			[
				'post_type' => Wpbb_BracketPlay::get_post_type(),
				'author' => $this->wp_user->ID,
				'posts_per_page' => -1,
			]
		);
		return $query->found_posts;
	}

	public function get_tournament_wins() {
		return 4;
	}

	public function get_total_accuracy() {
		return 0.5;
	}
}
