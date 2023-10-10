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
		parent::__construct();
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
		if (!$play_id) {
			return null;
		}
		$tournament_post_id = $play_data['bracket_tournament_post_id'];
		$busted_id = $play_data['busted_play_post_id'];
		$tournament = $tournament_post_id && $fetch_tournament ? $this->tournament_repo->get($tournament_post_id, $fetch_results, $fetch_template, $fetch_matches) : null;
		$picks = $fetch_picks && $play_id ? $this->get_picks($play_id) : [];
		$author_id = (int) $play_post->post_author;

		$data = [
			'tournament_id' => $tournament_post_id,
			'author' => $author_id,
			'id' => $play_post->ID,
			'title' => $play_post->post_title,
			'status' => $play_post->post_status,
			'date' => get_post_datetime($play_post->ID, 'date', 'local'),
			'date_gmt' => get_post_datetime($play_post->ID, 'date_gmt', 'gmt'),
			'picks' => $picks,
			'tournament' => $tournament,
			'total_score' => $play_data['total_score'] ?? null,
			'accuracy_score' => $play_data['accuracy_score'] ?? null,
			'slug' => $play_post->post_name,
			'author_display_name' => $author_id ? get_the_author_meta('display_name', $author_id) : '',
			'busted_id' => $busted_id,
		];

		$play = new Wp_Bracket_Builder_Bracket_Play($data);

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
		if (!$play_data) {
			return [];
		}
		return $play_data;
	}

	public function get_all(array|WP_Query $query, array $options = [
		'fetch_picks' => false,
		'fetch_tournament' => false,
		'fetch_results' => false,
		'fetch_template' => false,
		'fetch_matches' => false,
	]): array {
		if ($query instanceof WP_Query) {
			return $this->plays_from_query($query, $options);
		}

		$default_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Play::get_post_type(),
			'posts_per_page' => -1,
			'post_status' => 'any',
		];

		$args = array_merge($default_args, $query);

		$query = new WP_Query($args);

		return $this->plays_from_query($query, $options);
	}

	public function plays_from_query(WP_Query $query, $options): array {
		$plays = [];
		foreach ($query->posts as $post) {
			$fetch_picks = $options['fetch_picks'] ?? false;
			$fetch_tournament = $options['fetch_tournament'] ?? false;
			$fetch_results = $options['fetch_results'] ?? false;
			$fetch_template = $options['fetch_template'] ?? false;
			$fetch_matches = $options['fetch_matches'] ?? false;

			$play = $this->get($post, $fetch_picks, $fetch_tournament, $fetch_results, $fetch_template, $fetch_matches);
			if ($play) {
				$plays[] = $play;
			}
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
		$post_id = $this->insert_post($play, true, true);

		if (is_wp_error($post_id)) {
			throw new Exception('Error creating play post');
		}

		$tournament_post_id = $play->tournament_id;

		if (!$tournament_post_id) {
			throw new Exception('tournament_post_id is required');
		}

		$tournament = $this->tournament_repo->get_tournament_data($tournament_post_id);
		$tournament_id = $tournament['id'] ?? null;

		if (!$tournament_id) {
			throw new Exception('tournament_id not found');
		}

		$busted_post_id = $play->busted_id;
		if ($busted_post_id !== null) {
			$busted_play_data = $this->get_play_data($busted_post_id);
			$busted_play_id = $busted_play_data['id'];
		}

		$play_id = $this->insert_play_data([
			'post_id' => $post_id,
			'bracket_tournament_post_id' => $play->tournament_id,
			'bracket_tournament_id' => $tournament_id,
			'busted_play_post_id' => $busted_post_id ?? null,
			'busted_play_id' => $busted_play_id ?? null,
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
