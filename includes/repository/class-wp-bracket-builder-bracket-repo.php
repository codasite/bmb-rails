<?php
// require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket.php';

// interface Wp_Bracket_Builder_Bracket_Repository_Interface {
// 	public function add(Wp_Bracket_Builder_Bracket $bracket): Wp_Bracket_Builder_Bracket;
// 	public function get(int $id = null, string $name = null): Wp_Bracket_Builder_Bracket;
// 	public function get_all(): array;
// 	public function delete(int $id): bool;
// 	public function update(Wp_Bracket_Builder_Bracket $bracket): Wp_Bracket_Builder_Bracket;
// }

// class Wp_Bracket_Builder_Bracket_Repository implements Wp_Bracket_Builder_Bracket_Repository_Interface {
// 	private $wpdb;

// 	public function __construct() {
// 		global $wpdb;
// 		$this->wpdb = $wpdb;
// 	}

// 	public function add(Wp_Bracket_Builder_Bracket $bracket): Wp_Bracket_Builder_Bracket {
// 		$table_name = $this->bracket_table();
// 		$this->wpdb->insert(
// 			$table_name,
// 			[
// 				'name' => $bracket->name,
// 			]
// 		);
// 		$bracket->id = $this->wpdb->insert_id;
// 		// if ($bracket->teams) {
// 		// 	$this->insert_teams_for_bracket($bracket->id, $bracket->teams);
// 		// }
// 		# refresh from db
// 		$bracket = $this->get($bracket->id);
// 		return $bracket;
// 	}

// 	public function get(int $id = null, string $name = null): Wp_Bracket_Builder_Bracket {
// 		$bracket_arr = null;
// 		$table_name = $this->bracket_table();

// 		if ($id) {
// 			$bracket_arr = $this->wpdb->get_row(
// 				$this->wpdb->prepare(
// 					"SELECT * FROM {$table_name} WHERE id = %d",
// 					$id
// 				),
// 				ARRAY_A
// 			);
// 		} elseif ($name) {
// 			$bracket_arr = $this->wpdb->get_row(
// 				$this->wpdb->prepare(
// 					"SELECT * FROM {$table_name} WHERE name = %s",
// 					$name
// 				),
// 				ARRAY_A
// 			);
// 		}

// 		if ($bracket_arr) {
// 			# get teams
// 			// $teams_arr = $this->get_teams_for_bracket($bracket_arr['id']);
// 			// $bracket_arr['teams'] = $teams_arr;
// 			return Wp_Bracket_Builder_Bracket::from_array($bracket_arr);
// 		}

// 		return null;
// 	}

// 	private function get_teams_for_bracket(int $bracket_id): array {
// 		$table_name = $this->team_table();
// 		$teams = $this->wpdb->get_results(
// 			$this->wpdb->prepare(
// 				"SELECT * FROM {$table_name} WHERE bracket_id = %d",
// 				$bracket_id
// 			),
// 			ARRAY_A
// 		);
// 		return $teams;
// 	}

// 	public function get_all(): array {
// 		$table_name = $this->bracket_table();
// 		$brackets = $this->wpdb->get_results(
// 			"SELECT * FROM {$table_name}",
// 			ARRAY_A
// 		);

// 		$brackets_array = [];

// 		foreach ($brackets as $bracket) {
// 			$brackets_array[] = Wp_Bracket_Builder_Bracket::from_array($bracket);
// 		}

// 		return $brackets_array;
// 	}

// 	public function delete(int $id): bool {
// 		$table_name = $this->bracket_table();
// 		$this->wpdb->delete(
// 			$table_name,
// 			[
// 				'id' => $id,
// 			]
// 		);
// 		return true;
// 	}

// 	public function update(Wp_Bracket_Builder_Bracket $bracket): Wp_Bracket_Builder_Bracket {
// 		$table_name = $this->bracket_table();
// 		$old_bracket = $this->get($bracket->id);
// 		$update_array = [];
// 		if ($old_bracket->name !== $bracket->name) {
// 			$update_array['name'] = $bracket->name;
// 		}
// 		// if update array is not empty, update
// 		if (!empty($update_array)) {
// 			$this->wpdb->update(
// 				$table_name,
// 				$update_array,
// 				['id' => $bracket->id,]
// 			);
// 		}
// 		// $this->update_teams_for_bracket($old_bracket, $bracket);
// 		// refresh from db
// 		$bracket = $this->get($bracket->id);
// 		return $bracket;
// 	}
// 	// private function update_teams_for_bracket(Wp_Bracket_Builder_Bracket $old_bracket, Wp_Bracket_Builder_Bracket $bracket): void {
// 	// 	$table_name = $this->team_table();
// 	// 	$old_teams = $old_bracket->teams;
// 	// 	$new_teams = $bracket->teams;
// 	// 	if ($new_teams === null) {
// 	// 		// do nothing if teams are null
// 	// 		return;
// 	// 	}
// 	// 	if (empty($old_teams) && !empty($new_teams)) {
// 	// 		// if old teams are null, insert new teams
// 	// 		$this->insert_teams_for_bracket($bracket->id, $new_teams);
// 	// 		return;
// 	// 	}
// 	// 	if ($new_teams === []) {
// 	// 		// if new teams are empty but not null, delete old teams
// 	// 		$this->delete_teams_for_bracket($old_bracket->id);
// 	// 		return;
// 	// 	}
// 	// 	// insert, delete, or update teams as necessary
// 	// 	$old_team_map = [];
// 	// 	$new_team_map = [];
// 	// 	$teams_to_insert = [];

