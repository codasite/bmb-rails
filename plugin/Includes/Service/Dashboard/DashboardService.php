<?php

namespace WStrategies\BMB\Includes\Service\Dashboard;

use WP_Query;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\NotificationRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;

class DashboardService {
  private BracketRepo $bracket_repo;
  private \wpdb $wpdb;
  public static $bracket_status_mapping = [
    'live' => ['publish'],
    'private' => ['private'],
    'upcoming' => ['upcoming'],
    'closed' => ['score', 'complete'],
  ];

  public function __construct($args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    global $wpdb;
    $this->wpdb = $args['wpdb'] ?? $wpdb;
  }

  /**
   * Get all brackets hosted by the current user
   */
  public function get_hosted_brackets(int $paged, string $status) {
    $post_status = self::$bracket_status_mapping[$status];

    $the_query = new WP_Query([
      'post_type' => Bracket::get_post_type(),
      'author' => get_current_user_id(),
      'posts_per_page' => 6,
      'paged' => $paged,
      'post_status' => $post_status,
    ]);

    $brackets = $this->bracket_repo->get_all($the_query);
    return [
      'brackets' => $brackets,
      'max_num_pages' => $the_query->max_num_pages,
    ];
  }

  public function get_tournaments(int $paged, int $per_page, string $status) {
    $post_status = self::$bracket_status_mapping[$status];
    $offset = ($paged - 1) * $per_page;
    $user_id = get_current_user_id();
    $bracket_table = BracketRepo::table_name();
    $play_table = PlayRepo::table_name();
    $notification_table = NotificationRepo::table_name();
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
    $results = $this->wpdb->get_results($sql, ARRAY_A);
    $sql_arr = explode("\n", trim($sql));
    // remove last two lines
    $sql_arr = array_slice($sql_arr, 0, -2);
    $count_sql = implode("\n", $sql_arr);
    $count_sql = "SELECT COUNT(*) FROM ($count_sql) AS count";
    $count = $this->wpdb->get_var($count_sql);
    $max_num_pages = ceil((int) $count / $per_page);
    $brackets = [];
    foreach ($results as $result) {
      $bracket = $this->bracket_repo->get($result['id'], false);
      if ($bracket) {
        $brackets[] = $bracket;
      }
    }
    return [
      'brackets' => $brackets,
      'max_num_pages' => $max_num_pages,
    ];
  }
}
