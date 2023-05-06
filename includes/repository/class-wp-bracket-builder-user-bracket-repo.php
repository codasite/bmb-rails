<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket.php';

interface Wp_Bracket_Builder_Bracket_Repository_Interface {
	public function add(Wp_Bracket_Builder_Bracket $bracket): Wp_Bracket_Builder_Bracket;
	public function get(int $id): ?Wp_Bracket_Builder_Bracket;
	public function get_all(): array;
	public function delete(int $id): bool;
	// public function update(Wp_Bracket_Builder_Bracket $bracket): Wp_Bracket_Builder_Bracket;
}

class Wp_Bracket_Builder_Bracket_Repository implements Wp_Bracket_Builder_Bracket_Repository_Interface {
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function add(Wp_Bracket_Builder_Bracket $bracket): Wp_Bracket_Builder_Bracket {

		$cpt_id = $this->insert_cpt($bracket);

		$table_name = $this->bracket_table();
		$this->wpdb->insert(
			$table_name,
			[
				// 'name' => $bracket->name,
				'cpt_id' => $cpt_id,
				// 'active' => $bracket->active ? 1 : 0,
				'num_rounds' => $bracket->num_rounds,
				'num_wildcards' => $bracket->num_wildcards,
				'wildcard_placement' => $bracket->wildcard_placement,
			]
		);
		$bracket_id = $this->wpdb->insert_id;
		if ($bracket->rounds) {
			$this->insert_rounds_for_bracket($bracket_id, $bracket->rounds);
		}
		# refresh from db
		$bracket = $this->get($bracket_id);
		return $bracket;
	}

	private function insert_cpt(Wp_Bracket_Builder_Bracket $bracket): int {
		$post_id = wp_insert_post([
			'post_title' => $bracket->name,
			'post_type' => 'bracket',
			'post_status' => $bracket->active ? 'publish' : 'draft',
		]);
		return $post_id;
	}

	private function insert_rounds_for_bracket(int $bracket_id, array $rounds): void {
		$table_name = $this->round_table();
		foreach ($rounds as $round) {
			$this->wpdb->insert(
				$table_name,
				[
					'name' => $round->name,
					'bracket_id' => $bracket_id,
					'depth' => $round->depth,
				]
			);
			$round->id = $this->wpdb->insert_id;
			$this->insert_matches_for_round($bracket_id, $round);
		}
	}

	private function insert_matches_for_round(int $bracket_id, Wp_Bracket_Builder_Round $round): void {
		$table_name = $this->match_table();
		foreach ($round->matches as $match) {
			// Skip if match is null
			if ($match === null) {
				continue;
			}
			// First, insert teams
			$team1_id = null;
			$team2_id = null;

			if ($match->team1 !== null) {
				if ($match->team1->id === null) {
					$match->team1 = $this->insert_team_for_bracket($bracket_id, $match->team1);
				}
				$team1_id = $match->team1->id;
			}

			if ($match->team2 !== null) {
				if ($match->team2->id === null) {
					$match->team2 = $this->insert_team_for_bracket($bracket_id, $match->team2);
				}
				$team2_id = $match->team2->id;
			}

			$this->wpdb->insert(
				$table_name,
				[
					'round_id' => $round->id,
					'round_index' => $match->index,
					'team1_id' => $team1_id,
					'team2_id' => $team2_id,
				]
			);
			$match->id = $this->wpdb->insert_id;
		}
	}

	private function insert_team_for_bracket(int $bracket_id, Wp_Bracket_Builder_Team $team): Wp_Bracket_Builder_Team {
		$table_name = $this->team_table();
		$this->wpdb->insert(
			$table_name,
			[
				'name' => $team->name,
				'bracket_id' => $bracket_id,
				'seed' => $team->seed,
			]
		);
		$team->id = $this->wpdb->insert_id;
		return $team;
	}

	public function get(int $id = null, int $post_id = null, WP_Post $post = null): ?Wp_Bracket_Builder_Bracket {
		$bracket_arr = null;

		if ($id) {
			$bracket_arr = $this->get_bracket_array_by_id($id);
		} else if ($post_id) {
			$bracket_arr = $this->get_bracket_array_by_post_id($post_id);
		} else if ($post) {
			$bracket_arr = $this->get_bracket_array_by_post_id($post->ID);
		}

		if ($bracket_arr) {
			# get rounds
			$round_arr = $this->get_rounds_for_bracket($bracket_arr['id']);
			$bracket_arr['rounds'] = $round_arr;
			return Wp_Bracket_Builder_Bracket::from_array($bracket_arr);
		}

		return null;
	}