// 	// 	foreach ($old_teams as $team) {
// 	// 		// old teams are guaranteed to have ids
// 	// 		$old_team_map[$team->id] = $team;
// 	// 	}
// 	// 	foreach ($new_teams as $team) {
// 	// 		// new teams may not have ids, if they don't, they need to be inserted
// 	// 		if ($team->id) {
// 	// 			$new_team_map[$team->id] = $team;
// 	// 		} else {
// 	// 			$teams_to_insert[] = $team;
// 	// 		}
// 	// 	}

// 	// 	$old_ids = array_keys($old_team_map);
// 	// 	$new_ids = array_keys($new_team_map);


// 	// 	// mark teams that need to be deleted, inserted, or updated
// 	// 	$ids_to_delete = array_diff($old_ids, $new_ids);
// 	// 	$ids_to_insert = array_diff($new_ids, $old_ids);
// 	// 	$exisiting_ids = array_intersect($old_ids, $new_ids);


// 	// 	// delete teams
// 	// 	if (!empty($ids_to_delete)) {
// 	// 		$this->delete_teams_by_id($ids_to_delete);
// 	// 	}
// 	// 	// insert teams
// 	// 	if (!empty($ids_to_insert) || !empty($teams_to_insert)) {
// 	// 		// append new teams with ids to teams to insert
// 	// 		foreach ($ids_to_insert as $id) {
// 	// 			$teams_to_insert[] = $new_team_map[$id];
// 	// 		}
// 	// 		$this->insert_teams_for_bracket($bracket->id, $teams_to_insert);
// 	// 	}

// 	// 	// determine which teams need to be updated
// 	// 	$teams_to_update = [];
// 	// 	foreach ($exisiting_ids as $id) {
// 	// 		$old_team = $old_team_map[$id];
// 	// 		$new_team = $new_team_map[$id];
// 	// 		if (!$old_team->equals($new_team)) {
// 	// 			$teams_to_update[] = $new_team;
// 	// 		}
// 	// 	}
// 	// 	// update teams
// 	// 	if (!empty($teams_to_update)) {
// 	// 		$this->update_teams($teams_to_update);
// 	// 	}
// 	// }

// 	private function insert_teams_for_bracket(int $bracket_id, array $teams): void {
// 		$table_name = $this->team_table();
// 		$insert_sql = "INSERT INTO {$table_name} (name, bracket_id) VALUES ";
// 		$team_values = [];
// 		foreach ($teams as $team) {
// 			$team_values[] = $this->wpdb->prepare('(%s, %d)', $team->name, $bracket_id);
// 		}
// 		$insert_sql .= implode(',', $team_values);
// 		$this->wpdb->query($insert_sql);
// 	}

// 	private function update_teams(array $teams): void {
// 		// Conditional update for multiple rows. 
// 		// See: https://stackoverflow.com/questions/20255138/sql-update-multiple-records-in-one-query
// 		$table_name = $this->team_table();
// 		$update_sql = "UPDATE {$table_name} SET name = CASE id ";
// 		$team_values = [];
// 		foreach ($teams as $team) {
// 			$team_values[] = $this->wpdb->prepare('WHEN %d THEN %s', $team->id, $team->name);
// 		}
// 		$update_sql .= implode(' ', $team_values);
// 		$update_sql .= ' ELSE name END WHERE id IN(';
// 		$team_ids = [];
// 		foreach ($teams as $team) {
// 			$team_ids[] = $team->id;
// 		}
// 		$update_sql .= implode(',', $team_ids);
// 		$update_sql .= ')';
// 		$this->wpdb->query($update_sql);
// 	}

// 	private function delete_teams_for_bracket(int $bracket_id): void {
// 		$table_name = $this->team_table();
// 		$this->wpdb->delete(
// 			$table_name,
// 			[
// 				'bracket_id' => $bracket_id,
// 			]
// 		);
// 	}

// 	private function delete_teams_by_id(array $ids): void {
// 		$delete_ids = implode(',', $ids);
// 		$table_name = $this->team_table();
// 		$delete_sql = "DELETE FROM {$table_name} WHERE id IN({$delete_ids})";
// 		$this->wpdb->query($delete_sql);
// 	}

// 	private function bracket_table(): string {
// 		return $this->wpdb->prefix . 'bracket_builder_brackets';
// 	}
// 	private function team_table(): string {
// 		return $this->wpdb->prefix . 'bracket_builder_teams';
// 	}
// }
