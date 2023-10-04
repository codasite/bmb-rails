<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-match-pick.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-match.php';

/**
 * This interface can be implemented by any class that defines a type of bracket.
 */

interface Wp_Bracket_Builder_Bracket_Interface {

	/**
	 * A series of matches representing the bracket's initial structure.
	 * 
	 * @return Wp_Bracket_Builder_Match[]
	 */
	public function get_matches(): array;

	/**
	 * A series of match picks representing a bracket outcome.
	 * 
	 * @return Wp_Bracket_Builder_Match_Pick[]
	 */
	public function get_picks(): array;

	/**
	 * The title of the bracket
	 * 
	 * @return string
	 */
	public function get_title(): string;

	/**
	 * The bracket date
	 * 
	 * @return string
	 */
	public function get_date(): string;

	/**
	 * The number of teams in the bracket
	 * 
	 * @return int
	 */
	public function get_num_teams(): int;
}
