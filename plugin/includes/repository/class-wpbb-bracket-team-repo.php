<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-team.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wpbb-utils.php';

/**
 * Repository for Matches, Match Picks, and Teams
 */
class Wpbb_BracketTeamRepo
{
	/**
	 * @var Wpbb_Utils
	 */
	private $utils;

	/**
	 * @var wpdb
	 */
	private $wpdb;


	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->utils = new Wpbb_Utils();
	}

	/**
	 * TEAMS
	 */

	public function get_team(int|null $id): ?Wpbb_Team {
		if ($id === null) {
			return null;
		}

		$table_name = $this->team_table();
		$team = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE id = %d",
				$id
			),
			ARRAY_A
		);
		return new Wpbb_Team($team['name'], $team['id']);
	}

	public function get_teams(): array {
		$table_name = $this->team_table();
		$team_results = $this->wpdb->get_results(
			"SELECT * FROM {$table_name}",
			ARRAY_A
		);
		$teams = [];
		foreach ($team_results as $team) {
			$teams[] = new Wpbb_Team($team['name'], $team['id']);
		}
		return $teams;
	}

	public function insert_team(int $post_id, ?Wpbb_Team $team): ?Wpbb_Team {
		if (empty($team)) {
			return $team;
		}
		$table_name = $this->team_table();
		$this->wpdb->insert(
			$table_name,
			[
				'name' => $team->name,
				'bracket_template_id' => $post_id,
			]
		);
		$team->id = $this->wpdb->insert_id;
		return $team;
	}

	public function team_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_teams';
	}
}
