<?php
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-play-repo.php';

class Wpbb_CustomPlayQuery {
  private $play_repo;

  private $wpdb;

  public function __construct($opts = []) {
    global $wpdb;
    $this->play_repo = $opts['play_repo'] ?? new Wpbb_BracketPlayRepo();
    $this->wpdb = $opts['wpdb'] ?? $wpdb;
  }

  public static $sort_fields = ['total_score', 'accuracy_score'];

  public function handle_custom_query($clauses, $query_object) {
    if (!$query_object->get('post_type') === 'bracket_play') {
      return $clauses;
    }
    if (in_array($query_object->get('orderby'), self::$sort_fields)) {
      $clauses = $this->sort($clauses, $query_object);
    }
    return $clauses;
  }

  /**
   * Sort plays by a field in the plays table
   *
   * adapted from: https://wordpress.stackexchange.com/questions/4852/post-meta-vs-separate-database-tables
   */
  public function sort($clauses, $query_object) {
    if (
      $query_object->get('post_type') !== 'bracket_play' ||
      !in_array($query_object->get('orderby'), self::$sort_fields)
    ) {
      return $clauses;
    }
    $join = &$clauses['join'];
    if (!empty($join)) {
      $join .= ' ';
    } // Add space only if we need to
    $join .= "JOIN {$this->play_repo->plays_table()} plays ON plays.post_id = {$this->wpdb->posts}.ID";

    $orderby = &$clauses['orderby'];
    $orderby = "plays.{$query_object->get('orderby')} {$query_object->get(
      'order'
    )}";
    return $clauses;
  }
}