	private function get_bracket_array_by_id(int $id): ?array {
		$bracket_table = $this->bracket_table();
		$cpt_table = $this->cpt_table();
		$bracket_fields = $this->bracket_fields();

		$bracket_arr = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT {$bracket_fields}
					FROM {$bracket_table}
					LEFT JOIN {$cpt_table} ON {$bracket_table}.cpt_id = {$cpt_table}.ID
					WHERE {$bracket_table}.id = %d AND {$cpt_table}.post_type = 'bracket'",
				$id
			),
			ARRAY_A
		);

		return $bracket_arr;
	}

	private function get_bracket_array_by_post_id(int $post_id): ?array {
		$bracket_table = $this->bracket_table();
		$cpt_table = $this->cpt_table();
		$bracket_fields = $this->bracket_fields();

		$bracket_arr = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT {$bracket_fields}
					FROM {$bracket_table}
					LEFT JOIN {$cpt_table} ON {$bracket_table}.cpt_id = {$cpt_table}.ID
					WHERE {$cpt_table}.ID = %d",
				$post_id
			),
			ARRAY_A
		);

		return $bracket_arr;
	}


	private function get_rounds_for_bracket(int $bracket_id): array {
		$table_name = $this->round_table();
		$rounds = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE bracket_id = %d ORDER BY depth ASC",
				$bracket_id
			),
			ARRAY_A
		);
		foreach ($rounds as $index => $round) {
			// Max matches is 2^(round_index)
			$max_matches = pow(2, $round['depth']);
			$rounds[$index]['matches'] = $this->get_matches_for_round($round['id'], $max_matches);
		}
		return $rounds;
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
		$bracket_table = $this->bracket_table();
		$cpt_table = $this->cpt_table();
		$bracket_fields = $this->bracket_fields();
		$brackets = $this->wpdb->get_results(
			// "SELECT id, cpt_id, num_rounds, num_wildcards, wildcard_placement, created_at,
			// "SELECT {$bracket_table}.id, cpt_id, num_rounds, num_wildcards, wildcard_placement, 
			// 	post_title as name, post_date_gmt as created_at,
			"SELECT {$bracket_fields},
				(SELECT COUNT(*) FROM {$this->user_bracket_table()} WHERE bracket_id = {$bracket_table}.id) as num_submissions
			 FROM {$bracket_table}
			 LEFT JOIN {$cpt_table} ON {$bracket_table}.cpt_id = {$cpt_table}.ID
			 ORDER BY created_at DESC",
			ARRAY_A
		);

		$brackets_array = [];

		foreach ($brackets as $bracket) {
			$brackets_array[] = Wp_Bracket_Builder_Bracket::from_array($bracket);
		}

		return $brackets_array;
	}

	public function delete(int $id): bool {
		// $table_name = $this->bracket_table();
		// $this->wpdb->delete(
		// 	$table_name,
		// 	[
		// 		'id' => $id,
		// 	]
		// );
		// Get the associated cpt id
		$bracket = $this->get($id);
		$cpt_id = $bracket->cpt_id;
		wp_delete_post($cpt_id);

		return true;
	}

	public function set_active(int $id, bool $active): bool {
		// Get the associated cpt id
		$bracket = $this->get($id);
		$cpt_id = $bracket->cpt_id;
		// Update the cpt status
		wp_update_post([
			'ID' => $cpt_id,
			'post_status' => $active ? 'publish' : 'draft',
		]);
		// $table_name = $this->bracket_table();
		// $this->wpdb->update(
		// 	$table_name,
		// 	[
		// 		'active' => $active ? 1 : 0,
		// 	],
		// 	[
		// 		'id' => $id,
		// 	]
		// );
		return true;
	}

	private function bracket_fields(): string {
		$bracket_table = $this->bracket_table();
		return implode(', ', [
			"$bracket_table.id",
			'post_title as name',
			'cpt_id',
			'num_rounds',
			'num_wildcards',
			'wildcard_placement',
			'post_date_gmt as created_at',
			"IF(post_status = 'publish', 1, 0) as active",
		]);
	}

	private function bracket_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_brackets';
	}
	private function cpt_table(): string {
		return $this->wpdb->prefix . 'posts';
	}
	private function user_bracket_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_user_brackets';
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
}
