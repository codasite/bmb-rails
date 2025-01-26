<?php

namespace WStrategies\BMB\Includes\Service\TournamentFilter\Dashboard;

use WP_Query;
use WStrategies\BMB\Features\Notifications\NotificationSubscriptionRepo;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;

class DashboardTournamentsQuery {
  private BracketRepo $bracket_repo;
  private \wpdb $wpdb;
  public static $tournament_roles = ['hosting', 'playing'];
  public static $paged_status_mapping = [
    'all' => ['publish', 'private', 'upcoming', 'score', 'complete'],
    'live' => ['publish', 'score'],
    'private' => ['private'],
    'upcoming' => ['upcoming'],
    'complete' => ['complete'],
  ];
  private array $tournament_counts = [];

  public function __construct($args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    global $wpdb;
    $this->wpdb = $args['wpdb'] ?? $wpdb;
  }

  public function tournament_role_is_valid(string $role) {
    return in_array($role, self::$tournament_roles);
  }

  public function paged_status_is_valid(string $status) {
    return array_key_exists($status, self::$paged_status_mapping);
  }

  public function get_tournaments(
    int $paged,
    int $per_page,
    string $status,
    string $role
  ): array {
    if (
      !$this->paged_status_is_valid($status) ||
      !$this->tournament_role_is_valid($role)
    ) {
      return [];
    }
    $hosting = $role === 'hosting';
    if ($hosting) {
      return $this->get_hosted_tournaments($paged, $per_page, $status);
    }
    return $this->get_played_tournaments($paged, $per_page, $status);
  }

  /**
   * Tournament counts are cached in this class
   */
  public function get_tournaments_count(string $status, string $role) {
    if (
      !$this->paged_status_is_valid($status) ||
      !$this->tournament_role_is_valid($role)
    ) {
      return 0;
    }

    $hosting = $role === 'hosting';

    if (isset($this->tournament_counts[$hosting][$status])) {
      return $this->tournament_counts[$hosting][$status];
    }
    if ($hosting) {
      $count = $this->get_hosted_tournaments_count($status);
    } else {
      $count = $this->get_played_tournaments_count($status);
    }
    $this->tournament_counts[$hosting][$status] = $count;
    return $count;
  }

  public function get_max_num_pages(
    int $per_page,
    string $status,
    string $role
  ) {
    $count = $this->get_tournaments_count($status, $role);
    return ceil($count / $per_page);
  }

  public function has_tournaments(string $status, string $role) {
    $count = $this->get_tournaments_count($status, $role);
    return $count > 0;
  }

  /**
   * Get all brackets hosted by the current user
   */
  private function get_hosted_tournaments(
    int $paged,
    int $per_page,
    string $status
  ) {
    $query = $this->get_hosted_tournaments_query($status, $paged, $per_page);

    return $this->bracket_repo->get_all($query);
  }

  private function get_hosted_tournaments_count(string $status) {
    $query = $this->get_hosted_tournaments_query($status);
    return $query->found_posts;
  }

  private function get_hosted_tournaments_query(
    string $status,
    int $paged = 0,
    int $per_page = 0
  ) {
    $post_status = self::$paged_status_mapping[$status];
    $query_args = [
      'post_type' => Bracket::get_post_type(),
      'author' => get_current_user_id(),
      'post_status' => $post_status,
    ];
    if ($paged) {
      $query_args['paged'] = $paged;
    }
    if ($per_page) {
      $query_args['posts_per_page'] = $per_page;
    }

    $the_query = new WP_Query($query_args);

    return $the_query;
  }

  private function get_played_tournaments(
    int $paged,
    int $per_page,
    string $status
  ) {
    $results = $this->wpdb->get_results(
      $this->get_played_tournaments_sql($paged, $per_page, $status),
      ARRAY_A
    );
    $brackets = [];
    foreach ($results as $result) {
      $bracket = $this->bracket_repo->get($result['id'], false);
      if ($bracket) {
        $brackets[] = $bracket;
      }
    }
    return $brackets;
  }

  private function get_played_tournaments_count(string $status) {
    $sql = $this->get_played_tournaments_sql(1, 0, $status);
    $sql_arr = explode("\n", trim($sql));
    // remove last two lines
    $sql_arr = array_slice($sql_arr, 0, -2);
    $count_sql = implode("\n", $sql_arr);
    $count_sql = "SELECT COUNT(*) FROM ($count_sql) AS count";
    $count = $this->wpdb->get_var($count_sql);
    // $max_num_pages = ceil((int) $count / $per_page);
    return (int) $count;
  }

  private function get_played_tournaments_sql(
    int $paged,
    int $per_page,
    string $status
  ) {
    $post_status = self::$paged_status_mapping[$status];
    $offset = ($paged - 1) * $per_page;
    $user_id = get_current_user_id();
    $bracket_table = BracketRepo::table_name();
    $play_table = PlayRepo::table_name();
    $notification_table = NotificationSubscriptionRepo::table_name();
    $post_status_placeholders = implode(
      ',',
      array_fill(0, count($post_status), '%s')
    );
    $sql = $this->wpdb->prepare(
      "
SELECT bracket_post.id
FROM $bracket_table AS bracket
JOIN {$this->wpdb->prefix}posts AS bracket_post ON bracket.post_id = bracket_post.id
LEFT JOIN $play_table AS play ON play.bracket_id = bracket.id
LEFT JOIN {$this->wpdb->prefix}posts AS play_post ON play.post_id = play_post.id
LEFT JOIN $notification_table AS notification ON bracket_post.id = notification.post_id
WHERE (play_post.post_author = %d OR notification.user_id = %d)
  AND bracket_post.post_status IN ($post_status_placeholders)
GROUP BY bracket.id
ORDER BY bracket_post.post_date DESC
LIMIT %d OFFSET %d
",
      ...[$user_id, $user_id, ...$post_status, $per_page, $offset]
    );

    return $sql;
  }
}
