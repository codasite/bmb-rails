<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-team-repo.php';
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
	 * @var Wp_Bracket_Builder_Bracket_Team_Repository
	 */
	private $team_repo;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
		$this->team_repo = new Wp_Bracket_Builder_Bracket_Team_Repository();
		$this->utils = new Wp_Bracket_Builder_Utils();
	}

	public function get(
		int|WP_Post|null $post = null,
		bool $fetch_picks = true,
		bool $fetch_tournament = true,
		bool $fetch_results = true,
		bool $fetch_template = true,
		bool $fetch_matches = true,
	): ?Wp_Bracket_Builder_Bracket_Play {
		$play_post = get_post($post);

		if (!$play_post || $play_post->post_type !== Wp_Bracket_Builder_Bracket_Play::get_post_type()) {
			return null;
		}

		$play_data = $this->get_play_data($play_post);
		$play_id = $play_data['id'];
		$tournament_post_id = $play_data['bracket_tournament_post_id'];
		$tournament = $tournament_post_id && $fetch_tournament ? $this->tournament_repo->get($tournament_post_id, $fetch_results, $fetch_template, $fetch_matches) : null;
		$picks = $fetch_picks && $play_id ? $this->get_picks($play_id) : [];

		$play = new Wp_Bracket_Builder_Bracket_Play(
			$tournament_post_id,
			$play_post->post_author,
			$play_post->ID,
			$play_post->post_title,
			$play_post->post_status,
			get_post_meta($play_post->ID, 'html', true),
			get_post_meta($play_post->ID, 'img_url', true),
			get_post_datetime($play_post->ID, 'date', 'local'),
			get_post_datetime($play_post->ID, 'date_gmt', 'gmt'),
			$picks,
			$tournament,
		);

		return $play;
	}

	private function get_picks(int $play_id): array {
		$table_name = $this->picks_table();
		$where = $play_id ? "WHERE bracket_play_id = $play_id" : '';
		$sql = "SELECT * FROM $table_name $where ORDER BY round_index, match_index ASC";
		$data = $this->wpdb->get_results($sql, ARRAY_A);

		$picks = [];
		foreach ($data as $pick) {
			$winning_team_id = $pick['winning_team_id'];
			$winning_team = $this->team_repo->get_team($winning_team_id);
			$picks[] = new Wp_Bracket_Builder_Match_Pick(
				$pick['round_index'],
				$pick['match_index'],
				$winning_team_id,
				$pick['id'],
				$winning_team,
			);
		}
		return $picks;
	}

	public function get_play_data(int|WP_Post|null $play_post): array {

		if (!$play_post || $play_post instanceof WP_Post && $play_post->post_type !== Wp_Bracket_Builder_Bracket_Play::get_post_type()) {
			return [];
		}

		if ($play_post instanceof WP_Post) {
			$play_post = $play_post->ID;
		}

		$table_name = $this->plays_table();
		$play_data = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM $table_name WHERE post_id = %d",
				$play_post
			),
			ARRAY_A
		);

		return $play_data;
	}

	public function get_all(array|WP_Query $query): array {
		if ($query instanceof WP_Query) {
			return $this->plays_from_query($query);
		}

		$default_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Play::get_post_type(),
			'posts_per_page' => -1,
			'post_status' => 'any',
		];

		$args = array_merge($default_args, $query);

		$query = new WP_Query($args);

		return $this->plays_from_query($query);
	}

	public function plays_from_query(WP_Query $query): array {
		$plays = [];
		foreach ($query->posts as $post) {
			$plays[] = $this->get($post, false, false);
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
		$post_id = $this->insert_post($play, true);

		if (is_wp_error($post_id)) {
			return null;
		}

		$tournament_post_id = $play->tournament_id;

		if (!$tournament_post_id) {
			return null;
		}

		$tournament = $this->tournament_repo->get_tournament_data($tournament_post_id);
		$tournament_id = $tournament['id'];

		if (!$tournament_id) {
			return null;
		}

		$play_id = $this->insert_play_data([
			'post_id' => $post_id,
			'bracket_tournament_post_id' => $play->tournament_id,
			'bracket_tournament_id' => $tournament_id,
		]);

		if ($play_id && $play->picks) {
			$this->insert_picks($play_id, $play->picks);
		}

		return $this->get($post_id);
	}

	private function insert_play_data(array $data): int {
		$table_name = $this->plays_table();
		$this->wpdb->insert(
			$table_name,
			$data
		);
		return $this->wpdb->insert_id;
	}

	private function insert_picks(int $play_id, array $picks): void {
		foreach ($picks as $pick) {
			$this->insert_pick($play_id, $pick);
		}
	}

	private function insert_pick(int $play_id, Wp_Bracket_Builder_Match_Pick $pick): void {
		$table_name = $this->picks_table();
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

	public function picks_table() {
		return $this->wpdb->prefix . 'bracket_builder_match_picks';
	}

	public function plays_table() {
		return $this->wpdb->prefix . 'bracket_builder_plays';
	}
}
