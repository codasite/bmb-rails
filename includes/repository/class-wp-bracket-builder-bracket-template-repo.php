<?php

use function PHPUnit\Framework\isInstanceOf;

require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

class Wp_Bracket_Builder_Bracket_Template_Repository extends Wp_Bracket_Builder_Custom_Post_Repository_Base {
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
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
		$table_name = $this->match_table();
		foreach ($matches as $match) {
			// Skip if match is null
			if ($match === null) {
				continue;
			}
			// First, insert teams
			$team1 = $this->insert_team_for_template($template_id, $match->team1);
			$team2 = $this->insert_team_for_template($template_id, $match->team2);

			$this->wpdb->insert(
				$table_name,
				[
					'bracket_template_id' => $template_id,
					'round_index' => $match->round_index,
					'match_index' => $match->match_index,
					'team1_id' => $team1->id,
					'team2_id' => $team2->id,
				]
			);
			$match->id = $this->wpdb->insert_id;
		}
	}

	private function insert_team_for_template(int $template_id, ?Wp_Bracket_Builder_Team $team): ?Wp_Bracket_Builder_Team {
		if (empty($team)) {
			return $team;
		}
		$table_name = $this->team_table();
		$this->wpdb->insert(
			$table_name,
			[
				'name' => $team->name,
				'bracket_template_id' => $template_id,
			]
		);
		$team->id = $this->wpdb->insert_id;
		return $team;
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

	public function get_teams(): array {
		$table_name = $this->team_table();
		$team_results = $this->wpdb->get_results(
			"SELECT * FROM {$table_name}",
			ARRAY_A
		);
		$teams = [];
		foreach ($team_results as $team) {
			$teams[] = new Wp_Bracket_Builder_Team($team['name'], $team['id']);
		}
		return $teams;
	}

	public function get_matches(): array {
		// get all matches for all templates
		$table_name = $this->match_table();
		$match_results = $this->wpdb->get_results(
			"SELECT * FROM {$table_name} ORDER BY bracket_template_id, round_index, match_index ASC",
			ARRAY_A
		);
		$matches = [];
		foreach ($match_results as $match) {
			$team1 = $this->get_team($match['team1_id']);
			$team2 = $this->get_team($match['team2_id']);

			$matches[] = new Wp_Bracket_Builder_Match(
				$match['round_index'],
				$match['match_index'],
				$team1,
				$team2,
				$match['id'],
			);
		}

		return $matches;
	}

	private function get_matches_for_template(int $template_id): array {
		$table_name = $this->match_table();
		$match_results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE bracket_template_id = %d ORDER BY round_index, match_index ASC",
				$template_id
			),
			ARRAY_A
		);
		$matches = [];
		foreach ($match_results as $match) {
			$team1 = $this->get_team($match['team1_id']);
			$team2 = $this->get_team($match['team2_id']);

			// $matches[$match['round_index']][$match['match_index']] = new Wp_Bracket_Builder_Match(
			$matches[] = new Wp_Bracket_Builder_Match(
				$match['round_index'],
				$match['match_index'],
				$team1,
				$team2,
				$match['id'],
			);
		}

		return $matches;
	}

	/**
	 * could get all teams for template instead
	 */
	public function get_team(int|null $id): ?Wp_Bracket_Builder_Team {
		if ($id === null) {
			return null;
		}

		$table_name = $this->team_table();
		$team = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE id = %d",
				$id
			),
			ARRAY_A
		);
		return new Wp_Bracket_Builder_Team($team['name'], $team['id']);
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
		// Changed this to false so users can still see deleted templates. 
		return $this->delete_post($id, $force);
	}

	// public function delete(int $id): bool {
	// 	// $table_name = $this->bracket_table();
	// 	// $this->wpdb->delete(
	// 	// 	$table_name,
	// 	// 	[
	// 	// 		'id' => $id,
	// 	// 	]
	// 	// );
	// 	// Get the associated cpt id
	// 	$utils = new Wp_Bracket_Builder_Utils();
	// 	$bracket = $this->get($id);

	// 	if ($bracket !== null && property_exists($bracket, 'cpt_id')) {
	// 		$cpt_id = $bracket->cpt_id;
	// 		wp_delete_post($cpt_id);
	// 	} else {
	// 		$utils->log_sentry_message("Error deleting bracket {$id}: could not find associated cpt id", \Sentry\Severity::error());
	// 		return false;
	// 	}
	// 	return true;
	// }

	// public function set_active(int $id, bool $active): bool {
	// 	// Get the associated cpt id
	// 	$bracket = $this->get($id);
	// 	$cpt_id = $bracket->cpt_id;
	// 	// Update the cpt status
	// 	wp_update_post([
	// 		'ID' => $cpt_id,
	// 		'post_status' => $active ? 'publish' : 'draft',
	// 	]);
	// 	// $table_name = $this->bracket_table();
	// 	// $this->wpdb->update(
	// 	// 	$table_name,
	// 	// 	[
	// 	// 		'active' => $active ? 1 : 0,
	// 	// 	],
	// 	// 	[
	// 	// 		'id' => $id,
	// 	// 	]
	// 	// );
	// 	return true;
	// }


	public function add_max_teams(int $max) {
		$table_name = $this->max_teams_table();
		$existing_max_team_info = $this->get_max_teams();

		$data = array(
			"max_teams" => $max
		);

		if (isset($existing_max_team_info)) {
			$where = array('id' => $existing_max_team_info['id']);
			$this->wpdb->update($table_name, $data, $where);
		} else {
			$this->wpdb->insert($table_name, $data);
		}
	}

	public function get_max_teams() {
		$table_name = $this->max_teams_table();
		$existing_max_team_info = $this->wpdb->get_row(
			$this->wpdb->prepare("SELECT * FROM {$table_name}"),
			ARRAY_A
		);

		return $existing_max_team_info;
	}


	private function match_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_matches';
	}
	private function team_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_teams';
	}
	private function max_teams_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_max_teams';
	}
}
