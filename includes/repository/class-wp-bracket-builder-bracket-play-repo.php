<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wp-bracket-builder-bracket-play-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

class Wp_Bracket_Builder_Bracket_Play_Repository extends Wp_Bracket_Builder_Custom_Post_Repository_Base {
	/**
	 * @var Wp_Bracket_Builder_Utils
	 */
	private $utils;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Pick_Service
	 */
	private $bracket_pick_service;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Tournament_Repository
	 */
	private $tournament_repo;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Template_Repository
	 */
	private $template_repo;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
		$this->template_repo = new Wp_Bracket_Builder_Bracket_Template_Repository();
		$this->utils = new Wp_Bracket_Builder_Utils();
		$this->bracket_pick_service = new Wp_Bracket_Builder_Bracket_Pick_Service();
	}

	public function get(int|WP_Post|null $post = null, bool $fetch_picks = true, bool $fetch_matches = true): ?Wp_Bracket_Builder_Bracket_Play {
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
			$this->tournament_repo->get($tournament_id, $fetch_matches),
		);

		return $play;
	}

	private function get_picks(int $play_id): array {
		$table_name = $this->match_pick_table();
		$sql = "SELECT * FROM $table_name WHERE bracket_play_id = $play_id ORDER BY round_index, match_index ASC";
		$results = $this->wpdb->get_results($sql, ARRAY_A);

		$picks = [];
		foreach ($results as $result) {
			$winning_team_id = $result['winning_team_id'];
			$winning_team = $this->template_repo->get_team($winning_team_id);
			$picks[] = new Wp_Bracket_Builder_Match_Pick(
				$result['round_index'],
				$result['match_index'],
				$winning_team_id,
				$result['id'],
				$winning_team,
			);
		}
		return $picks;
	}

	public function get_all(array $query_args): array {
		$default_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Play::get_post_type(),
			'posts_per_page' => -1,
			'post_status' => 'any',
		];

		$args = array_merge($default_args, $query_args);

		$query = new WP_Query($args);

		$plays = [];
		foreach ($query->posts as $post) {
			$plays[] = $this->get($post, false, false);
		}
		return $plays;
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
		$table_name = $this->match_pick_table();
		foreach ($picks as $pick) {
			$this->wpdb->insert(
				$table_name,
				[
					'bracket_play_id' => $play_id,
					'round_index' => $pick->round_index,
					'match_index' => $pick->match_index,
					'winning_team_id' => $pick->winning_team_id,
				]
			);
		}
	}

	private function match_pick_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_match_picks';
	}
}
