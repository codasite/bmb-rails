<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-domain.php';

interface Wp_Bracket_Builder_Sport_Repository_Interface {
	public function add(Wp_Bracket_Builder_Sport $sport): Wp_Bracket_Builder_Sport;
	public function get(int $id = null, string $name = null): Wp_Bracket_Builder_Sport;
	public function get_all(): array;
	public function delete(int $id): bool;
	public function update(Wp_Bracket_Builder_Sport $sport): Wp_Bracket_Builder_Sport;
}

class Wp_Bracket_Builder_Sport_Repository_Mock implements Wp_Bracket_Builder_Sport_Repository_Interface {
	private $sports;

	public function __construct() {
		$this->sports = [
			new Wp_Bracket_Builder_Sport('Football', 1, [
				new Wp_Bracket_Builder_Team('Broncos'),
				new Wp_Bracket_Builder_Team('Chiefs'),
				new Wp_Bracket_Builder_Team('Raiders'),
				new Wp_Bracket_Builder_Team('Chargers'),
			]),
			new Wp_Bracket_Builder_Sport('Basketball', 2, [
				new Wp_Bracket_Builder_Team('Nuggets'),
				new Wp_Bracket_Builder_Team('Rockets'),
				new Wp_Bracket_Builder_Team('Lakers'),
				new Wp_Bracket_Builder_Team('Clippers'),
			]),
			new Wp_Bracket_Builder_Sport('Baseball', 3, [
				new Wp_Bracket_Builder_Team('Rockies'),
				new Wp_Bracket_Builder_Team('Astros'),
				new Wp_Bracket_Builder_Team('Dodgers'),
				new Wp_Bracket_Builder_Team('Angels'),
			]),
		];
	}

	public function add(Wp_Bracket_Builder_Sport $sport): Wp_Bracket_Builder_Sport {
		$this->sports[] = $sport;
		return $sport;
	}

	public function get(int $id = null, string $name = null): Wp_Bracket_Builder_Sport {
		$sport = null;
		if ($id) {
			$sport = $this->sports[$id];
		} else if ($name) {
			foreach ($this->sports as $sport) {
				if ($sport->name === $name) {
					return $sport;
				}
			}
		}
		return $sport;
	}

	public function get_all(): array {
		return $this->sports;
	}

	public function delete(int $id): bool {
		unset($this->sports[$id]);
		return true;
	}

	public function update(Wp_Bracket_Builder_Sport $sport): Wp_Bracket_Builder_Sport {
		$this->sports[$sport->id] = $sport;
		return $sport;
	}
}

class Wp_Bracket_Builder_Sport_Repository implements Wp_Bracket_Builder_Sport_Repository_Interface {
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function add(Wp_Bracket_Builder_Sport $sport): Wp_Bracket_Builder_Sport {
		$table_name = $this->sport_table();
		$this->wpdb->insert(
			$table_name,
			[
				'name' => $sport->name,
			]
		);
		$sport->id = $this->wpdb->insert_id;
		if ($sport->teams) {
			$this->insert_teams_for_sport($sport->id, $sport->teams);
		}
		# refresh from db
		$sport = $this->get($sport->id);
		return $sport;
	}

	public function get(int $id = null, string $name = null): Wp_Bracket_Builder_Sport {
		$sport_arr = null;
		$table_name = $this->sport_table();

		if ($id) {
			$sport_arr = $this->wpdb->get_row(
				$this->wpdb->prepare(
					"SELECT * FROM {$table_name} WHERE id = %d",
					$id
				),
				ARRAY_A
			);
		} elseif ($name) {
			$sport_arr = $this->wpdb->get_row(
				$this->wpdb->prepare(
					"SELECT * FROM {$table_name} WHERE name = %s",
					$name
				),
				ARRAY_A
			);
		}

		if ($sport_arr) {
			# get teams
			$teams_arr = $this->get_teams_for_sport($sport_arr['id']);
			$sport_arr['teams'] = $teams_arr;
			return Wp_Bracket_Builder_Sport::from_array($sport_arr);
		}

		return null;
	}

