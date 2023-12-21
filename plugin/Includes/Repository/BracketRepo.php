<?php
namespace WStrategies\BMB\Includes\Repository;

use DateTimeImmutable;
use WP_Post;
use WP_Query;
use wpdb;
use WStrategies\BMB\Includes\Domain\Bracket;

class BracketRepo extends CustomPostRepoBase implements CustomTableInterface {
  /**
   * @var BracketTeamRepo
   */
  private $team_repo;

  /**
   * @var BracketMatchRepo
   */
  private $match_repo;

  /**
   * @var BracketResultsRepo
   */
  private $results_repo;

  /**
   * @var wpdb
   */
  private $wpdb;

  public function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->team_repo = new BracketTeamRepo();
    $this->match_repo = new BracketMatchRepo($this->team_repo);
    $this->results_repo = new BracketResultsRepo($this, $this->team_repo);
    parent::__construct();
  }

  public function add(Bracket $bracket): ?Bracket {
    $post_id = $this->insert_post($bracket, true, true);

    if (is_wp_error($post_id)) {
      return null;
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
    bool $fetch_results = true
  ): ?Bracket {
    if ($post instanceof Bracket) {
      $post = $post->id;
    }

    $bracket_post = get_post($post);

    if (
      !$bracket_post ||
      $bracket_post->post_type !== Bracket::get_post_type()
    ) {
      return null;
    }

    $bracket_data = $this->get_custom_table_data($bracket_post->ID);
    if (!isset($bracket_data['id'])) {
      return null;
    }
    $bracket_id = $bracket_data['id'];
    $results_updated = isset($bracket_data['results_first_updated_at'])
      ? new DateTimeImmutable($bracket_data['results_first_updated_at'])
      : false;

    $matches =
      $fetch_matches && $bracket_id
        ? $this->match_repo->get_matches($bracket_id)
        : [];

    $results =
      $fetch_results && $bracket_id
        ? $this->results_repo->get_results($bracket_id)
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
      'slug' => $bracket_post->post_name,
      'author_display_name' => $author_id
        ? get_the_author_meta('display_name', $author_id)
        : '',
      'results_first_updated_at' => $results_updated,
      'thumbnail_url' => get_the_post_thumbnail_url($bracket_post->ID),
      'url' => get_permalink($bracket_post->ID),
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

  public function get_all(array|WP_Query $query = []): array {
    if ($query instanceof WP_Query) {
      return $this->brackets_from_query($query);
    }

    $default_args = [
      'post_type' => Bracket::get_post_type(),
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
      winning_play_id bigint(20) UNSIGNED,
      winning_play_post_id bigint(20) UNSIGNED,
			PRIMARY KEY (id),
			UNIQUE KEY (post_id),
			FOREIGN KEY (post_id) REFERENCES {$posts_table}(ID) ON DELETE CASCADE
		) $charset_collate;";

    // import dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  public static function add_constraints() {
    global $wpdb;
    $table_name = self::table_name();
    $plays_table = BracketPlayRepo::table_name();
    $posts_table = $wpdb->posts;
    $sql = "ALTER TABLE $table_name
      ADD FOREIGN KEY (winning_play_id) REFERENCES {$plays_table}(id) ON DELETE SET NULL,
      ADD FOREIGN KEY (winning_play_post_id) REFERENCES {$posts_table}(ID) ON DELETE SET NULL
    ";
    $wpdb->query($sql);
  }

  public static function drop_table(): void {
    global $wpdb;
    $table_name = self::table_name();
    $sql = "DROP TABLE IF EXISTS {$table_name}";
    $wpdb->query($sql);
  }
}
