<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-domain.php';

interface Wp_Bracket_Builder_Sport_Repository_Interface {
	public function add(Wp_Bracket_Builder_Sport $sport);
	public function get(int $id = null, string $name = null): Wp_Bracket_Builder_Sport;
	public function get_all(): array;
	public function delete(int $id);
	public function update(Wp_Bracket_Builder_Sport $sport);
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

	public function add(Wp_Bracket_Builder_Sport $sport) {
		$this->sports[] = $sport;
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

	public function delete(int $id) {
		unset($this->sports[$id]);
	}

	public function update(Wp_Bracket_Builder_Sport $sport) {
		$this->sports[$sport->id] = $sport;
	}
}

class Wp_Bracket_Builder_Sport_Repository implements Wp_Bracket_Builder_Sport_Repository_Interface {
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function add(Wp_Bracket_Builder_Sport $sport) {
		$table_name = $this->table_name();
		$this->wpdb->insert(
			$table_name,
			[
				'name' => $sport->name,
			]
		);
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
			return new Wp_Bracket_Builder_Sport($sport->id, $sport->name);
		}

		return null;
	}

	public function get_all(): array {
		$table_name = $this->table_name();
		$sports = $this->wpdb->get_results(
			"SELECT * FROM {$table_name}"
		);

		$sports_array = [];

		foreach ($sports as $sport) {
			$sports_array[] = new Wp_Bracket_Builder_Sport($sport->id, $sport->name);
		}

		return $sports_array;
	}

	public function delete(int $id) {
		$table_name = $this->table_name();
		$this->wpdb->delete(
			$table_name,
			[
				'id' => $id,
			]
		);
	}

	public function update(Wp_Bracket_Builder_Sport $sport) {
		$table_name = $this->table_name();
		$this->wpdb->update(
			$table_name,
			[
				'name' => $sport->name(),
			],
			[
				'id' => $sport->id,
			]
		);
	}

	private function table_name(): string {
		return $this->wpdb->prefix . 'bracket_builder_sports';
	}
}
