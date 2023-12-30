<?php
namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\Includes\Service\Serializer\BracketPlaySerializer;
use WStrategies\BMB\Includes\Service\Serializer\BracketSerializer;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://github.com/barrymolina
 * @since      1.0.0
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/public
 * @author     Barry Molina <barry@wstrategies.co>
 */
class EnqueueScriptsHooks implements HooksInterface {
  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string $plugin_name The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  private $play_repo;

  /**
   * @var BracketRepo
   */
  private $bracket_repo;

  /**
   * @var BracketProductUtils
   */
  private $bracket_product_utils;

  /**
   * @var BracketPlaySerializer
   */
  private BracketPlaySerializer $play_serializer;

  /**
   * @var BracketSerializer
   */
  private BracketSerializer $bracket_serializer;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct($args = []) {
    $this->plugin_name = $args['plugin_name'];
    $this->version = $args['version'];
    $this->play_repo = $args['play_repo'] ?? new BracketPlayRepo();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->bracket_product_utils =
      $args['bracket_product_utils'] ?? new BracketProductUtils();
    $this->play_serializer =
      $args['play_serializer'] ?? new BracketPlaySerializer();
    $this->bracket_serializer =
      $args['bracket_serializer'] ?? new BracketSerializer();
  }

  public function load(Loader $loader): void {
    $loader->add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    $loader->add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {
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
      plugin_dir_url(dirname(__FILE__, 2)) . 'Public/css/wpbb-public.css',
      [],
      $this->version,
      'all'
    );
    wp_enqueue_style(
      'index.css',
      plugin_dir_url(dirname(__FILE__, 2)) .
        'Includes/react-bracket-builder/build/wordpress/index.css',
      [],
      null,
      'all'
    );
  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {
    wp_enqueue_script(
      'tailwind',
      'https://cdn.tailwindcss.com',
      [],
      $this->version,
      false
    );
    wp_enqueue_script(
      'wpbb-bracket-builder-react',
      plugin_dir_url(dirname(__FILE__, 2)) .
        'Includes/react-bracket-builder/build/wordpress/index.js',
      ['wp-element'],
      $this->version,
      true
    );

    $sentry_env = defined('WP_SENTRY_ENV') ? WP_SENTRY_ENV : 'production';
    $sentry_dsn = defined('WP_SENTRY_PHP_DSN') ? WP_SENTRY_PHP_DSN : '';
    list($bracket, $play) = $this->get_bracket_and_play();

    wp_localize_script('wpbb-bracket-builder-react', 'wpbb_app_obj', [
      'sentry_env' => $sentry_env,
      'sentry_dsn' => $sentry_dsn,
      'nonce' => wp_create_nonce('wp_rest'),
      'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
      'my_brackets_url' =>
        get_permalink(get_page_by_path('dashboard')) . '?tab=brackets',
      'bracket_builder_url' => get_permalink(
        get_page_by_path('bracket-builder')
      ),
      'user_can_share_bracket' => current_user_can('wpbb_share_bracket')
        ? true
        : false,
      'upgrade_account_url' => $this->get_bmb_plus_permalink(),
      'bracket_product_archive_url' => $this->get_bracket_product_archive_url(),
      'my_play_history_url' =>
        get_permalink(get_page_by_path('dashboard')) . '?tab=play-history',
      'play' => $play,
      'bracket' => $bracket,
      'is_user_logged_in' => is_user_logged_in(),
    ]);
  }

  private function get_bmb_plus_permalink() {
    // use wp query to get the post for the bmb subscription
    $args = [
      'name' => $this->get_bmb_plus_slug(),
      'post_type' => 'product',
      'post_status' => 'publish',
      'numberposts' => 1,
    ];
    $posts = get_posts($args);
    if (empty($posts)) {
      // return the home url if the bmb plus product is not found
      return get_home_url();
    }
    return get_permalink($posts[0]);
  }

  private function get_bmb_plus_slug() {
    return defined('BMB_PLUS_SLUG') ? BMB_PLUS_SLUG : 'bmb-plus';
  }

  private function get_bracket_product_archive_url() {
    return $this->bracket_product_utils->get_bracket_product_archive_url();
  }

  private function get_bracket_and_play() {
    $play = null;
    $bracket = null;
    $post = get_post();
    if (!$post) {
      return;
    }
    if ($post->post_type === 'bracket_play') {
      $play = $this->play_serializer->serialize($this->play_repo->get($post));
      $bracket = $play['bracket'];
    } elseif ($post->post_type === 'bracket') {
      $bracket = $this->bracket_serializer->serialize(
        $this->bracket_repo->get($post)
      );
    }
    return [$bracket, $play];
  }
}
