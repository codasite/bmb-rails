<?php

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

    $this->load_dependencies();
    $this->set_locale();
    $this->define_admin_hooks();
    $this->define_public_hooks();
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Wpbb_Loader. Orchestrates the hooks of the plugin.
   * - Wpbb_i18n. Defines internationalization functionality.
   * - Wpbb_Admin. Defines all hooks for the admin area.
   * - Wpbb_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies() {
    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once plugin_dir_path(dirname(__FILE__)) .
      'includes/class-wpbb-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path(dirname(__FILE__)) .
      'includes/class-wpbb-i18n.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path(dirname(__FILE__)) .
      'admin/class-wpbb-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once plugin_dir_path(dirname(__FILE__)) .
      'public/class-wpbb-public.php';

    /**
     * The bracket api controller class
     */
    require_once plugin_dir_path(dirname(__FILE__)) .
      'includes/controllers/class-wpbb-bracket-api.php';

    /**
     * The bracket picks api controller class
     */
    require_once plugin_dir_path(dirname(__FILE__)) .
      'includes/controllers/class-wpbb-bracket-play-api.php';

    /**
     * Callbacks for hooks and filters
     */
    require_once plugin_dir_path(dirname(__FILE__)) .
      'public/class-wpbb-public-hooks.php';

    /**
     * Shortcodes for the public side of the site
     */
    require_once plugin_dir_path(dirname(__FILE__)) .
      'public/class-wpbb-public-shortcodes.php';

    /**
     * Bracket Product Integrations
     */
    require_once plugin_dir_path(dirname(__FILE__)) .
      'includes/service/product-integrations/class-wpbb-product-integration-interface.php';
    require_once plugin_dir_path(dirname(__FILE__)) .
      'includes/service/product-integrations/gelato/class-wpbb-gelato-product-integration.php';

    $this->loader = new Wpbb_Loader();
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
    $plugin_i18n = new Wpbb_i18n();

    $this->loader->add_action(
      'plugins_loaded',
      $plugin_i18n,
      'load_plugin_textdomain'
    );
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks() {
    $plugin_admin = new Wpbb_Admin(
      $this->get_plugin_name(),
      $this->get_version()
    );
    $bracket_api = new Wpbb_BracketApi();
    $play_api = new Wpbb_BracketPlayApi();

    $gelato_product_integration = new Wpbb_GelatoProductIntegration();

    $this->loader->add_action(
      'admin_enqueue_scripts',
      $plugin_admin,
      'enqueue_styles'
    );
    $this->loader->add_action(
      'admin_enqueue_scripts',
      $plugin_admin,
      'enqueue_scripts'
    );

    $this->loader->add_action(
      'admin_menu',
      $plugin_admin,
      'bracket_builder_init_menu'
    );
    $this->loader->add_action('init', $plugin_admin, 'add_capabilities');

    $this->loader->add_action('rest_api_init', $bracket_api, 'register_routes');
    $this->loader->add_action('rest_api_init', $play_api, 'register_routes');

    $this->loader->add_action('init', $this, 'register_custom_post_types');
    $this->loader->add_action('init', $this, 'register_custom_post_status');

    // custom meta for Gelato bracket product variations
    $this->loader->add_action(
      'woocommerce_product_after_variable_attributes',
      $gelato_product_integration,
      'after_variable_attributes',
      10,
      3
    );
    $this->loader->add_action(
      'woocommerce_save_product_variation',
      $gelato_product_integration,
      'save_product_variation',
      10,
      2
    );
    $this->loader->add_action(
      'admin_notices',
      $gelato_product_integration,
      'admin_notices'
    );
  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_public_hooks() {
    $plugin_public = new Wpbb_Public(
      $this->get_plugin_name(),
      $this->get_version()
    );
    $shortcodes = new Wpbb_Public_Shortcodes();

    $gelato_product_integration = new Wpbb_GelatoProductIntegration();

    $this->loader->add_action(
      'wp_enqueue_scripts',
      $plugin_public,
      'enqueue_styles'
    );
    $this->loader->add_action(
      'wp_enqueue_scripts',
      $plugin_public,
      'enqueue_scripts'
    );

    $this->loader->add_filter(
      'woocommerce_add_to_cart_validation',
      $gelato_product_integration,
      'add_to_cart_validation',
      10,
      5
    );
    $this->loader->add_action(
      'woocommerce_add_cart_item_data',
      $gelato_product_integration,
      'add_cart_item_data',
      10,
      3
    );
    $this->loader->add_action(
      'woocommerce_checkout_create_order_line_item',
      $gelato_product_integration,
      'checkout_create_order_line_item',
      10,
      4
    );
    $this->loader->add_action(
      'woocommerce_before_checkout_process',
      $gelato_product_integration,
      'before_checkout_process'
    );
    $this->loader->add_action(
      'woocommerce_payment_complete',
      $gelato_product_integration,
      'payment_complete'
    );
    $this->loader->add_filter(
      'woocommerce_available_variation',
      $gelato_product_integration,
      'available_variation',
      10,
      3
    );
    $public_hooks = new Wpbb_PublicHooks();
    $this->loader->add_action('init', $public_hooks, 'add_rewrite_tags', 10, 0);
    $this->loader->add_action(
      'init',
      $public_hooks,
      'add_rewrite_rules',
      10,
      0
    );
    $this->loader->add_action('init', $public_hooks, 'add_roles');
    $this->loader->add_action(
      'template_redirect',
      $public_hooks,
      'template_redirect'
    );
    $this->loader->add_filter('query_vars', $public_hooks, 'add_query_vars');
    $this->loader->add_filter(
      'posts_clauses',
      $public_hooks,
      'custom_query_fields',
      10,
      2
    );
    $this->loader->add_filter(
      'user_has_cap',
      $public_hooks,
      'user_cap_filter',
      10,
      3
    );

    $this->loader->add_action('init', $shortcodes, 'add_shortcodes');

    $this->loader->add_action(
      'woocommerce_subscription_status_active',
      $public_hooks,
      'add_bmb_plus_role',
      10,
      1
    );

    $this->loader->add_action(
      'woocommerce_subscription_status_cancelled',
      $public_hooks,
      'remove_bmb_plus_role',
      10,
      1
    );

    $this->loader->add_action(
      'wpbb_play_printed',
      $public_hooks,
      'mark_play_printed',
      10,
      1
    );
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
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @return    Wpbb_Loader    Orchestrates the hooks of the plugin.
   * @since     1.0.0
   */
  public function get_loader() {
    return $this->loader;
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

  public function register_custom_post_types() {
    register_post_type('bracket', [
      'labels' => [
        'name' => __('Brackets'),
        'singular_name' => __('Bracket'),
      ],
      'description' => 'Brackets for the WP Bracket Builder plugin',
      'public' => true,
      'has_archive' => true,
      'supports' => ['title', 'author', 'thumbnail', 'custom-fields'],
      'show_ui' => true,
      'show_in_rest' => true, // Default endpoint for oxygen. React app uses Wpbb_Bracket_Api
      'taxonomies' => ['post_tag'],
      'rewrite' => ['slug' => 'brackets'],
    ]);

    register_post_type('bracket_play', [
      'labels' => [
        'name' => __('Plays'),
        'singular_name' => __('Play'),
      ],
      'description' => 'Bracket plays for the WP Bracket Builder plugin',
      'public' => true,
      'has_archive' => true,
      'supports' => ['title', 'author', 'thumbnail', 'custom-fields'],
      'show_ui' => true,
      'show_in_rest' => true,
      // 'rest_controller_class' => 'Wpbb_Bracket_Api',
      // 'rest_controller_class' => array($bracket_api, 'register_routes'),
      'taxonomies' => ['post_tag'],
      'rewrite' => ['slug' => 'plays'],
    ]);
  }

  public function register_custom_post_status() {
    // Custom post status for completed tournaments
    register_post_status('complete', [
      'label' => 'Complete',
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop(
        'Completed <span class="count">(%s)</span>',
        'Complete <span class="count">(%s)</span>'
      ),
    ]);

    register_post_status('score', [
      'label' => 'Scored',
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop(
        'Scored <span class="count">(%s)</span>',
        'Scored <span class="count">(%s)</span>'
      ),
    ]);

    register_post_status('archive', [
      'label' => 'Archive',
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop(
        'Completed <span class="count">(%s)</span>',
        'Archive <span class="count">(%s)</span>'
      ),
    ]);
  }
}
