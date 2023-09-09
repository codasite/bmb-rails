<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

class Wp_Bracket_Builder_Bracket_Tournament_Repository extends Wp_Bracket_Builder_Custom_Post_Repository_Base {
	private $wpdb;

	private $template_repo;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->template_repo = new Wp_Bracket_Builder_Bracket_Template_Repository();
	}

	public function add(Wp_Bracket_Builder_Bracket_Tournament $tournament): ?Wp_Bracket_Builder_Bracket_Tournament {

		$tournament_id = $this->insert_post($tournament, true);

		if (is_wp_error($tournament_id)) {
			return null;
		}

		# refresh from db
		$tournament = $this->get($tournament_id);
		return $tournament;
	}

	public function get(int|WP_Post|null $post = null, bool $fetch_matches = true): ?Wp_Bracket_Builder_Bracket_Tournament {
		$tournament_post = get_post($post);

		if ($tournament_post === null) {
			return null;
		}

		if ($tournament_post->post_type !== Wp_Bracket_Builder_Bracket_Tournament::get_post_type()) {
			return null;
		}

		$template_id = get_post_meta($tournament_post->ID, 'bracket_template_id', true);

		$tournament = new Wp_Bracket_Builder_Bracket_Tournament(
			(int)$template_id,
			$tournament_post->ID,
			$tournament_post->post_title,
			$tournament_post->post_author,
			$tournament_post->post_status,
			get_post_datetime($tournament_post->ID, 'date', 'local'),
			get_post_datetime($tournament_post->ID, 'date_gmt', 'gmt'),
			$this->template_repo->get($template_id, $fetch_matches),
		);

		return $tournament;
	}

	public function get_all(array $query_args = []): array {
		$default_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Tournament::get_post_type(),
			'post_status' => 'any',
		];

		$args = array_merge($default_args, $query_args);

		$query = new WP_Query($args);

		$tournaments = [];
		foreach ($query->posts as $post) {
			$tournaments[] = $this->get($post, false);
		}
		return $tournaments;
	}

	public function get_all_by_author(int $author_id, array $query_args = []): array {
		$default_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Tournament::get_post_type(),
			'post_status' => 'any',
			'author' => $author_id,
		];

		$args = array_merge($default_args, $query_args);

		$query = new WP_Query($args);

		$tournaments = [];
		foreach ($query->posts as $post) {
			$tournaments[] = $this->get($post, false);
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

		# refresh from db
		$tournament = $this->get($tournament_id);
		return $tournament;
	}
}
