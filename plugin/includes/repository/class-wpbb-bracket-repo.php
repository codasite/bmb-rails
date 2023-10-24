<?php

require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-bracket.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-match.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-team.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-bracket-team-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wpbb-utils.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'service/class-wpbb-notification-service.php';

class Wpbb_BracketRepo extends Wpbb_CustomPostRepoBase {
  /**
   * @var Wpbb_BracketTeamRepo
   */
  private $team_repo;

  /**
   * @var wpdb
   */
  private $wpdb;

  /**
   * @var Wpbb_Notification_Service
   */
  private $notification_service;

  public function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->team_repo = new Wpbb_BracketTeamRepo();
    $this->notification_service = new Wpbb_Notification_Service();
    parent::__construct();
  }

  public function add(Wpbb_Bracket $bracket): ?Wpbb_Bracket {
    $post_id = $this->insert_post($bracket, true, true);

    if (is_wp_error($post_id)) {
      return null;
    }

    $bracket_id = $this->insert_bracket_data([
      'post_id' => $post_id,
    ]);

    if ($bracket->matches) {
      $this->insert_matches($bracket_id, $bracket->matches);
    }

    if ($bracket->results) {
      $this->insert_results($bracket_id, $bracket->results);
    }

    # refresh from db
    $bracket = $this->get($post_id);
    return $bracket;
  }

  public function insert_bracket_data(array $data): int {
    $table_name = $this->brackets_table();
    $this->wpdb->insert($table_name, $data);
    return $this->wpdb->insert_id;
  }

  public function insert_matches(int $bracket_id, array $matches): void {
    $table_name = $this->match_table();
    foreach ($matches as $match) {
      // Skip if match is null
      if ($match === null) {
        continue;
      }
      // First, insert teams
      $team1 = $this->team_repo->add($bracket_id, $match->team1);
      $team2 = $this->team_repo->add($bracket_id, $match->team2);

      $this->wpdb->insert($table_name, [
        'bracket_id' => $bracket_id,
        'round_index' => $match->round_index,
        'match_index' => $match->match_index,
        'team1_id' => $team1?->id,
        'team2_id' => $team2?->id,
      ]);
      $match->id = $this->wpdb->insert_id;
    }
  }

  public function insert_results(int $bracket_id, array $results): void {
    foreach ($results as $result) {
      $this->insert_result($bracket_id, $result);
    }
  }

  public function insert_result(int $bracket_id, Wpbb_MatchPick $pick): void {
    $table_name = $this->results_table();
    $this->wpdb->insert($table_name, [
      'bracket_id' => $bracket_id,
      'round_index' => $pick->round_index,
      'match_index' => $pick->match_index,
      'winning_team_id' => $pick->winning_team_id,
    ]);
  }

  public function get(
    int|WP_Post|Wpbb_Bracket|null $post = null,
    bool $fetch_matches = true,
    bool $fetch_results = true
  ): ?Wpbb_Bracket {
    if ($post instanceof Wpbb_Bracket) {
      $post = $post->id;
    }

    $bracket_post = get_post($post);

    if (
      !$bracket_post ||
      $bracket_post->post_type !== Wpbb_Bracket::get_post_type()
    ) {
      return null;
    }

    $bracket_data = $this->get_bracket_data($bracket_post);
    if (!isset($bracket_data['id'])) {
      return null;
    }
    $bracket_id = $bracket_data['id'];

    $matches =
      $fetch_matches && $bracket_id ? $this->get_matches($bracket_id) : [];

    $results =
      $fetch_results && $bracket_id ? $this->get_results($bracket_id) : [];

    $author_id = (int) $bracket_post->post_author;

    $data = [
      'id' => $bracket_post->ID,
      'title' => $bracket_post->post_title,
      'author' => $author_id,
      'status' => $bracket_post->post_status,
      'month' => get_post_meta($bracket_post->ID, 'month', true),
      'year' => get_post_meta($bracket_post->ID, 'year', true),
      'num_teams' => get_post_meta($bracket_post->ID, 'num_teams', true),
      'wildcard_placement' => get_post_meta(
        $bracket_post->ID,
        'wildcard_placement',
        true
      ),
      'published_date' => get_post_datetime($bracket_post->ID, 'date', 'gmt'),
      'matches' => $matches,
      'results' => $results,
      'slug' => $bracket_post->post_name,
      'author_display_name' => $author_id
        ? get_the_author_meta('display_name', $author_id)
        : '',
    ];

    return new Wpbb_Bracket($data);
  }

  public function update(
    Wpbb_Bracket|int|null $bracket,
    array|null $data = null
  ): ?Wpbb_Bracket {
    if (!$bracket || !$data) {
      return null;
    }
    if (!($bracket instanceof Wpbb_Bracket)) {
      $bracket = $this->get($bracket);
    }
    $array = $bracket->to_array();
    $updated_array = array_merge($array, $data);

    $bracket = Wpbb_Bracket::from_array($updated_array);

    $post_id = $this->update_post($bracket);

    if (is_wp_error($post_id)) {
      return null;
    }

    $bracket_data = $this->get_bracket_data($post_id);
    $bracket_id = $bracket_data['id'];

    if ($bracket_id && $bracket->results) {
      $this->update_results($bracket_id, $bracket->results);
    }

    $this->notification_service->notify_bracket_updated($bracket_id);

    # refresh from db
    $bracket = $this->get($post_id);
    return $bracket;
  }

  public function update_results(
    int $bracket_id,
    array|null $new_results
  ): void {
    if ($new_results === null) {
      return;
    }

    $old_results = $this->get_results($bracket_id);

    if (empty($old_results)) {
      $this->insert_results($bracket_id, $new_results);
      return;
    }

    foreach ($new_results as $new_result) {
      $pick_exists = false;
      foreach ($old_results as $old_result) {
        if (
          $new_result->round_index === $old_result->round_index &&
          $new_result->match_index === $old_result->match_index
        ) {
          $pick_exists = true;
          $this->wpdb->update(
            $this->results_table(),
            [
              'winning_team_id' => $new_result->winning_team_id,
            ],
            [
              'id' => $old_result->id,
            ]
          );
        }
      }
      if (!$pick_exists) {
        $this->insert_result($bracket_id, $new_result);
      }
    }
  }

  public function get_bracket_data(int|WP_Post|null $bracket_post): array {
    if (
      !$bracket_post ||
      ($bracket_post instanceof WP_Post &&
        $bracket_post->post_type !== Wpbb_Bracket::get_post_type())
    ) {
      return [];
    }

    if ($bracket_post instanceof WP_Post) {
      $bracket_post = $bracket_post->ID;
    }

    $table_name = $this->brackets_table();
    $bracket_data = $this->wpdb->get_row(
      $this->wpdb->prepare(
        "SELECT * FROM $table_name WHERE post_id = %d",
        $bracket_post
      ),
      ARRAY_A
    );

    if (!$bracket_data) {
      return [];
    }

    return $bracket_data;
  }

  public function get_matches(int $bracket_id): array {
    $table_name = $this->match_table();
    $where = $bracket_id ? "WHERE bracket_id = $bracket_id" : '';
    $match_results = $this->wpdb->get_results(
      "SELECT * FROM {$table_name} $where ORDER BY round_index, match_index ASC",
      ARRAY_A
    );
    $matches = [];
    foreach ($match_results as $match) {
      $team1 = $this->team_repo->get($match['team1_id']);
      $team2 = $this->team_repo->get($match['team2_id']);

      $matches[] = new Wpbb_Match([
        'round_index' => $match['round_index'],
        'match_index' => $match['match_index'],
        'team1' => $team1,
        'team2' => $team2,
        'id' => $match['id'],
      ]);
    }

    return $matches;
  }

  public function get_results(int|null $bracket_id): array {
    $table_name = $this->results_table();
    $where = $bracket_id ? "WHERE bracket_id = $bracket_id" : '';
    $sql = "SELECT * FROM $table_name $where ORDER BY round_index, match_index ASC";
    $data = $this->wpdb->get_results($sql, ARRAY_A);

    $bracket_results = [];
    foreach ($data as $result) {
      $winning_team_id = $result['winning_team_id'];
      $winning_team = $this->team_repo->get($winning_team_id);
      $bracket_results[] = new Wpbb_MatchPick([
        'round_index' => $result['round_index'],
        'match_index' => $result['match_index'],
        'winning_team_id' => $winning_team_id,
        'id' => $result['id'],
        'winning_team' => $winning_team,
      ]);
    }
    return $bracket_results;
  }

  public function get_all(array|WP_Query $query = []): array {
    if ($query instanceof WP_Query) {
      return $this->brackets_from_query($query);
    }

    $default_args = [
      'post_type' => Wpbb_Bracket::get_post_type(),
      'post_status' => 'any',
    ];

    $args = array_merge($default_args, $query);
    $query = new WP_Query($args);

    return $this->brackets_from_query($query);
  }

  public function brackets_from_query(WP_Query $query): array {
    $brackets = [];
    foreach ($query->posts as $post) {
      $bracket = $this->get($post, false);
      if ($bracket) {
        $brackets[] = $bracket;
      }
    }
    return $brackets;
  }

  public function get_user_info_and_last_round_pick(
    $bracket_id,
    $final_round_pick
  ) {
    global $wpdb;

    /**
     * @var $query string
     * Sorts picks for bracket by round index and
     * returns the author's email, display name, the
     * winning pick and the winning result.
     */

    $query = "
        SELECT author.user_email as email, author.display_name as name, pick.winning_team_id as winning_team_id
        FROM wp_bracket_builder_plays play
        JOIN wp_bracket_builder_match_picks pick 
        ON pick.bracket_play_id = play.id
        AND pick.round_index = %d
        AND pick.match_index = %d
        JOIN wp_posts post
        ON post.ID = play.post_id
        JOIN wp_users author
        ON author.ID = post.post_author
        WHERE play.bracket_post_id = %d
        GROUP BY post.post_author;
        ";

    $prepared_query = $wpdb->prepare(
      $query,
      $final_round_pick->round_index,
      $final_round_pick->match_index,
      $bracket_id
    );
    $results = $wpdb->get_results($prepared_query);
    return $results;
  }

  public function delete(int $id, $force = false): bool {
    return $this->delete_post($id, $force);
  }

  public function results_table(): string {
    return $this->wpdb->prefix . 'bracket_builder_bracket_results';
  }

  public function match_table(): string {
    return $this->wpdb->prefix . 'bracket_builder_matches';
  }

  public function brackets_table(): string {
    return $this->wpdb->prefix . 'bracket_builder_brackets';
  }
}
