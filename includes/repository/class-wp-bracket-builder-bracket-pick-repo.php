<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket.php';

interface Wp_Bracket_Builder_Bracket_Pick_Repository_Interface {
	public function add(Wp_Bracket_Builder_Bracket_Pick $bracket): ?Wp_Bracket_Builder_Bracket_Pick;
	public function get(int $id): ?Wp_Bracket_Builder_Bracket_Pick;
	public function get_all(): array;
	// public function get_all(): array;
	// public function delete(int $id): bool;
	// public function update(Wp_Bracket_Builder_Bracket $bracket): Wp_Bracket_Builder_Bracket;
}

class Wp_Bracket_Builder_Bracket_Pick_Repository implements Wp_Bracket_Builder_Bracket_Pick_Repository_Interface {
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function add(Wp_Bracket_Builder_Bracket_Pick $bracket): ?Wp_Bracket_Builder_Bracket_Pick {
		return null;
	}

	public function get(int $id): ?Wp_Bracket_Builder_Bracket_Pick {

		return null;
	}

	public function get_all(): array {
		// $table_name = $this->bracket_pick_table();
		// $sql = "SELECT * FROM $table_name";
		// $results = $this->wpdb->get_results($sql);
		// $brackets = [];
		// foreach ($results as $result) {
		// 	$brackets[] = $this->map_row_to_bracket($result);
		// }
		// return $brackets;
		return [];
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
