<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

interface Wp_Bracket_Builder_Bracket_Pick_Repository_Interface {
	public function add(Wp_Bracket_Builder_Bracket_Pick $bracket): ?Wp_Bracket_Builder_Bracket_Pick;
	public function get(int $id): ?Wp_Bracket_Builder_Bracket_Pick;
	public function get_all(): array;
	// public function get_all(): array;
	// public function delete(int $id): bool;
	// public function update(Wp_Bracket_Builder_Bracket $bracket): Wp_Bracket_Builder_Bracket;
}

class Wp_Bracket_Builder_Bracket_Pick_Repository implements Wp_Bracket_Builder_Bracket_Pick_Repository_Interface {
	/**
	 * @var Wp_Bracket_Builder_Utils
	 */
	private $utils;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	private Wp_Bracket_Builder_Bracket_Repository_Interface $bracket_repo;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->bracket_repo = new Wp_Bracket_Builder_Bracket_Repository();
		$this->utils = new Wp_Bracket_Builder_Utils();
	}

	// public function add(Wp_Bracket_Builder_Bracket_Pick $pick): ?Wp_Bracket_Builder_Bracket_Pick {
	// 	$bracket_id = $pick->bracket_id;

	// 	$bracket = $this->bracket_repo->get($bracket_id);
	// 	if (!$bracket) {
	// 		return null;
	// 	}

	// 	$name = $pick->name;
	// 	$cust_id = $pick->customer_id;
	// 	$img_url = $pick->img_url;
	// 	$table_name = $this->bracket_pick_table();

	// 	$this->wpdb->insert(
	// 		$table_name,
	// 		[
	// 			'bracket_id' => $bracket_id,
	// 			'name' => $name,
	// 			'img_url' => $img_url,
	// 			'customer_id' => $cust_id,
	// 		]
	// 	);

	// 	$pick->id = $this->wpdb->insert_id;
	// 	$this->insert_match_picks($pick, $bracket);
	// 	$pick = $this->get($pick->id);

	// 	return $pick;
	// }

	public function get(int $id): ?Wp_Bracket_Builder_Bracket_Pick {
		$post = get_post($id);
		$this->utils->log('got post: ' . json_encode($post));
		if (!$post || $post->post_type !== 'bracket_pick') {
			return null;
		}
		$pick = Wp_Bracket_Builder_Bracket_Pick::from_post($post);

		return $pick;
	}

	public function add(Wp_Bracket_Builder_Bracket_Pick $pick): ?Wp_Bracket_Builder_Bracket_Pick {
		// TODO: check if bracket exists
		//$bracket_id = $pick->bracket_id;

		$post_array = $pick->to_post_array();
		$this->utils->log('post array: ' . json_encode($post_array));

		$pick_id = wp_insert_post($post_array, true);

		if (is_wp_error($pick_id)) {
			$this->utils->log('error inserting post: ' . $pick_id->get_error_message(), 'error');
			return null;
		}
		$this->utils->log('pick id: ' . $pick_id);

		// need to update the post meta separately
		update_post_meta($pick_id, 'bracket_pick_html', wp_kses_post($pick->html));

		// refresh the pick object
		$pick = $this->get($pick_id);

		return $pick;
	}

	private function insert_match_picks(Wp_Bracket_Builder_Bracket_Pick $pick, Wp_Bracket_Builder_Bracket $bracket): void {
		$pick_id = $pick->id;

		if (!$pick_id) {
			echo 'pick id is null';
			return;
		}
		foreach ($pick->rounds as $i => $round) {
			if (!$round->matches) {
				echo 'round matches is null';
				continue;
			}
			foreach ($round->matches as $j => $match) {
				if (!$match->result) {
					echo 'match result is null';
					continue;
				}
				$match_id = $bracket->rounds[$i]->matches[$j]->id;
				$team_id = $match->result->id;
				if (!$match_id || !$team_id) {
					echo 'match id or team id is null';
					continue;
				}
				echo "inserting match pick: $pick_id, $match_id, $team_id";
				$this->insert_match_pick($pick_id, $match_id, $team_id);
			}
		}
	}

	private function insert_match_pick(int $bracket_pick_id, int $match_id, int $team_id): int {
		$table_name = $this->match_pick_table();

		$this->wpdb->insert(
			$table_name,
			[
				'bracket_pick_id' => $bracket_pick_id,
				'match_id' => $match_id,
				'team_id' => $team_id,
			]
		);

		return $this->wpdb->insert_id;
	}

	// public function get(int $id): ?Wp_Bracket_Builder_Bracket_Pick {
	// 	$table_name = $this->bracket_pick_table();
	// 	$sql = "SELECT * FROM $table_name WHERE id = $id";
	// 	$result = $this->wpdb->get_row($sql, ARRAY_A);
	// 	if (!$result) {
	// 		return null;
	// 	}
	// 	$pick = Wp_Bracket_Builder_Bracket_Pick::from_array($result);
	// 	$bracket = $this->bracket_repo->get($result['bracket_id']);

	// 	$pick->rounds = $bracket->rounds;
	// 	$match_results_map = $this->get_match_results_map($pick->id);

	// 	$pick->fill_in_results($match_results_map);

	// 	return $pick;
	// }

	private function get_match_results_map(int $pick_id): array {
		$match_picks = $this->get_match_picks($pick_id);
		$match_results_map = [];
		foreach ($match_picks as $match_pick) {
			$match_results_map[$match_pick['match_id']] = $match_pick['team_id'];
		}
		return $match_results_map;
	}

	private function get_match_picks(int $pick_id): array {
		$table_name = $this->match_pick_table();
		$sql = "SELECT * FROM $table_name WHERE bracket_pick_id = $pick_id";
		$results = $this->wpdb->get_results($sql, ARRAY_A);
		return $results;
	}

	public function get_all($bracket_id = null): array {
		$table_name = $this->bracket_pick_table();
		$sql = "SELECT * FROM $table_name";
		if ($bracket_id) {
			$sql .= " WHERE bracket_id = $bracket_id";
		}
		$results = $this->wpdb->get_results($sql, ARRAY_A);
		$brackets = [];
		foreach ($results as $result) {
			$brackets[] = Wp_Bracket_Builder_Bracket_Pick::from_array($result);
		}
		return $brackets;
	}

	private function bracket_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_brackets';
	}
	private function cpt_table(): string {
		return $this->wpdb->prefix . 'posts';
	}
	private function bracket_pick_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_bracket_picks';
	}
	private function match_pick_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_match_picks';
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
