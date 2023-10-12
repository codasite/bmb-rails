<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-match.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-team.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-bracket-team-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'service/class-wpbb-bracket-play-service.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wpbb-utils.php';

/**
 * Repository for Matches, Match Picks, and Teams
 */
class Wpbb_BracketMatchRepo {
  /**
   * @var Wpbb_Utils
   */
  private $utils;

  /**
   * @var wpdb
   */
  private $wpdb;

  /**
   * @var Wpbb_BracketTeamRepo
   */
  private $team_repo;

  public function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->utils = new Wpbb_Utils();
    $this->team_repo = new Wpbb_BracketTeamRepo();
  }

  /**
   * MATCHES
   */

  public function get_match(int $id): ?Wpbb_Match {
    $table_name = $this->match_table();
    $match = $this->wpdb->get_row(
      $this->wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
      ARRAY_A
    );
    if (!$match) {
      return null;
    }

    $team1 = $this->get_team($match['team1_id']);
    $team2 = $this->get_team($match['team2_id']);

    return new Wpbb_Match([
      $match['round_index'],
      $match['match_index'],
      $team1,
      $team2,
      $match['id'],
    ]);
  }

  public function get_matches(int|null $template_id): array {
    $table_name = $this->match_table();
    $where = $template_id ? "WHERE bracket_template_id = $template_id" : '';
    $match_results = $this->wpdb->get_results(
      $this->wpdb->prepare(
        "SELECT * FROM {$table_name} $where ORDER BY round_index, match_index ASC",
        $template_id
      ),
      ARRAY_A
    );
    $matches = [];
    foreach ($match_results as $match) {
      $team1 = $this->get_team($match['team1_id']);
      $team2 = $this->get_team($match['team2_id']);

      $matches[] = new Wpbb_Match([
        $match['round_index'],
        $match['match_index'],
        $team1,
        $team2,
        $match['id'],
      ]);
    }

    return $matches;
  }

  public function insert_match(int $template_id, Wpbb_Match $match) {
    $table_name = $this->match_table();
    // First, insert teams
    $team1 = $this->insert_team($template_id, $match->team1);
    $team2 = $this->insert_team($template_id, $match->team2);

    $this->wpdb->insert($table_name, [
      'bracket_template_id' => $template_id,
      'round_index' => $match->round_index,
      'match_index' => $match->match_index,
      'team1_id' => $team1->id,
      'team2_id' => $team2->id,
    ]);
    $match->id = $this->wpdb->insert_id;
  }

  public function insert_matches(int $template_id, array $matches): array {
    foreach ($matches as $match) {
      // Skip if match is null
      if ($match === null) {
        continue;
      }
      $this->insert_match($template_id, $match);
    }
    return $this->get_matches($template_id);
  }

  /**
   * MATCH PICKS
   */

  public function get_picks(int|null $play_id): array {
    $table_name = $this->match_pick_table();
    $where = $play_id ? "WHERE bracket_play_id = $play_id" : '';
    $sql = "SELECT * FROM $table_name $where ORDER BY round_index, match_index ASC";
    $results = $this->wpdb->get_results($sql, ARRAY_A);

    $picks = [];
    foreach ($results as $result) {
      $winning_team_id = $result['winning_team_id'];
      $winning_team = $this->get_team($winning_team_id);
      $picks[] = new Wpbb_MatchPick(
        $result['round_index'],
        $result['match_index'],
        $winning_team_id,
        $result['id'],
        $winning_team
      );
    }
    return $picks;
  }

  public function insert_picks(int $play_id, array $picks): void {
    foreach ($picks as $pick) {
      $this->insert_pick($play_id, $pick);
    }
  }

  public function insert_pick(int $play_id, Wpbb_MatchPick $pick): void {
    $table_name = $this->match_pick_table();
    $this->wpdb->insert($table_name, [
      'bracket_play_id' => $play_id,
      'round_index' => $pick->round_index,
      'match_index' => $pick->match_index,
      'winning_team_id' => $pick->winning_team_id,
    ]);
  }

  public function update_picks(int $play_id, array|null $new_picks): void {
    if ($new_picks === null) {
      return;
    }

    $old_picks = $this->get_picks($play_id);

    if (empty($old_picks)) {
      $this->insert_picks($play_id, $new_picks);
      return;
    }

    foreach ($new_picks as $new_pick) {
      $pick_exists = false;
      foreach ($old_picks as $old_pick) {
        if (
          $new_pick->round_index === $old_pick->round_index &&
          $new_pick->match_index === $old_pick->match_index
        ) {
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
        $this->insert_picks($play_id, [$new_pick]);
      }
    }
  }

  /**
   * TEAMS
   */

  public function get_team(int|null $id): ?Wpbb_Team {
    return $this->team_repo->get($id);
  }

  public function insert_team(int $template_id, ?Wpbb_Team $team): ?Wpbb_Team {
    return $this->team_repo->add($template_id, $team);
  }

  public function match_table(): string {
    return $this->wpdb->prefix . 'bracket_builder_matches';
  }

  public function match_pick_table(): string {
    return $this->wpdb->prefix . 'bracket_builder_match_picks';
  }
}
