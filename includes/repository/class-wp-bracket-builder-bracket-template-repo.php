<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

// interface Wp_Bracket_Builder_Bracket_Template_Repository_Interface {
// 	public function add(Wp_Bracket_Builder_Bracket_Template $template): Wp_Bracket_Builder_Bracket_Template;
// 	public function get(int $id): ?Wp_Bracket_Builder_Bracket_Template;
// 	public function get_all(): array;
// 	public function delete(int $id): bool;
// 	public function add_max_teams(int $max);
// 	public function get_max_teams();
// 	public function get_user_brackets(): array;
// 	// public function update(Wp_Bracket_Builder_Bracket_Template $bracket): Wp_Bracket_Builder_Bracket_Template;
// }

class Wp_Bracket_Builder_Bracket_Template_Repository extends Wp_Bracket_Builder_Custom_Post_Repository_Base {
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	// public function add(Wp_Bracket_Builder_Bracket_Template $template): ?Wp_Bracket_Builder_Bracket_Template {
	public function add(Wp_Bracket_Builder_Bracket_Template $template): mixed {

		$template_id = $this->insert_post($template, true);

		return $template_id;

		// if (is_wp_error($template_id)) {
		// 	return null;
		// }

		// if ($template->matches) {
		// 	$this->insert_matches_for_template($template_id, $template->matches);
		// }

		// # refresh from db
		// $template = $this->get($template_id);
		// return $template;
	}

	private function insert_matches_for_template(int $template_id, array $matches): void {
		$table_name = $this->match_table();
		foreach ($matches as $match) {
			// Skip if match is null
			if ($match === null) {
				continue;
			}
			// First, insert teams
			$team1_id = null;
			$team2_id = null;

			if ($match->team1 !== null) {
				if ($match->team1->id === null) {
					$match->team1 = $this->insert_team_for_template($template_id, $match->team1);
				}
				$team1_id = $match->team1->id;
			}

			if ($match->team2 !== null) {
				if ($match->team2->id === null) {
					$match->team2 = $this->insert_team_for_template($template_id, $match->team2);
				}
				$team2_id = $match->team2->id;
			}

			$this->wpdb->insert(
				$table_name,
				[
					'bracket_template_id' => $template_id,
					'round_index' => $match->round_index,
					'match_index' => $$match->match_index,
					'team1_id' => $team1_id,
					'team2_id' => $team2_id,
				]
			);
			$match->id = $this->wpdb->insert_id;
		}
	}

	private function insert_team_for_template(int $template_id, Wp_Bracket_Builder_Team $team): Wp_Bracket_Builder_Team {
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

	public function get(int|WP_Post|null $post = null, bool $fetch_matches = true): ?Wp_Bracket_Builder_Bracket_Template {
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
		$table_name = $this->match_table();
		$match_results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE bracket_template_id = %d ORDER BY round_index, match_index ASC",
				$template_id
			),
			ARRAY_A
		);
		$matches = [];
		foreach ($match_results as $index => $match) {
			$team1 = $this->get_team($match['team1_id']);
			$team2 = $this->get_team($match['team2_id']);

			$matches[$match['round_index']][$match['match_index']] = new Wp_Bracket_Builder_Match(
				$match['id'],
				$match['round_index'],
				$match['match_index'],
				$team1,
				$team2,
			);
		}

