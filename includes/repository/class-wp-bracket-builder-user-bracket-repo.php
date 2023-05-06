<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket.php';

interface Wp_Bracket_Builder_User_Bracket_Repository_Interface {
	public function add(Wp_Bracket_Builder_User_Bracket $bracket): ?Wp_Bracket_Builder_User_Bracket;
	public function get(int $id): ?Wp_Bracket_Builder_User_Bracket;
	// public function get_all(): array;
	// public function delete(int $id): bool;
	// public function update(Wp_Bracket_Builder_Bracket $bracket): Wp_Bracket_Builder_Bracket;
}

class Wp_Bracket_Builder_User_Bracket_Repository implements Wp_Bracket_Builder_User_Bracket_Repository_Interface {
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function add(Wp_Bracket_Builder_User_Bracket $bracket): ?Wp_Bracket_Builder_User_Bracket {

		return null;
	}

	public function get(int $id): ?Wp_Bracket_Builder_User_Bracket {

		return null;
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
