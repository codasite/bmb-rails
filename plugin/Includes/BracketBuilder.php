<?php
namespace WStrategies\BMB\Includes;
use WStrategies\BMB\Includes\Controllers\BracketApi;
use WStrategies\BMB\Includes\Controllers\BracketPlayApi;
use WStrategies\BMB\Includes\Controllers\NotificationApi;
use WStrategies\BMB\Includes\Hooks\AdminHooks;
use WStrategies\BMB\Includes\Hooks\BracketAdminHooks;
use WStrategies\BMB\Includes\Hooks\CustomPostHooks;
use WStrategies\BMB\Includes\Hooks\EnqueueScriptsHooks;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Permissions;
use WStrategies\BMB\Includes\Hooks\PublicHooks;
use WStrategies\BMB\Includes\Hooks\PublicShortcodes;
use WStrategies\BMB\Includes\Hooks\UpcomingBracketHooks;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductHooks;
use WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato\GelatoProductIntegration;

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
class BracketBuilder {
  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      Loader $loader Maintains and registers all hooks for the plugin.
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
    $this->loader = new Loader();
    $this->set_locale();
    $this->define_hooks();
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale() {
    (new i18n())->load($this->loader);
  }

  private function define_hooks() {
    $name_and_version_args = [
      'plugin_name' => $this->get_plugin_name(),
      'version' => $this->get_version(),
    ];
    /** @var HooksInterface[] $hooks */
    $hooks = [
      new PublicHooks(),
      new AdminHooks($name_and_version_args),
      new GelatoProductIntegration(),
      new BracketProductHooks(),
      new EnqueueScriptsHooks($name_and_version_args),
      new PublicShortcodes(),
      new CustomPostHooks(),
      new BracketApi(),
      new BracketPlayApi(),
      new NotificationApi(),
      new Permissions(),
      new UpcomingBracketHooks(),
      new BracketAdminHooks(),
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
