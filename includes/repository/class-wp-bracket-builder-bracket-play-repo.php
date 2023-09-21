<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-match-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wp-bracket-builder-bracket-play-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

class Wp_Bracket_Builder_Bracket_Play_Repository extends Wp_Bracket_Builder_Custom_Post_Repository_Base {
	/**
	 * @var Wp_Bracket_Builder_Utils
	 */
	private $utils;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Tournament_Repository
	 */
	private $tournament_repo;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Match_Repository
	 */
	private $match_repo;

	public function __construct() {
		$this->tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
		$this->utils = new Wp_Bracket_Builder_Utils();
		$this->match_repo = new Wp_Bracket_Builder_Bracket_Match_Repository();
	}

	public function get(int|WP_Post|null $post = null, bool $fetch_picks = true, bool $fetch_tournament = true, bool $fetch_matches = true,): ?Wp_Bracket_Builder_Bracket_Play {
		$play_post = get_post($post);

		if (!$play_post || $play_post->post_type !== Wp_Bracket_Builder_Bracket_Play::get_post_type()) {
			return null;
		}

		$picks = $fetch_picks ? $this->get_picks($play_post->ID) : [];

		$tournament_id = get_post_meta($play_post->ID, 'bracket_tournament_id', true);

		$play = new Wp_Bracket_Builder_Bracket_Play(
			$tournament_id,
			$play_post->post_author,
			$play_post->ID,
			$play_post->post_title,
			$play_post->post_status,
			get_post_meta($play_post->ID, 'html', true),
			get_post_meta($play_post->ID, 'img_url', true),
			get_post_datetime($play_post->ID, 'date', 'local'),
			get_post_datetime($play_post->ID, 'date_gmt', 'gmt'),
			$picks,
			$fetch_tournament ? $this->tournament_repo->get($tournament_id, $fetch_matches) : null,
		);

		return $play;
	}

	private function get_picks(int $play_id): array {
		return $this->match_repo->get_picks($play_id);
	}

	public function get_all(array|WP_Query $query, $fetch_picks = false, $fetch_tournament = false, $fetch_matches = false,): array {
		if ($query instanceof WP_Query) {
			return $this->plays_from_query($query, $fetch_picks, $fetch_tournament, $fetch_matches);
		}

		$default_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Play::get_post_type(),
			'posts_per_page' => -1,
			'post_status' => 'publish',
		];

		$args = array_merge($default_args, $query);

		$query = new WP_Query($args);

		return $this->plays_from_query($query, $fetch_picks, $fetch_tournament, $fetch_matches);
	}

	public function plays_from_query(WP_Query $query, $fetch_picks = false, $fetch_tournament = false, $fetch_matches = false,): array {
		$plays = [];
		foreach ($query->posts as $post) {
			$plays[] = $this->get($post, $fetch_picks, $fetch_tournament, $fetch_matches);
		}
		return $plays;
	}

	public function get_count(array $query_args): int {
		$default_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Play::get_post_type(),
			'posts_per_page' => -1,
			'post_status' => 'publish'
		];

		$args = array_merge($default_args, $query_args);

		$query = new WP_Query($args);

		return $query->found_posts;
	}

	// get all plays for a specific tournament
	public function get_all_by_tournament(int $tournament_id): array {
		$query = new WP_Query([
			'post_type' => Wp_Bracket_Builder_Bracket_Play::get_post_type(),
			'posts_per_page' => -1,
			'post_status' => 'any',
			'tournament_id' => $tournament_id,
		]);
		$plays = [];
		foreach ($query->posts as $post) {
			$plays[] = $this->get($post, false, false);
		}
		return $plays;
	}

	// get plays made by a specific author
	public function get_all_by_author(int $tournament_id): array {
		$query = new WP_Query([
			'post_type' => Wp_Bracket_Builder_Bracket_Play::get_post_type(),
			'posts_per_page' => -1,
			'post_status' => 'any',
			'author' => get_current_user_id(),
		]);

		$plays = [];
		foreach ($query->posts as $post) {
			$plays[] = $this->get($post, false, false);
		}
		return $plays;
	}

	public function add(Wp_Bracket_Builder_Bracket_Play $play): ?Wp_Bracket_Builder_Bracket_Play {
		$play_id = $this->insert_post($play, true);

		if (is_wp_error($play_id)) {
			return null;
		}

		// insert match picks
		$this->insert_match_picks($play_id, $play->picks);

		return $this->get($play_id);
	}

	private function insert_match_picks(int $play_id, array $picks): void {
		$this->match_repo->insert_picks($play_id, $picks);
	}
}
