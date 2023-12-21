<?php
namespace WStrategies\BMB\Includes\Service\CustomQuery;

use WStrategies\BMB\Includes\Repository\BracketPlayRepo;

class CustomPlayQuery {
  private $play_repo;

  private $wpdb;

  public function __construct($opts = []) {
    global $wpdb;
    $this->play_repo = $opts['play_repo'] ?? new BracketPlayRepo();
    $this->wpdb = $opts['wpdb'] ?? $wpdb;
  }

  public static $sort_fields = ['total_score', 'accuracy_score'];
  public static $filter_fields = [
    'bracket_id',
    'bracket_post_id',
    'is_printed',
  ];
  // This is a mapping of query fields to the actual field names in the database
  public static $alternate_field_mappings = [
    'bracket_id' => 'bracket_post_id',
  ];

  private static $plays_alias = 'plays';

  public function handle_custom_query($clauses, $query_object) {
    if (!$query_object->get('post_type') === 'bracket_play') {
      return $clauses;
    }
    if (
      !empty(
        $this->key_value_intersect($query_object->query, self::$filter_fields)
      )
    ) {
      $clauses = $this->filter($clauses, $query_object);
    }
    if (in_array($query_object->get('orderby'), self::$sort_fields)) {
      $clauses = $this->sort($clauses, $query_object);
    }
    return $clauses;
  }

  private function key_value_intersect(array $keys, array $values) {
    return array_intersect_key($keys, array_flip($values));
  }

  public function filter($clauses, $query_object) {
    if (
      $query_object->get('post_type') !== 'bracket_play' ||
      empty(
        $this->key_value_intersect($query_object->query, self::$filter_fields)
      )
    ) {
      return $clauses;
    }

    $this->map_query_fields($query_object->query);
    $this->add_join($clauses);
    $where = &$clauses['where'];
    $this->init_clause($where);
    $alias = self::$plays_alias;
    foreach (
      $this->key_value_intersect($query_object->query, self::$filter_fields)
      as $key => $value
    ) {
      $where .= " AND $alias.$key = {$query_object->query[$key]}";
    }

    return $clauses;
  }

  /**
   * Sort plays by a field in the plays table
   *
   * adapted from: https://wordpress.stackexchange.com/questions/50305/how-to-extend-wp-query-to-include-custom-table-in-query
   */
  public function sort($clauses, $query_object) {
    if (
      $query_object->get('post_type') !== 'bracket_play' ||
      !in_array($query_object->get('orderby'), self::$sort_fields)
    ) {
      return $clauses;
    }
    $this->add_join($clauses);

    $orderby = &$clauses['orderby'];
    $orderby = "plays.{$query_object->get('orderby')} {$query_object->get(
      'order'
    )}";
    return $clauses;
  }

  private function add_join(&$clauses) {
    $join = &$clauses['join'];
    $this->init_clause($join);
    // check if plays table has been joined
    if (strpos($join, 'plays') === false) {
      $join .= $this->join_plays_table();
    }
  }

  private function init_clause(&$clause) {
    if (!empty($clause)) {
      $clause .= ' ';
    } // Add space only if we need to
  }

  private function join_plays_table() {
    $plays_alias = self::$plays_alias;
    return "JOIN {$this->play_repo->table_name()} $plays_alias ON plays.post_id = {$this->wpdb->posts}.ID";
  }

  private function map_query_fields(&$query) {
    foreach ($query as $key => $value) {
      if (array_key_exists($key, self::$alternate_field_mappings)) {
        $query[self::$alternate_field_mappings[$key]] = $value;
        unset($query[$key]);
      }
    }
  }
}
