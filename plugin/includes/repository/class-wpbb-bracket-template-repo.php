<?php

require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-match.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-team.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-bracket-team-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-bracket-match-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-custom-post-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wpbb-utils.php';

class Wpbb_BracketTemplateRepo extends Wpbb_CustomPostRepoBase {
  /**
   * @var Wpbb_BracketTeamRepo
   */
  private $team_repo;

  /**
   * @var Wpbb_BracketMatchRepo
   */
  private $match_repo;

  /**
   * @var wpdb
   */
  private $wpdb;

  public function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->team_repo = new Wpbb_BracketTeamRepo();
    $this->match_repo = new Wpbb_BracketMatchRepo();
    parent::__construct();
  }

  public function add(Wpbb_BracketTemplate $template): ?Wpbb_BracketTemplate {
    $post_id = $this->insert_post($template, true, true);

    if (is_wp_error($post_id)) {
      return null;
    }

    $template_id = $this->insert_template_data([
      'post_id' => $post_id,
    ]);

    if ($template->matches) {
      $this->insert_matches($template_id, $template->matches);
    }

    # refresh from db
    $template = $this->get($post_id);
    return $template;
  }

  public function insert_template_data(array $data): int {
    $table_name = $this->templates_table();
    $this->wpdb->insert($table_name, $data);
    return $this->wpdb->insert_id;
  }

  public function insert_matches(int $template_id, array $matches): void {
    $this->match_repo->insert_matches($template_id, $matches);
  }

  public function get(
    int|WP_Post|Wpbb_BracketTemplate|null $post = null,
    bool $fetch_matches = true
  ): ?Wpbb_BracketTemplate {
    if ($post instanceof Wpbb_BracketTemplate) {
      $post = $post->id;
    }

    $template_post = get_post($post);

    if (
      !$template_post ||
      $template_post->post_type !== Wpbb_BracketTemplate::get_post_type()
    ) {
      return null;
    }

    $template_data = $this->get_template_data($template_post);
    if (!isset($template_data['id'])) {
      return null;
    }
    $template_id = $template_data['id'];

    $matches =
      $fetch_matches && $template_id ? $this->get_matches($template_id) : [];
    $author_id = (int) $template_post->post_author;

    $data = [
      'id' => $template_post->ID,
      'title' => $template_post->post_title,
      'author' => $author_id,
      'status' => $template_post->post_status,
      'date' => get_post_meta($template_post->ID, 'date', true),
      'num_teams' => get_post_meta($template_post->ID, 'num_teams', true),
      'wildcard_placement' => get_post_meta(
        $template_post->ID,
        'wildcard_placement',
        true
      ),
      'published_date' => get_post_datetime($template_post->ID, 'date', 'gmt'),
      'matches' => $matches,
      'slug' => $template_post->post_name,
      'author_display_name' => $author_id
        ? get_the_author_meta('display_name', $author_id)
        : '',
    ];

    return new Wpbb_BracketTemplate($data);
  }

  public function update(
    Wpbb_BracketTemplate|int|null $template,
    array|null $data = null
  ): ?Wpbb_BracketTemplate {
    if (!$template || !$data) {
      return null;
    }
    if (!($template instanceof Wpbb_BracketTemplate)) {
      $template = $this->get($template);
    }
    $array = $template->to_array();
    $updated_array = array_merge($array, $data);

    $template = Wpbb_BracketTemplate::from_array($updated_array);

    $post_id = $this->update_post($template);

    if (is_wp_error($post_id)) {
      return null;
    }

    # refresh from db
    $template = $this->get($post_id);
    return $template;
  }

  public function get_template_data(int|WP_Post|null $template_post): array {
    if (
      !$template_post ||
      ($template_post instanceof WP_Post &&
        $template_post->post_type !== Wpbb_BracketTemplate::get_post_type())
    ) {
      return [];
    }

    if ($template_post instanceof WP_Post) {
      $template_post = $template_post->ID;
    }

    $table_name = $this->templates_table();
    $template_data = $this->wpdb->get_row(
      $this->wpdb->prepare(
        "SELECT * FROM $table_name WHERE post_id = %d",
        $template_post
      ),
      ARRAY_A
    );

    if (!$template_data) {
      return [];
    }

    return $template_data;
  }

  public function get_matches(int $template_id): array {
    return $this->match_repo->get_matches($template_id);
  }

  public function get_all(array|WP_Query $query = []): array {
    if ($query instanceof WP_Query) {
      return $this->templates_from_query($query);
    }

    $default_args = [
      'post_type' => Wpbb_BracketTemplate::get_post_type(),
      'post_status' => 'any',
    ];

    $args = array_merge($default_args, $query);
    $query = new WP_Query($args);

    return $this->templates_from_query($query);
  }

  public function templates_from_query(WP_Query $query): array {
    $templates = [];
    foreach ($query->posts as $post) {
      $template = $this->get($post, false);
      if ($template) {
        $templates[] = $template;
      }
    }
    return $templates;
  }

  public function delete(int $id, $force = false): bool {
    return $this->delete_post($id, $force);
  }

  public function templates_table(): string {
    return $this->wpdb->prefix . 'bracket_builder_templates';
  }
}
