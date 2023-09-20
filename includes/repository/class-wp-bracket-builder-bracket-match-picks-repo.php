<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wp-bracket-builder-bracket-play-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

/**
 * This class is meant be used by other repositories that need to fetch match picks
 */
class Wp_Bracket_Builder_Bracket_Match_Picks_Repository {
	/**
	 * @var Wp_Bracket_Builder_Utils
	 */
	private $utils;

	/**
	 * @var wpdb
	 */
	private $wpdb;


	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->utils = new Wp_Bracket_Builder_Utils();
	}

	public function get_picks(int $id): array {
		$table_name = $this->match_pick_table();
		$sql = "SELECT * FROM $table_name WHERE bracket_play_id = $id ORDER BY round_index, match_index ASC";
		$results = $this->wpdb->get_results($sql, ARRAY_A);

		$picks = [];
		foreach ($results as $result) {
			$winning_team_id = $result['winning_team_id'];
			$winning_team = $this->template_repo->get_team($winning_team_id);
			$picks[] = new Wp_Bracket_Builder_Match_Pick(
				$result['round_index'],
				$result['match_index'],
				$winning_team_id,
				$result['id'],
				$winning_team,
			);
		}
		return $picks;
	}

	public function insert_match_picks(int $id, array $picks): void {
		$table_name = $this->match_pick_table();
		foreach ($picks as $pick) {
			$this->wpdb->insert(
				$table_name,
				[
					'bracket_play_id' => $id,
					'round_index' => $pick->round_index,
					'match_index' => $pick->match_index,
					'winning_team_id' => $pick->winning_team_id,
				]
			);
		}
	}

	public function update_match_picks(int $id, array $picks): void {
		// TODO: implement this method
		// For each pick:
		// - find pick with same round_index and match_index for this bracket_play_id
		// - if it exists, update it
		// - if it doesn't exist, insert it
	}

	private function match_pick_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_match_picks';
	}
}
