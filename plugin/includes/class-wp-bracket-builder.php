<?php
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-loader.php';
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-i18n.php';
require_once WPBB_PLUGIN_DIR . 'admin/class-wpbb-admin.php';
require_once WPBB_PLUGIN_DIR . 'public/class-wpbb-public.php';
require_once WPBB_PLUGIN_DIR .
  'includes/controllers/class-wpbb-bracket-api.php';
require_once WPBB_PLUGIN_DIR .
  'includes/controllers/class-wpbb-bracket-play-api.php';
require_once WPBB_PLUGIN_DIR . 'public/class-wpbb-public-hooks.php';
require_once WPBB_PLUGIN_DIR . 'public/class-wpbb-public-shortcodes.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-product-integration-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/gelato/class-wpbb-gelato-product-integration.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/bracket-product/class-wpbb-bracket-product-hooks.php';
require_once WPBB_PLUGIN_DIR . 'admin/class-wpbb-custom-post-hooks.php';
require_once WPBB_PLUGIN_DIR . 'public/class-wpbb-enqueue-scripts-hooks.php';

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 * @author     Barry Molina <barry@wstrategies.co>
 */
class Wp_Bracket_Builder {
  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      Wpbb_Loader $loader Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;
  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function __construct() {
    if (defined('WP_BRACKET_BUILDER_VERSION')) {
      $this->version = WP_BRACKET_BUILDER_VERSION;
    } else {
      $this->version = '1.0.0';
    }
    $this->plugin_name = 'wp-bracket-builder';
    $this->loader = new Wpbb_Loader();
    $this->set_locale();
    $this->define_hooks();
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Wpbb_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale() {
    (new Wpbb_i18n())->load($this->loader);
  }

  private function define_hooks() {
    $name_and_version_args = [
      'plugin_name' => $this->get_plugin_name(),
      'version' => $this->get_version(),
    ];
    /** @var Wpbb_HooksInterface[] $hooks */
    $hooks = [
      new Wpbb_PublicHooks(),
      new Wpbb_Admin($name_and_version_args),
      new Wpbb_GelatoProductIntegration(),
      new Wpbb_BracketProductHooks(),
      new Wpbb_EnqueueScriptsHooks($name_and_version_args),
      new Wpbb_PublicShortcodes(),
      new Wpbb_CustomPostHooks(),
      new Wpbb_BracketApi(),
      new Wpbb_BracketPlayApi(),
    ];
    foreach ($hooks as $hook) {
      $hook->load($this->loader);
    }
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run() {
    $this->loader->run();
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name() {
    return $this->plugin_name;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version() {
    return $this->version;
  }
}
