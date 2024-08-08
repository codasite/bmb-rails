<?php
namespace WStrategies\BMB\Includes\Repository;

use DateTimeImmutable;
use WP_Post;
use WP_Query;
use wpdb;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Service\BracketLeaderboardService;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class BracketRepo extends CustomPostRepoBase implements CustomTableInterface {
  private TeamRepo $team_repo;
  private BracketMatchRepo $match_repo;
  private BracketResultsRepo $results_repo;
  private PickRepo $pick_repo;
  private wpdb $wpdb;
  private BracketLeaderboardService $leaderboard_service;
  private BracketProductUtils $bracket_product_utils;

  public function __construct($args = []) {
    global $wpdb;
    $this->wpdb = $args['wpdb'] ?? $wpdb;
    $this->team_repo = $args['team_repo'] ?? new TeamRepo();
    $this->match_repo =
      $args['match_repo'] ?? new BracketMatchRepo($this->team_repo);
    $this->results_repo =
      $args['results_repo'] ?? new BracketResultsRepo($this, $this->team_repo);
    $this->pick_repo = $args['pick_repo'] ?? new PickRepo($this->team_repo);
    $this->leaderboard_service =
      $args['leaderboard_service'] ??
      new BracketLeaderboardService(null, [
        'bracket_repo' => $this,
        'play_repo' => new PlayRepo(['bracket_repo' => $this]),
      ]);
    $this->bracket_product_utils =
      $args['bracket_product_utils'] ??
      new BracketProductUtils([
        'bracket_repo' => $this,
      ]);
    parent::__construct();
  }

  public function add(Bracket $bracket): ?Bracket {
    $post_id = $this->insert_post($bracket, true, true);

    if (is_wp_error($post_id)) {
      throw new \Exception($post_id->get_error_message());
    }

    $bracket_id = $this->insert_custom_table_data([
      'post_id' => $post_id,
    ]);

    if ($bracket->matches) {
      $this->match_repo->insert_matches($bracket_id, $bracket->matches);
    }

    if ($bracket->results) {
      $this->results_repo->insert_results($bracket_id, $bracket->results);
    }

    # refresh from db
    $bracket = $this->get($post_id);
    return $bracket;
  }

  public function insert_custom_table_data(array $data): int {
    $table_name = self::table_name();
    $this->wpdb->insert($table_name, $data);
    return $this->wpdb->insert_id;
  }

  public function get(
    int|WP_Post|Bracket|null $post = null,
    bool $fetch_matches = true,
    bool $fetch_results = true,
    bool $fetch_most_popular_picks = false
  ): ?Bracket {
    if ($post instanceof Bracket) {
      $post = $post->id;
    }

    $bracket_post = get_post($post);
    assert($bracket_post instanceof WP_Post || $bracket_post === null);

    if (
      !$bracket_post ||
      $bracket_post->post_type !== Bracket::get_post_type()
    ) {
      return null;
    }

    $bracket_data = $this->get_custom_table_data($bracket_post->ID);
    $bracket_id = $bracket_data['id'] ?? null;
    if (!$bracket_id) {
      return null;
    }
    $results_updated = isset($bracket_data['results_first_updated_at'])
      ? new DateTimeImmutable($bracket_data['results_first_updated_at'])
      : null;

    $matches = $fetch_matches
      ? $this->match_repo->get_matches($bracket_id)
      : [];

    $results = $fetch_results
      ? $this->results_repo->get_results($bracket_id)
      : [];

    $most_popular_picks = $fetch_most_popular_picks
      ? $this->pick_repo->get_most_popular_picks($bracket_id)
      : [];

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
      'most_popular_picks' => $most_popular_picks,
      'slug' => $bracket_post->post_name,
      'author_display_name' => $author_id
        ? get_the_author_meta('display_name', $author_id)
        : '',
      'results_first_updated_at' => $results_updated,
      'thumbnail_url' => get_the_post_thumbnail_url($bracket_post->ID),
      'url' => get_permalink($bracket_post->ID),
      'num_plays' => $this->leaderboard_service->get_num_plays([
        'bracket_id' => $bracket_post->ID,
      ]),
      'fee' => $this->bracket_product_utils->get_bracket_fee($bracket_post->ID),
      'should_notify_results_updated' => get_post_meta(
        $bracket_post->ID,
        'should_notify_results_updated',
        true
      ),
    ];

    return new Bracket($data);
  }

  public function get_custom_table_data(
    int|null $id,
    $use_post_id = true
  ): array {
    $id_field = $use_post_id ? 'post_id' : 'id';
    $table_name = self::table_name();
    $bracket_data = $this->wpdb->get_row(
      $this->wpdb->prepare(
        "SELECT * FROM $table_name WHERE $id_field = %d",
        $id
      ),
      ARRAY_A
    );

    if (!$bracket_data) {
      return [];
    }

    return $bracket_data;
  }

  public function get_all(array|WP_Query $query = [], array $args = []): array {
    $fetch_matches = $args['fetch_matches'] ?? false;
    $fetch_results = $args['fetch_results'] ?? false;
    if ($query instanceof WP_Query) {
      return $this->brackets_from_query($query);
    }

    $default_args = [
      'post_type' => Bracket::get_post_type(),
      'post_status' => 'any',
    ];

    $args = array_merge($default_args, $query);
    $query = new WP_Query($args);

    return $this->brackets_from_query($query, $fetch_matches, $fetch_results);
  }

  public function brackets_from_query(
    WP_Query $query,
    $fetch_matches = false,
    $fetch_results = false
  ): array {
    $brackets = [];
    foreach ($query->posts as $post) {
      $bracket = $this->get($post, $fetch_matches, $fetch_results);
      if ($bracket) {
        $brackets[] = $bracket;
      }
    }
    return $brackets;
  }

  public function update(
    Bracket|int|null $bracket,
    array|null $data = null
  ): ?Bracket {
    if ($bracket === null) {
      return null;
    }
    if (!($bracket instanceof Bracket)) {
      $bracket = $this->get($bracket);
    }
    $array = $bracket->to_array();
    $updated_array = empty($data) ? $array : array_merge($array, $data);

    $bracket = Bracket::from_array($updated_array);

    $post_id = $this->update_post($bracket, true);

    if (is_wp_error($post_id)) {
      return null;
    }

    $this->update_custom_table_data($post_id, $updated_array);
    // $this->update_teams($bracket->matches);

    $bracket_data = $this->get_custom_table_data($post_id);
    $bracket_id = $bracket_data['id'];

    if ($bracket_id && $bracket->results) {
      $this->results_repo->update_results($bracket_id, $bracket->results);
    }

    # refresh from db
    $bracket = $this->get($post_id);
    return $bracket;
  }

  public function update_custom_table_data(
    int $id,
    array $data,
    bool $use_post_id = true
  ): void {
    $old_data = $this->get_custom_table_data($id, $use_post_id);
    $id_field = $use_post_id ? 'post_id' : 'id';
    $update_fields = ['results_first_updated_at'];
    $update_data = [];
    foreach ($data as $key => $value) {
      if (in_array($key, $update_fields) && $value !== $old_data[$key]) {
        $update_data[$key] = $value;
      }
    }
    if (empty($update_data)) {
      return;
    }
    $this->wpdb->update(self::table_name(), $update_data, [
      $id_field => $id,
    ]);
  }

  public function delete(int $id, $force = false): bool {
    return $this->delete_post($id, $force);
  }

  public static function table_name(): string {
    return CustomTableNames::table_name('brackets');
  }

  public static function create_table(): void {
    global $wpdb;
    /**
     * Create the bracket brackets table
     */

    $table_name = self::table_name();
    $posts_table = $wpdb->posts;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id bigint(20) UNSIGNED NOT NULL,
      results_first_updated_at datetime,
			PRIMARY KEY (id),
			UNIQUE KEY (post_id),
			FOREIGN KEY (post_id) REFERENCES {$posts_table}(ID) ON DELETE CASCADE
		) $charset_collate;";

    // import dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  public static function drop_table(): void {
    global $wpdb;
    $table_name = self::table_name();
    $sql = "DROP TABLE IF EXISTS {$table_name}";
    $wpdb->query($sql);
  }
}
