<?php
namespace WStrategies\BMB\Includes\Repository;

use Exception;
use WP_Post;
use WP_Query;
use wpdb;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Service\Permissions\PlayPermissions;
use WStrategies\BMB\Includes\Utils;

class BracketPlayRepo extends CustomPostRepoBase implements
  CustomTableInterface {
  /**
   * @var Utils
   */
  private $utils;

  /**
   * @var BracketRepo
   */
  private $bracket_repo;

  /**
   * @var BracketTeamRepo
   */
  public $team_repo;

  /**
   * @var BracketMatchPickRepo
   */
  public BracketMatchPickRepo $pick_repo;

  /**
   * @var wpdb
   */
  private $wpdb;

  public function __construct($args = []) {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->team_repo = $args['team_repo'] ?? new BracketTeamRepo();
    $this->pick_repo =
      $args['pick_repo'] ?? new BracketMatchPickRepo($this->team_repo);
    $this->utils = $args['utils'] ?? new Utils();
    parent::__construct();
  }

  public function get_id_from_cookie() {
    $play_id = $this->utils->get_cookie('play_id');
    if (!$play_id) {
      $this->utils->warn('play_id not found');
      return null;
    }
    return $play_id;
  }

  public function get(
    int|WP_Post|null|BracketPlay $post = null,
    array $opts = []
  ): ?BracketPlay {
    list(
      $fetch_picks,
      $fetch_bracket,
      $fetch_results,
      $fetch_matches,
    ) = $this->get_defaults($opts);

    if ($post === null) {
      $post = $this->get_id_from_cookie();
    }

    if ($post instanceof BracketPlay) {
      $post = $post->id;
    }

    $play_post = get_post($post);

    if (!$play_post || $play_post->post_type !== BracketPlay::get_post_type()) {
      return null;
    }

    $play_data = $this->get_play_data($play_post);
    if (!isset($play_data['id'])) {
      return null;
    }
    $play_id = $play_data['id'];
    $bracket_post_id = $play_data['bracket_post_id'];
    $busted_id = $play_data['busted_play_post_id'];
    $author_id = (int) $play_post->post_author;

    $data = [
      'bracket_id' => $bracket_post_id,
      'author' => $author_id,
      'id' => $play_post->ID,
      'title' => $play_post->post_title,
      'status' => $play_post->post_status,
      'published_date' => get_post_datetime($play_post->ID, 'date', 'gmt'),
      'picks' =>
        $fetch_picks && $play_id ? $this->pick_repo->get_picks($play_id) : [],
      'bracket' =>
        $bracket_post_id && $fetch_bracket
          ? $this->bracket_repo->get(
            $bracket_post_id,
            $fetch_results,
            $fetch_matches
          )
          : null,
      'total_score' => $play_data['total_score'] ?? null,
      'accuracy_score' => $play_data['accuracy_score'] ?? null,
      'slug' => $play_post->post_name,
      'author_display_name' => $author_id
        ? get_the_author_meta('display_name', $author_id)
        : '',
      'busted_id' => $busted_id,
      'busted_play' => $busted_id
        ? $this->get($busted_id, [
          'fetch_bracket' => false,
          'fetch_results' => false,
          'fetch_matches' => false,
        ])
        : null,
      'is_printed' => (bool) $play_data['is_printed'],
      'is_bustable' => PlayPermissions::is_bustable($play_post),
      'is_winner' => (bool) $play_data['is_winner'],
      'bmb_official' => (bool) $play_data['bmb_official'],
      'is_tournament_entry' => (bool) $play_data['is_tournament_entry'],
      'thumbnail_url' => get_the_post_thumbnail_url(
        $play_post->ID,
        'thumbnail'
      ),
      'url' => get_permalink($play_post->ID),
    ];

    return new BracketPlay($data);
  }

  private function get_defaults(array $user_opts = []): array {
    $default_opts = [
      'fetch_picks' => true,
      'fetch_bracket' => true,
      'fetch_results' => true,
      'fetch_matches' => true,
    ];
    $opts = array_merge($default_opts, $user_opts);
    return [
      $opts['fetch_picks'],
      $opts['fetch_bracket'],
      $opts['fetch_results'],
      $opts['fetch_matches'],
    ];
  }

  public function get_play_data(int|WP_Post|null $play_post): array {
    if (
      !$play_post ||
      ($play_post instanceof WP_Post &&
        $play_post->post_type !== BracketPlay::get_post_type())
    ) {
      return [];
    }

    if ($play_post instanceof WP_Post) {
      $play_post = $play_post->ID;
    }

    $table_name = self::table_name();
    $play_data = $this->wpdb->get_row(
      $this->wpdb->prepare(
        "SELECT * FROM $table_name WHERE post_id = %d",
        $play_post
      ),
      ARRAY_A
    );
    if (!$play_data) {
      return [];
    }
    return $play_data;
  }

  public function get_all(
    array|WP_Query $query = [],
    array $options = [
      'fetch_picks' => false,
      'fetch_bracket' => false,
      'fetch_results' => false,
      'fetch_matches' => false,
    ]
  ): array {
    if ($query instanceof WP_Query) {
      return $this->plays_from_query($query, $options);
    }

    $default_args = [
      'post_type' => BracketPlay::get_post_type(),
      'posts_per_page' => -1,
      'post_status' => 'any',
    ];

    $args = array_merge($default_args, $query);

    $query = new WP_Query($args);

    return $this->plays_from_query($query, $options);
  }

  private function plays_from_query(WP_Query $query, $options): array {
    $plays = [];
    foreach ($query->posts as $post) {
      $play = $this->get($post, $options);
      if ($play) {
        $plays[] = $play;
      }
    }
    return $plays;
  }

  public function get_count(array $query_args): int {
    $default_args = [
      'post_type' => BracketPlay::get_post_type(),
      'posts_per_page' => -1,
      'post_status' => 'publish',
    ];

    $args = array_merge($default_args, $query_args);

    $query = new WP_Query($args);

    return $query->found_posts;
  }

  /**
   * @throws ValidationException
   */
  public function add(BracketPlay $play): ?BracketPlay {
    $post_id = $this->insert_post($play, true, true);

    if (is_wp_error($post_id)) {
      throw new Exception('Error creating play post');
    }

    $bracket_post_id = $play->bracket_id;

    if (!$bracket_post_id) {
      throw new ValidationException('bracket_id is required');
    }

    $bracket = $this->bracket_repo->get_custom_table_data($bracket_post_id);
    $bracket_id = $bracket['id'] ?? null;
    if (!$bracket_id) {
      throw new Exception('bracket_id not found');
    }

    $busted_post_id = $play->busted_id;
    if ($busted_post_id !== null) {
      $busted_play_data = $this->get_play_data($busted_post_id);
      $busted_play_id = $busted_play_data['id'];
    }

    $play_id = $this->insert_play_data([
      'post_id' => $post_id,
      'bracket_post_id' => $bracket_post_id,
      'bracket_id' => $bracket_id,
      'busted_play_post_id' => $busted_post_id,
      'busted_play_id' => $busted_play_id ?? null,
      'total_score' => $play->total_score,
      'accuracy_score' => $play->accuracy_score,
      'is_printed' => $play->is_printed ?? false,
      'is_winner' => $play->is_winner ?? false,
      'bmb_official' => $play->bmb_official ?? false,
      'is_tournament_entry' => $play->is_tournament_entry ?? false,
    ]);

    if ($play_id && $play->picks) {
      $this->pick_repo->insert_picks($play_id, $play->picks);
    }

    return $this->get($post_id);
  }

  private function insert_play_data(array $data): int {
    $table_name = self::table_name();
    $this->wpdb->insert($table_name, $data);
    return $this->wpdb->insert_id;
  }

  /**
   * Get an array of users and their picks for the given bracket result
   *
   * @param int $bracket_id The bracket id
   * @param MatchPick $bracket_result The bracket result to match against
   *
   * @return array An array of objects with the user and their pick
   */
  public function get_user_picks_for_result(
    Bracket|int|null $bracket_id,
    MatchPick $bracket_result
  ): array {
    if (!$bracket_id) {
      return [];
    }
    if ($bracket_id instanceof Bracket) {
      $bracket_id = $bracket_id->id;
    }
    global $wpdb;
    $plays_table = self::table_name();
    $picks_table = $this->pick_repo->table_name();
    $posts_table = $wpdb->prefix . 'posts';
    $users_table = $wpdb->prefix . 'users';

    $query = "
        SELECT users.ID as user_id, picks.id as pick_id, plays.post_id as play_id
        FROM $plays_table plays
        JOIN $picks_table picks 
        ON picks.bracket_play_id = plays.id
        AND picks.round_index = %d
        AND picks.match_index = %d
        JOIN $posts_table posts
        ON posts.ID = plays.post_id
        JOIN $users_table users
        ON users.ID = posts.post_author
        WHERE plays.bracket_post_id = %d
        GROUP BY posts.post_author;
        ";

    $prepared_query = $wpdb->prepare(
      $query,
      $bracket_result->round_index,
      $bracket_result->match_index,
      $bracket_id
    );

    $results = $wpdb->get_results($prepared_query);
    $user_picks = [];
    foreach ($results as $result) {
      $user_pick = [
        'play_id' => $result->play_id,
        'user_id' => $result->user_id,
        'pick_id' => $result->pick_id,
      ];
      $user_picks[] = $user_pick;
    }
    return $user_picks;
  }

  public function update(BracketPlay|int|null $play, $data): ?BracketPlay {
    if ($play === null || empty($data)) {
      return null;
    }
    if (!($play instanceof BracketPlay)) {
      $play = $this->get($play);
    }
    $array = $play->to_array();
    $updated_array = array_merge($array, $data);

    $play = BracketPlay::from_array($updated_array);

    $post_id = $this->update_post($play, true);

    if (is_wp_error($post_id)) {
      return null;
    }

    $play_data = $this->get_play_data($post_id);
    $play_id = $play_data['id'];

    if ($play_id) {
      $this->update_play_data($play_id, $data);
    }

    return $this->get($post_id);
  }

  private function update_play_data(
    $play_id,
    $data,
    $use_post_id = false
  ): void {
    $id_field = $use_post_id ? 'post_id' : 'id';
    $update_fields = [];
    $update_data = [];
    foreach ($data as $key => $value) {
      if (in_array($key, $this->play_data_update_fields())) {
        $update_fields[] = $key;
        $update_data[$key] = $value;
      }
    }
    if (empty($update_fields)) {
      return;
    }
    $this->wpdb->update(self::table_name(), $update_data, [
      $id_field => $play_id,
    ]);
  }

  private function play_data_update_fields(): array {
    return ['is_printed', 'is_tournament_entry'];
  }

  public static function table_name(): string {
    return CustomTableNames::table_name('plays');
  }

  public static function create_table(): void {
    /**
     * Create the play meta table
     */

    global $wpdb;
    $table_name = self::table_name();
    $brackets_table = BracketRepo::table_name();
    $posts_table = $wpdb->posts;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id bigint(20) UNSIGNED NOT NULL,
			total_score int(11),
			accuracy_score float,
			bracket_post_id bigint(20) UNSIGNED,
      bracket_id bigint(20) UNSIGNED,
			busted_play_post_id bigint(20) UNSIGNED,
			busted_play_id bigint(20) UNSIGNED,
      is_printed tinyint(1) NOT NULL DEFAULT 0,
      is_winner tinyint(1) NOT NULL DEFAULT 0,
      bmb_official tinyint(1) NOT NULL DEFAULT 0,
      is_tournament_entry tinyint(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			UNIQUE KEY (post_id),
			FOREIGN KEY (post_id) REFERENCES {$posts_table}(ID) ON DELETE CASCADE,
			FOREIGN KEY (bracket_post_id) REFERENCES {$posts_table}(ID) ON DELETE CASCADE,
			FOREIGN KEY (bracket_id) REFERENCES {$brackets_table}(id) ON DELETE CASCADE,
			FOREIGN KEY (busted_play_post_id) REFERENCES {$posts_table}(ID) ON DELETE SET NULL,
			FOREIGN KEY (busted_play_id) REFERENCES {$table_name}(id) ON DELETE SET NULL
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
