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
		$table_name = $this->table_name();
		$this->wpdb->insert(
			$table_name,
			[
				'name' => $sport->name,
			]
		);
		$sport->id = $this->wpdb->insert_id;
		if ($sport->teams) {
			$this->insert_teams($sport);
		}
		# refresh from db
		$sport = $this->get($sport->id);
		return $sport;
	}

	private function insert_teams(Wp_Bracket_Builder_Sport $sport): void {
		$insert_sql = "INSERT INTO {$this->wpdb->prefix}bracket_builder_teams (name, sport_id) VALUES ";
		$team_values = [];
		foreach ($sport->teams as $team) {
			$team_values[] = $this->wpdb->prepare('(%s, %d)', $team->name, $sport->id);
		}
		$insert_sql .= implode(',', $team_values);
		$this->wpdb->query($insert_sql);
	}

	public function get(int $id = null, string $name = null): Wp_Bracket_Builder_Sport {
		$sport = null;
		$table_name = $this->table_name();

		if ($id) {
			$sport = $this->wpdb->get_row(
				$this->wpdb->prepare(
					"SELECT * FROM {$table_name} WHERE id = %d",
					$id
				)
			);
		} elseif ($name) {
			$sport = $this->wpdb->get_row(
				$this->wpdb->prepare(
					"SELECT * FROM {$table_name} WHERE name = %s",
					$name
				)
			);
		}

		if ($sport) {
			# get teams
			$teams = $this->wpdb->get_results(
				$this->wpdb->prepare(
					"SELECT * FROM {$this->wpdb->prefix}bracket_builder_teams WHERE sport_id = %d",
					$sport->id
				),
				ARRAY_A
			);
			$teams_array = [];
			foreach ($teams as $team) {
				$teams_array[] = Wp_Bracket_Builder_Team::from_array($team);
			}
			return new Wp_Bracket_Builder_Sport($sport->name, $sport->id, $teams_array);
		}

		return null;
	}

	public function get_all(): array {
		$table_name = $this->table_name();
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
		$table_name = $this->table_name();
		$this->wpdb->delete(
			$table_name,
			[
				'id' => $id,
			]
		);
		return true;
	}

	public function update(Wp_Bracket_Builder_Sport $sport): Wp_Bracket_Builder_Sport {
		$table_name = $this->table_name();
		$this->wpdb->update(
			$table_name,
			[
				'name' => $sport->name,
			],
			[
				'id' => $sport->id,
			]
		);
		return $sport;
	}

	private function table_name(): string {
		return $this->wpdb->prefix . 'bracket_builder_sports';
	}
}
