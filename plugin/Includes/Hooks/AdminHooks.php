<?php
namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Includes\Loader;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/admin
 * @author     Barry Molina <barry@wstrategies.co>
 */
class AdminHooks implements HooksInterface {
  private string $plugin_name;

  private string $version;

  public function __construct($opts = []) {
    $this->plugin_name = $opts['plugin_name'];
    $this->version = $opts['version'];
  }

  public function load(Loader $loader): void {
    $loader->add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
    // $loader->add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

    $loader->add_action('init', [$this, 'add_capabilities']);
    $loader->add_action(
      'add_user_role',
      [$this, 'create_user_profile_post'],
      10,
      2
    );
    $loader->add_action(
      'remove_user_role',
      [$this, 'remove_user_profile_post'],
      10,
      2
    );
    $loader->add_filter(
      'manage_posts_columns',
      [$this, 'add_post_id_column'],
      10,
      1
    );
    $loader->add_filter(
      'manage_posts_custom_column',
      [$this, 'get_post_id_column_content'],
      10,
      2
    );
  }

  /**
   * Register the stylesheets for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_styles(): void {
    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Loader as all of the hooks are defined
     * in that particular class.
     *
     * The Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    wp_enqueue_style(
      $this->plugin_name,
      plugin_dir_url(dirname(__FILE__, 2)) . 'Admin/css/wpbb-admin.css',
      [],
      $this->version,
      'all'
    );
  }

  /**
   * Register the JavaScript for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts(): void {
    wp_enqueue_script(
      'wpbb-bracket-builder-react',
      plugin_dir_url(dirname(__FILE__, 2)) .
        'Includes/react-bracket-builder/build/wordpress/index.js',
      ['wp-element'],
      $this->version,
      true
    );
    wp_enqueue_script(
      'tailwind',
      'https://cdn.tailwindcss.com',
      [],
      $this->version,
      false
    );
  }

  public function add_capabilities(): void {
    $role = get_role('administrator');
    $role->add_cap('wpbb_share_bracket');
    $role->add_cap('wpbb_bust_play');
    $role->add_cap('wpbb_enable_chat');
    $role->add_cap('wpbb_delete_bracket');
    $role->add_cap('wpbb_edit_bracket');
    $role->add_cap('wpbb_play_bracket');
    $role->add_cap('wpbb_view_bracket_chat');
    $role->add_cap('wpbb_view_play');
    $role->add_cap('wpbb_print_play');
    $role->add_cap('wpbb_delete_notification');
    $role->add_cap('wpbb_create_payment_intent');
  }

  /**
   * Adds a user_profile post when a bmb_vip role is added to a user.
   * @param int $user_id
   * @param string $role
   */
  public function create_user_profile_post(int $user_id, string $role): void {
    if ($role === 'bmb_vip') {
      $username = get_the_author_meta('user_login', $user_id);
      $posts = get_posts([
        'post_type' => 'user_profile',
        'post_status' => 'publish',
        'author' => $user_id,
      ]);
      $post_id = null;
      if (count($posts) > 0) {
        $post_id = $posts[0]->ID;
      }
      $post = [
        'ID' => $post_id,
        'post_title' => $username,
        'post_name' => sanitize_title($username),
        'post_content' => '',
        'post_status' => 'publish',
        'post_author' => $user_id,
        'post_type' => 'user_profile',
      ];
      wp_insert_post($post);
    }
  }

  /**
   * Removes a user_profile post when a bmb_vip role is removed from a user.
   * @param int $user_id
   * @param string $role
   */
  public function remove_user_profile_post(int $user_id, string $role): void {
    if ($role != 'bmb_vip') {
      return;
    }
    $posts = get_posts([
      'post_type' => 'user_profile',
      'post_status' => 'publish',
      'author' => $user_id,
    ]);
    foreach ($posts as $post) {
      wp_delete_post($post->ID, true);
    }
  }

  function add_post_id_column($columns) {
    $columns['post_id_clmn'] = 'ID'; // $Columns['Column ID'] = 'Column Title';
    return $columns;
  }

  function get_post_id_column_content($column, $id): void {
    if ($column === 'post_id_clmn') {
      echo $id;
    }
  }
}