		return $matches;
	}

	private function get_matches_for_round(int $round_id, int $max_matches): array {
		$table_name = $this->match_table();
		$matches = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE round_id = %d ORDER BY round_index ASC",
				$round_id
			),
			ARRAY_A
		);
		foreach ($matches as $index => $match) {
			$matches[$index]['team1'] = $match['team1_id'] === null ? null : $this->get_team_by_id($match['team1_id']);
			$matches[$index]['team2'] = $match['team2_id'] === null ? null : $this->get_team_by_id($match['team2_id']);
		}
		// If the length of the matches array is less than the max matches, pad it with nulls
		if (count($matches) < $max_matches) {
			$matches = $this->pad_matches($matches, $max_matches, $round_id);
		}

		return $matches;
	}

	// Not all rounds will have the max number of matches, so we need to pad the array with nulls
	// This is to account for brackets with wildcard rounds 
	private function pad_matches(array $matches, int $max_matches, int $round_id): array {
		$padded = array_pad([], $max_matches, null);
		foreach ($matches as $match) {
			$padded[$match['round_index']] = $match;
		}
		return $padded;
	}

	/**
	 * could get all teams for template instead
	 */
	private function get_team(int|null $id): ?Wp_Bracket_Builder_Team {
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

	private function get_team_by_id(int $team_id): array {
		$table_name = $this->team_table();
		$team = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE id = %d",
				$team_id
			),
			ARRAY_A
		);
		return $team;
	}

	public function get_all(): array {
		$query = new WP_Query([
			'post_type' => Wp_Bracket_Builder_Bracket_Template::get_post_type(),
			'posts_per_page' => -1,
		]);
		$templates = [];
		foreach ($query->posts as $post) {
			$templates[] = $this->get($post, false);
		}
		return $templates;
		// return $query->posts;
	}
	// public function get_all(): array {
	// 	$bracket_table = $this->bracket_table();
	// 	$cpt_table = $this->cpt_table();
	// 	$bracket_fields = $this->bracket_fields();
	// 	$brackets = $this->wpdb->get_results(
	// 		// "SELECT id, cpt_id, num_rounds, num_wildcards, wildcard_placement, created_at,
	// 		// "SELECT {$bracket_table}.id, cpt_id, num_rounds, num_wildcards, wildcard_placement, 
	// 		// 	post_title as name, post_date_gmt as created_at,
	// 		"SELECT {$bracket_fields},
	// 			(SELECT COUNT(*) FROM {$this->bracket_pick_table()} WHERE bracket_id = {$bracket_table}.id) as num_submissions
	// 		 FROM {$bracket_table}
	// 		 LEFT JOIN {$cpt_table} ON {$bracket_table}.cpt_id = {$cpt_table}.ID
	// 		 ORDER BY created_at DESC",
	// 		ARRAY_A
	// 	);

	// 	$brackets_array = [];

	// 	foreach ($brackets as $bracket) {
	// 		$brackets_array[] = Wp_Bracket_Builder_Bracket_Template::from_array($bracket);
	// 	}

	// 	return $brackets_array;
	// }

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

	// private function bracket_fields(): string {
	// 	$bracket_table = $this->bracket_table();
	// 	return implode(', ', [
	// 		"$bracket_table.id",
	// 		'post_title as name',
	// 		'cpt_id',
	// 		'num_rounds',
	// 		'num_wildcards',
	// 		'wildcard_placement',
	// 		'post_date_gmt as created_at',
	// 		"IF(post_status = 'publish', 1, 0) as active",
	// 	]);
	// }

	// public function add_max_teams(int $max) {
	// 	$table_name = $this->max_teams_table();
	// 	$existing_max_team_info = $this->get_max_teams();

	// 	$data = array(
	// 		"max_teams" => $max
	// 	);

	// 	if (isset($existing_max_team_info)) {
	// 		$where = array('id' => $existing_max_team_info['id']);
	// 		$this->wpdb->update($table_name, $data, $where);
	// 	} else {
	// 		$this->wpdb->insert($table_name, $data);
	// 	}
	// }

	// public function get_max_teams() {
	// 	$table_name = $this->max_teams_table();
	// 	$existing_max_team_info = $this->wpdb->get_row(
	// 		$this->wpdb->prepare("SELECT * FROM {$table_name}"),
	// 		ARRAY_A
	// 	);

	// 	return $existing_max_team_info;
	// }


	// public function get_user_brackets(): array {
	// 	$current_user = wp_get_current_user();
	// 	$current_user_id = $current_user->ID;

	// 	$bracket_table = $this->bracket_table();
	// 	$cpt_table = $this->cpt_table();
	// 	$bracket_fields = $this->bracket_fields();
	// 	$brackets = $this->wpdb->get_results(
	// 		"SELECT {$bracket_fields},
	// 			(SELECT COUNT(*) FROM {$this->bracket_pick_table()} WHERE bracket_id = {$bracket_table}.id) as num_submissions
	// 		 FROM {$bracket_table}
	// 		 LEFT JOIN {$cpt_table} ON {$bracket_table}.cpt_id = {$cpt_table}.ID where {$cpt_table}.post_author = $current_user_id
	// 		 ORDER BY created_at DESC",
	// 		ARRAY_A
	// 	);
	// 	$brackets_array = [];
	// 	foreach ($brackets as $bracket) {
	// 		$brackets_array[] = Wp_Bracket_Builder_Bracket_Template::from_array($bracket);
	// 	}
	// 	return $brackets_array;
	// }

	private function bracket_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_brackets';
	}
	private function cpt_table(): string {
		return $this->wpdb->prefix . 'posts';
	}
	private function bracket_pick_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_bracket_picks';
	}
	private function round_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_rounds';
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