	private function get_teams_for_sport(int $sport_id): array {
		$table_name = $this->team_table();
		$teams = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE sport_id = %d",
				$sport_id
			),
			ARRAY_A
		);
		return $teams;
	}

	public function get_all(): array {
		$table_name = $this->sport_table();
		$sports = $this->wpdb->get_results(
			"SELECT * FROM {$table_name}",
			ARRAY_A
		);

		$sports_array = [];

		foreach ($sports as $sport) {
			$sports_array[] = Wp_Bracket_Builder_Sport::from_array($sport);
		}

		return $sports_array;
	}

	public function delete(int $id): bool {
		$table_name = $this->sport_table();
		$this->wpdb->delete(
			$table_name,
			[
				'id' => $id,
			]
		);
		return true;
	}

	public function update(Wp_Bracket_Builder_Sport $sport): Wp_Bracket_Builder_Sport {
		$table_name = $this->sport_table();
		$old_sport = $this->get($sport->id);
		$update_array = [];
		if ($old_sport->name !== $sport->name) {
			$update_array['name'] = $sport->name;
		}
		// if update array is not empty, update
		if (!empty($update_array)) {
			$this->wpdb->update(
				$table_name,
				$update_array,
				['id' => $sport->id,]
			);
		}
		$this->update_teams_for_sport($old_sport, $sport);
		// refresh from db
		$sport = $this->get($sport->id);
		return $sport;
	}
	private function update_teams_for_sport(Wp_Bracket_Builder_Sport $old_sport, Wp_Bracket_Builder_Sport $sport): void {
		$table_name = $this->team_table();
		$old_teams = $old_sport->teams;
		$new_teams = $sport->teams;
		if ($new_teams === null) {
			// do nothing if teams are null
			return;
		}
		if (empty($old_teams) && !empty($new_teams)) {
			// if old teams are null, insert new teams
			$this->insert_teams_for_sport($sport->id, $new_teams);
			return;
		}
		if ($new_teams === []) {
			// if new teams are empty but not null, delete old teams
			$this->delete_teams_for_sport($old_sport->id);
			return;
		}
		// insert, delete, or update teams as necessary
		$old_team_map = [];
		$new_team_map = [];
		$teams_to_insert = [];

		foreach ($old_teams as $team) {
			// old teams are guaranteed to have ids
			$old_team_map[$team->id] = $team;
		}
		foreach ($new_teams as $team) {
			// new teams may not have ids, if they don't, they need to be inserted
			if ($team->id) {
				$new_team_map[$team->id] = $team;
			} else {
				$teams_to_insert[] = $team;
			}
		}

		$old_ids = array_keys($old_team_map);
		$new_ids = array_keys($new_team_map);


		// mark teams that need to be deleted, inserted, or updated
		$ids_to_delete = array_diff($old_ids, $new_ids);
		$ids_to_insert = array_diff($new_ids, $old_ids);
		$exisiting_ids = array_intersect($old_ids, $new_ids);


		// delete teams
		if (!empty($ids_to_delete)) {
			$this->delete_teams_by_id($ids_to_delete);
		}
		// insert teams
		if (!empty($ids_to_insert) || !empty($teams_to_insert)) {
			// append new teams with ids to teams to insert
			foreach ($ids_to_insert as $id) {
				$teams_to_insert[] = $new_team_map[$id];
			}
			$this->insert_teams_for_sport($sport->id, $teams_to_insert);
		}

		// determine which teams need to be updated
		$teams_to_update = [];
		foreach ($exisiting_ids as $id) {
			$old_team = $old_team_map[$id];
			$new_team = $new_team_map[$id];
			if (!$old_team->equals($new_team)) {
				$teams_to_update[] = $new_team;
			}
		}
		// update teams
		if (!empty($teams_to_update)) {
			$this->update_teams($teams_to_update);
		}
	}

	private function insert_teams_for_sport(int $sport_id, array $teams): void {
		$table_name = $this->team_table();
		$insert_sql = "INSERT INTO {$table_name} (name, sport_id) VALUES ";
		$team_values = [];
		foreach ($teams as $team) {
			$team_values[] = $this->wpdb->prepare('(%s, %d)', $team->name, $sport_id);
		}
		$insert_sql .= implode(',', $team_values);
		$this->wpdb->query($insert_sql);
	}

	private function update_teams(array $teams): void {
		// Conditional update for multiple rows. 
		// See: https://stackoverflow.com/questions/20255138/sql-update-multiple-records-in-one-query
		$table_name = $this->team_table();
		$update_sql = "UPDATE {$table_name} SET name = CASE id ";
		$team_values = [];
		foreach ($teams as $team) {
			$team_values[] = $this->wpdb->prepare('WHEN %d THEN %s', $team->id, $team->name);
		}
		$update_sql .= implode(' ', $team_values);
		$update_sql .= ' ELSE name END WHERE id IN(';
		$team_ids = [];
		foreach ($teams as $team) {
			$team_ids[] = $team->id;
		}
		$update_sql .= implode(',', $team_ids);
		$update_sql .= ')';
		$this->wpdb->query($update_sql);
	}

	private function delete_teams_for_sport(int $sport_id): void {
		$table_name = $this->team_table();
		$this->wpdb->delete(
			$table_name,
			[
				'sport_id' => $sport_id,
			]
		);
	}

	private function delete_teams_by_id(array $ids): void {
		$delete_ids = implode(',', $ids);
		$table_name = $this->team_table();
		$delete_sql = "DELETE FROM {$table_name} WHERE id IN({$delete_ids})";
		$this->wpdb->query($delete_sql);
	}

	private function sport_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_sports';
	}
	private function team_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_teams';
	}
}
