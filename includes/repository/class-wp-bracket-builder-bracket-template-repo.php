<?php

use function PHPUnit\Framework\isInstanceOf;

require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-match-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

class Wp_Bracket_Builder_Bracket_Template_Repository extends Wp_Bracket_Builder_Custom_Post_Repository_Base {
	/**
	 * @var Wp_Bracket_Builder_Bracket_Match_Repository
	 */
	private $match_repo;

	public function __construct() {
		global $wpdb;
		$this->match_repo = new Wp_Bracket_Builder_Bracket_Match_Repository();
	}

	public function add(Wp_Bracket_Builder_Bracket_Template $template): ?Wp_Bracket_Builder_Bracket_Template {

		$template_id = $this->insert_post($template, true);

		if (is_wp_error($template_id)) {
			return null;
		}

		if ($template->matches) {
			$this->insert_matches_for_template($template_id, $template->matches);
		}

		# refresh from db
		$template = $this->get($template_id);
		return $template;
	}

	private function insert_matches_for_template(int $template_id, array $matches): void {
		$this->match_repo->insert_matches($template_id, $matches);
	}

	public function get(int|WP_Post|null|string $post = null, bool $fetch_matches = true): ?Wp_Bracket_Builder_Bracket_Template {
		$template_post = get_post($post);

		if ($template_post === null) {
			return null;
		}

		if ($template_post->post_type !== Wp_Bracket_Builder_Bracket_Template::get_post_type()) {
			return null;
		}

		$matches = $fetch_matches ? $this->get_matches_for_template($template_post->ID) : [];

		$template = new Wp_Bracket_Builder_Bracket_Template(
			$template_post->ID,
			$template_post->post_title,
			$template_post->post_author,
			$template_post->post_status,
			get_post_meta($template_post->ID, 'num_teams', true),
			get_post_meta($template_post->ID, 'wildcard_placement', true),
			get_post_datetime($template_post->ID, 'date', 'local'),
			get_post_datetime($template_post->ID, 'date_gmt', 'gmt'),
			get_post_meta($template_post->ID, 'html', true),
			get_post_meta($template_post->ID, 'img_url', true),
			$matches,
		);

		return $template;
	}


	private function get_matches_for_template(int $template_id): array {
		return $this->match_repo->get_matches($template_id);
	}

	public function get_all(array|WP_Query $query = []): array {
		if ($query instanceof WP_Query) {
			return $this->templates_from_query($query);
		}

		$default_args = [
			'post_type' => Wp_Bracket_Builder_Bracket_Template::get_post_type(),
			'post_status' => 'any',
		];

		$args = array_merge($default_args, $query);
		$query = new WP_Query($args);

		return $this->templates_from_query($query);
	}

	public function templates_from_query(WP_Query $query): array {
		$templates = [];
		foreach ($query->posts as $post) {
			$templates[] = $this->get($post, false);
		}
		return $templates;
	}

	public function delete(int $id, $force = false): bool {
		return $this->delete_post($id, $force);
	}
}
