<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wp-bracket-builder-bracket-play-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

/**
 * Repository for Matches, Match Picks, and Teams
 */
class Wp_Bracket_Builder_Bracket_Match_Repository {
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

	/**
	 * MATCHES
	 */

	public function get_matches(int|null $post_id): array {
		$table_name = $this->match_table();
		$where = $post_id ? "WHERE bracket_template_id = $post_id" : '';
		$match_results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$table_name} $where ORDER BY round_index, match_index ASC",
				$post_id
			),
			ARRAY_A
		);
		$matches = [];
		foreach ($match_results as $match) {
			$team1 = $this->get_team($match['team1_id']);
			$team2 = $this->get_team($match['team2_id']);

			// $matches[$match['round_index']][$match['match_index']] = new Wp_Bracket_Builder_Match(
			$matches[] = new Wp_Bracket_Builder_Match(
				$match['round_index'],
				$match['match_index'],
				$team1,
				$team2,
				$match['id'],
			);
		}

		return $matches;
	}

	public function insert_matches(int $post_id, array $matches): void {
		$table_name = $this->match_table();
		foreach ($matches as $match) {
			// Skip if match is null
			if ($match === null) {
				continue;
			}
			// First, insert teams
			$team1 = $this->insert_team($post_id, $match->team1);
			$team2 = $this->insert_team($post_id, $match->team2);

			$this->wpdb->insert(
				$table_name,
				[
					'bracket_template_id' => $post_id,
					'round_index' => $match->round_index,
					'match_index' => $match->match_index,
					'team1_id' => $team1->id,
					'team2_id' => $team2->id,
				]
			);
			$match->id = $this->wpdb->insert_id;
		}
	}

	/**
	 * MATCH PICKS
	 */

	public function get_picks(int|null $post_id): array {
		$table_name = $this->match_pick_table();
		$where = $post_id ? "WHERE bracket_play_id = $post_id" : '';
		$sql = "SELECT * FROM $table_name $where ORDER BY round_index, match_index ASC";
		$results = $this->wpdb->get_results($sql, ARRAY_A);

		$picks = [];
		foreach ($results as $result) {
			$winning_team_id = $result['winning_team_id'];
			$winning_team = $this->get_team($winning_team_id);
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

	public function insert_picks(int $post_id, array $picks): void {
		foreach ($picks as $pick) {
			$this->insert_pick($post_id, $pick);
		}
	}

	public function insert_pick(int $post_id, Wp_Bracket_Builder_Match_Pick $pick): void {
		$table_name = $this->match_pick_table();
		$this->wpdb->insert(
			$table_name,
			[
				'bracket_play_id' => $post_id,
				'round_index' => $pick->round_index,
				'match_index' => $pick->match_index,
				'winning_team_id' => $pick->winning_team_id,
			]
		);
	}

	public function update_picks(int $post_id, array|null $new_picks): void {
		if ($new_picks === null) {
			return;
		}

		$old_picks = $this->get_picks($post_id);

		if (empty($old_picks)) {
			$this->insert_picks($post_id, $new_picks);
			return;
		}

		foreach ($new_picks as $new_pick) {
			$pick_exists = false;
			foreach ($old_picks as $old_pick) {
				if ($new_pick->round_index === $old_pick->round_index && $new_pick->match_index === $old_pick->match_index) {
					$pick_exists = true;
					$this->wpdb->update(
						$this->match_pick_table(),
						[
							'winning_team_id' => $new_pick->winning_team_id,
						],
						[
							'id' => $old_pick->id,
						]
					);
				}
			}
			if (!$pick_exists) {
				$this->insert_picks($post_id, [$new_pick]);
			}
		}
	}

	/**
	 * TEAMS
	 */

	public function get_team(int|null $id): ?Wp_Bracket_Builder_Team {
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
		return new Wp_Bracket_Builder_Team($team['name'], $team['id']);
	}

	public function get_teams(): array {
		$table_name = $this->team_table();
		$team_results = $this->wpdb->get_results(
			"SELECT * FROM {$table_name}",
			ARRAY_A
		);
		$teams = [];
		foreach ($team_results as $team) {
			$teams[] = new Wp_Bracket_Builder_Team($team['name'], $team['id']);
		}
		return $teams;
	}

	public function insert_team(int $post_id, ?Wp_Bracket_Builder_Team $team): ?Wp_Bracket_Builder_Team {
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

	public function match_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_matches';
	}

	public function match_pick_table(): string {
		return $this->wpdb->prefix . 'bracket_builder_match_picks';
	}
}
