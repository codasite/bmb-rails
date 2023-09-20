<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-match-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

class Wp_Bracket_Builder_Bracket_Tournament_Repository extends Wp_Bracket_Builder_Custom_Post_Repository_Base {

	/**
	 * @var Wp_Bracket_Builder_Bracket_Template_Repository
	 */
	private $template_repo;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Match_Repository
	 */
	private $match_repo;

	public function __construct() {
		$this->template_repo = new Wp_Bracket_Builder_Bracket_Template_Repository();
		$this->match_repo = new Wp_Bracket_Builder_Bracket_Match_Repository();
	}

	public function add(Wp_Bracket_Builder_Bracket_Tournament $tournament): ?Wp_Bracket_Builder_Bracket_Tournament {

		$tournament_id = $this->insert_post($tournament, true);

		if (is_wp_error($tournament_id)) {
			return null;
		}

		$this->match_repo->insert_picks($tournament_id, $tournament->results);

		# refresh from db
		$tournament = $this->get($tournament_id);
		return $tournament;
	}

	public function get(int|WP_Post|null $post = null, bool $fetch_matches = true, bool $fetch_results = true): ?Wp_Bracket_Builder_Bracket_Tournament {
		$tournament_post = get_post($post);

		if ($tournament_post === null) {
			return null;
		}

		if ($tournament_post->post_type !== Wp_Bracket_Builder_Bracket_Tournament::get_post_type()) {
			return null;
		}

		$template_id = get_post_meta($tournament_post->ID, 'bracket_template_id', true);

		// This is to avoid "Argument #1 ($bracket_template_id) must be of type int, string given" error
		if ($template_id === '') {
			return null;
		}

		$results = $fetch_results ? $this->match_repo->get_picks($tournament_post->ID) : [];

		$tournament = new Wp_Bracket_Builder_Bracket_Tournament(
			(int)$template_id,
			$tournament_post->ID,
			$tournament_post->post_title,
			$tournament_post->post_author,
			$tournament_post->post_status,
			get_post_datetime($tournament_post->ID, 'date', 'local'),
			get_post_datetime($tournament_post->ID, 'date_gmt', 'gmt'),
			$this->template_repo->get($template_id, $fetch_matches),
			$results,
		);

		return $tournament;
	}

	public function get_all(array|WP_Query $query = []): array {
		if ($query instanceof WP_Query) {
			return $this->tournaments_from_query($query);
		}

		$default_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Tournament::get_post_type(),
			'post_status' => 'any',
		];

		$args = array_merge($default_args, $query);

		$query = new WP_Query($args);
		return $this->tournaments_from_query($query);
	}

	public function tournaments_from_query(WP_Query $query) {
		$tournaments = [];
		foreach ($query->posts as $post) {
			$tournaments[] = $this->get($post, false);
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

	public function update(Wp_Bracket_Builder_Bracket_Tournament $tournament): ?Wp_Bracket_Builder_Bracket_Tournament {
		$tournament_id = $this->update_post($tournament);

		if (is_wp_error($tournament_id)) {
			return null;
		}

		$this->match_repo->update_picks($tournament_id, $tournament->results);

		# refresh from db
		$tournament = $this->get($tournament_id);
		return $tournament;
	}
}
