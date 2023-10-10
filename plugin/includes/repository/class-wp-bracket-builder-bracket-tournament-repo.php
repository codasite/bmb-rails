<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-match-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-team-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

class Wp_Bracket_Builder_Bracket_Tournament_Repository extends Wp_Bracket_Builder_Custom_Post_Repository_Base {

	/**
	 * @var Wp_Bracket_Builder_Bracket_Template_Repository
	 */
	private $template_repo;

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
		$this->template_repo = new Wp_Bracket_Builder_Bracket_Template_Repository();
		$this->team_repo = new Wp_Bracket_Builder_Bracket_Team_Repository();
		parent::__construct();
	}

	public function add(Wp_Bracket_Builder_Bracket_Tournament $tournament): ?Wp_Bracket_Builder_Bracket_Tournament {

		$post_id = $this->insert_post($tournament, true, true);

		if ($post_id instanceof WP_Error) {
			throw new Exception($post_id->get_error_message());
		}

		$template_post_id = $tournament->bracket_template_id;
		$template = $tournament->bracket_template;

		// Either a template post id or a template object must be provided to create a tournament
		if (!$template_post_id) {
			if ($template) {
				$template = $this->template_repo->add($template);
				$template_post_id = $template->id;
			} else {
				throw new Exception('bracket_template_id or bracket_template is required');
			}
		}

		$template_data = $this->template_repo->get_template_data($template_post_id);
		$template_id = $template_data['id'];

		if (!$template_id) {
			throw new Exception('Bracket template data id not found');
		}

		$tournament_id = $this->insert_tournament_data([
			'post_id' => $post_id,
			'bracket_template_post_id' => $template_post_id,
			'bracket_template_id' => $template_id,
		]);

		if ($tournament_id && $tournament->results) {
			$this->insert_results($tournament_id, $tournament->results);
		}

		# refresh from db
		return $this->get($post_id);
	}

	private function insert_tournament_data(array $data): int {
		$table_name = $this->tournament_table();
		$this->wpdb->insert(
			$table_name,
			$data
		);
		return $this->wpdb->insert_id;
	}

	private function insert_results(int $tournament_id, array $results): void {
		foreach ($results as $result) {
			$this->insert_result($tournament_id, $result);
		}
	}

	private function insert_result(int $tournament_id, Wp_Bracket_Builder_Match_Pick $pick): void {
		$table_name = $this->results_table();
		$this->wpdb->insert(
			$table_name,
			[
				'bracket_tournament_id' => $tournament_id,
				'round_index' => $pick->round_index,
				'match_index' => $pick->match_index,
				'winning_team_id' => $pick->winning_team_id,
			]
		);
	}


	public function get(int|WP_Post|null $post = null, bool $fetch_results = true, bool $fetch_template = true, bool $fetch_matches = true): ?Wp_Bracket_Builder_Bracket_Tournament {
		$tournament_post = get_post($post);

		if (!$tournament_post || $tournament_post->post_type !== Wp_Bracket_Builder_Bracket_Tournament::get_post_type()) {
			return null;
		}

		$tournament_data = $this->get_tournament_data($tournament_post);
		if (!isset($tournament_data['id'])) {
			return null;
		}
		$tournament_id = $tournament_data['id'];
		$template_post_id = $tournament_data['bracket_template_post_id'];
		$template = $template_post_id && $fetch_template ? $this->template_repo->get($template_post_id, $fetch_matches) : null;
		$results = $fetch_results ? $this->get_tournament_results($tournament_id) : [];
		$author_id = (int)$tournament_post->post_author;

		$data = [
			'bracket_template_id' => $template_post_id,
			'id' => $tournament_post->ID,
			'title' => $tournament_post->post_title,
			'author' => $author_id,
			'status' => $tournament_post->post_status,
			'date' => get_post_meta($tournament_post->ID, 'date', true),
			'published_date' => get_post_datetime($tournament_post->ID, 'date', 'gmt'),
			'bracket_template' => $template,
			'results' => $results,
			'slug' => $tournament_post->post_name,
			'author_display_name' => $author_id ? get_the_author_meta('display_name', $tournament_post->author_id) : '',
		];

		$tournament = new Wp_Bracket_Builder_Bracket_Tournament($data);

		return $tournament;
	}

	public function get_tournament_results(int|null $tournament_id): array {
		$table_name = $this->results_table();
		$where = $tournament_id ? "WHERE bracket_tournament_id = $tournament_id" : '';
		$sql = "SELECT * FROM $table_name $where ORDER BY round_index, match_index ASC";
		$data = $this->wpdb->get_results($sql, ARRAY_A);

		$tournament_results = [];
		foreach ($data as $result) {
			$winning_team_id = $result['winning_team_id'];
			$winning_team = $this->team_repo->get_team($winning_team_id);
			$tournament_results[] = new Wp_Bracket_Builder_Match_Pick(
				$result['round_index'],
				$result['match_index'],
				$winning_team_id,
				$result['id'],
				$winning_team,
			);
		}
		return $tournament_results;
	}

	public function get_tournament_data(int|WP_Post|null $post): array {
		if (!$post || $post instanceof WP_Post && $post->post_type !== Wp_Bracket_Builder_Bracket_Tournament::get_post_type()) {
			return [];
		}

		if ($post instanceof WP_Post) {
			$post = $post->ID;
		}

		$table_name = $this->tournament_table();
		$query = $this->wpdb->prepare(
			"SELECT * FROM $table_name WHERE post_id = %d",
			$post
		);

		$data = $this->wpdb->get_row($query, ARRAY_A);
		if (!$data) {
			return [];
		}
		return $data;
	}

	public function get_all(array|WP_Query $query = [], array $options = [
		'fetch_results' => false,
		'fetch_template' => false,
		'fetch_matches' => false,
	]): array {
		if ($query instanceof WP_Query) {
			return $this->tournaments_from_query($query, $options);
		}

		$default_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Tournament::get_post_type(),
			'post_status' => 'any',
		];

		$args = array_merge($default_args, $query);

		$query = new WP_Query($args);
		return $this->tournaments_from_query($query, $options);
	}

	public function tournaments_from_query(WP_Query $query, array $options = []) {
		$tournaments = [];
		foreach ($query->posts as $post) {
			$fetch_results = isset($options['fetch_results']) ? $options['fetch_results'] : false;
			$fetch_template = isset($options['fetch_template']) ? $options['fetch_template'] : false;
			$fetch_matches = isset($options['fetch_matches']) ? $options['fetch_matches'] : false;
			$tournament = $this->get($post, $fetch_results, $fetch_template, $fetch_matches);
			if ($tournament) {
				$tournaments[] = $tournament;
			}
		}
		return $tournaments;
	}

	public function filter($args) {
		$author = isset($args['author']) ? $args['author'] : null;
		$status = isset($args['status']) ? $args['status'] : null;

		$filter_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Tournament::get_post_type(),
			'post_status' => $status === null ? 'any' : $status,
			'author' => $author,
		];

		$query = new WP_Query($filter_args);
		$tournaments = [];
		foreach ($query->posts as $post) {
			if ($post->post_status === $status || $status === null) {
				$tournaments[] = $this->get($post, false);
			}
		}
		return $tournaments;
	}

	public function delete(int $id, $force = false): bool {
		return $this->delete_post($id, $force);
	}

	public function update(Wp_Bracket_Builder_Bracket_Tournament|int|null $tournament, array|null $data = null): ?Wp_Bracket_Builder_Bracket_Tournament {
		if (!$tournament || !$data) {
			return null;
		}

		if (!($tournament instanceof Wp_Bracket_Builder_Bracket_Tournament)) {
			$tournament = $this->get($tournament);
		}

		if (!$tournament) {
			return null;
		}

		$array = $tournament->to_array();
		$updated_array = array_merge($array, $data);

		$tournament = Wp_Bracket_Builder_Bracket_Tournament::from_array($updated_array);

		$post_id = $this->update_post($tournament);

		if (is_wp_error($post_id)) {
			return null;
		}

		$tournament_data = $this->get_tournament_data($post_id);
		$tournament_id = $tournament_data['id'];

		if ($tournament_id && $tournament->results) {
			$this->update_results($tournament_id, $tournament->results);
		}

		# refresh from db
		$tournament = $this->get($post_id);
		return $tournament;
	}

	public function update_results(int $tournament_id, array|null $new_results): void {
		if ($new_results === null) {
			return;
		}

		$old_results = $this->get_tournament_results($tournament_id);

		if (empty($old_results)) {
			$this->insert_results($tournament_id, $new_results);
			return;
		}

		foreach ($new_results as $new_result) {
			$pick_exists = false;
			foreach ($old_results as $old_result) {
				if ($new_result->round_index === $old_result->round_index && $new_result->match_index === $old_result->match_index) {
					$pick_exists = true;
					$this->wpdb->update(
						$this->results_table(),
						[
							'winning_team_id' => $new_result->winning_team_id,
						],
						[
							'id' => $old_result->id,
						]
					);
				}
			}
			if (!$pick_exists) {
				$this->insert_result($tournament_id, $new_result);
			}
		}
	}

	public function tournament_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_tournaments';
	}

	public function results_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_tournament_results';
	}

	function get_author_emails_by_tournament_id($tournament_post_id) {
		global $wpdb;
	
		$query = "
			SELECT DISTINCT u.ID AS author_id, u.user_email AS author_email
			FROM {$wpdb->prefix}bracket_builder_plays p
			JOIN {$wpdb->prefix}posts po ON p.post_id = po.ID
			JOIN {$wpdb->prefix}posts t ON p.bracket_tournament_post_id = t.ID
			JOIN {$wpdb->prefix}users u ON po.post_author = u.ID
			WHERE t.ID = %d;
		";
	
		$results = $wpdb->get_results($wpdb->prepare($query, $tournament_post_id));
	
		return $results;
	}
}
