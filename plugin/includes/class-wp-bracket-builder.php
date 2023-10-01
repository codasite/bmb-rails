<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://github.com/barrymolina
 * @since      1.0.0
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 */

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
	 * @var      Wp_Bracket_Builder_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * - Wp_Bracket_Builder_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Bracket_Builder_i18n. Defines internationalization functionality.
	 * - Wp_Bracket_Builder_Admin. Defines all hooks for the admin area.
	 * - Wp_Bracket_Builder_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-bracket-builder-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-bracket-builder-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-bracket-builder-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-bracket-builder-public.php';

		/**
		 * The bracket template api controller class
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/controllers/class-wp-bracket-builder-bracket-template-api.php';

		/**
		 * The bracket tournament api controller class
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/controllers/class-wp-bracket-builder-bracket-tournament-api.php';

		/**
		 * The bracket picks api controller class
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/controllers/class-wp-bracket-builder-bracket-play-api.php';

		/**
		 * The html to image converter api controller class
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/controllers/class-wp-bracket-builder-convert-api.php';

		/**
		 * Callbacks for hooks and filters
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-bracket-builder-public-hooks.php';

		/**
		 * Shortcodes for the public side of the site
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-bracket-builder-public-shortcodes.php';

		$this->loader = new Wp_Bracket_Builder_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Bracket_Builder_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wp_Bracket_Builder_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wp_Bracket_Builder_Admin($this->get_plugin_name(), $this->get_version());
		$template_api = new Wp_Bracket_Builder_Bracket_Template_Api();
		$tournament_api = new Wp_Bracket_Builder_Bracket_Tournament_Api();
		$play_api = new Wp_Bracket_Builder_Bracket_Play_Api();
		$convert_api = new Wp_Bracket_Builder_Convert_Api();

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		$this->loader->add_action('admin_menu', $plugin_admin, 'bracket_builder_init_menu');
		$this->loader->add_action('init', $plugin_admin, 'add_capabilities');


		$this->loader->add_action('rest_api_init', $template_api, 'register_routes');
		$this->loader->add_action('rest_api_init', $tournament_api, 'register_routes');
		$this->loader->add_action('rest_api_init', $play_api, 'register_routes');
		$this->loader->add_action('rest_api_init', $convert_api, 'register_routes');

		$this->loader->add_action('init', $this, 'register_custom_post_types');
		$this->loader->add_action('init', $this, 'register_custom_post_status');

		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_bracket_pick_meta_box');
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_bracket_pick_img_urls_meta_box');

		$this->loader->add_filter('manage_bracket_pick_posts_columns', $plugin_admin, 'add_bracket_pick_columns');
		$this->loader->add_filter('manage_bracket_pick_posts_custom_column', $plugin_admin, 'show_bracket_pick_data', 10, 2);

		$this->loader->add_action('save_post', $plugin_admin, 'save_bracket_pick_html_meta_box');

		// custom meta for bracket product variations
		$this->loader->add_action('woocommerce_product_after_variable_attributes', $plugin_admin, 'variation_settings_fields', 10, 3);
		$this->loader->add_action('woocommerce_save_product_variation', $plugin_admin, 'save_variation_settings_fields', 10, 2);

		$this->loader->add_action('woocommerce_save_product_variation', $plugin_admin, 'validate_variation_fields', 10, 2);
		$this->loader->add_action('admin_notices', $plugin_admin, 'display_custom_admin_error');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp_Bracket_Builder_Public($this->get_plugin_name(), $this->get_version());
		$public_hooks = new Wp_Bracket_Builder_Public_Hooks();
		$shortcodes = new Wp_Bracket_Builder_Public_Shortcodes();

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		$this->loader->add_filter('woocommerce_add_to_cart_validation', $plugin_public, 'bracket_product_add_to_cart_validation', 10, 5);
		$this->loader->add_action('woocommerce_add_cart_item_data', $plugin_public, 'add_bracket_to_cart_item_data', 10, 3);
		$this->loader->add_action('woocommerce_checkout_create_order_line_item', $plugin_public, 'add_bracket_to_order_item', 10, 4);
		$this->loader->add_action('woocommerce_before_checkout_process', $plugin_public, 'handle_before_checkout_process');
		$this->loader->add_action('woocommerce_payment_complete', $plugin_public, 'handle_payment_complete');
		$this->loader->add_filter('woocommerce_available_variation', $plugin_public, 'filter_variation_availability', 10, 3);

		$this->loader->add_action('init', $public_hooks, 'add_rewrite_tags', 10, 0);
		$this->loader->add_action('init', $public_hooks, 'add_rewrite_rules', 10, 0);
		$this->loader->add_filter('query_vars', $public_hooks, 'add_query_vars');
		$this->loader->add_action('init', $public_hooks, 'add_roles');
		$this->loader->add_filter('posts_clauses', $public_hooks, 'sort_plays', 10, 2);
		$this->loader->add_filter('template_redirect', $public_hooks, 'print_redirect', 10);

		$this->loader->add_action('init', $shortcodes, 'add_shortcodes');
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
	 * @since     1.0.0
	 * @return    Wp_Bracket_Builder_Loader    Orchestrates the hooks of the plugin.
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
		register_post_type(
			'bracket_template',
			array(
				'labels' => array(
					'name' => __('Templates'),
					'singular_name' => __('Template'),
				),
				'description' => 'Bracket templates for the WP Bracket Builder plugin',
				'public' => true,
				'has_archive' => true,
				'supports' => array('title', 'author', 'thumbnail', 'custom-fields'),
				'show_ui' => true,
				'show_in_rest' => true, // Default endpoint for oxygen. React app uses Wp_Bracket_Builder_Bracket_Api
				// 'rest_controller_class' => 'Wp_Bracket_Builder_Bracket_Api',
				// 'rest_controller_class' => array($bracket_api, 'register_routes'),
				'taxonomies' => array('post_tag'),
				'rewrite' => array('slug' => 'templates'),
			)
		);

		register_post_type(
			'bracket_play',
			array(
				'labels' => array(
					'name' => __('Plays'),
					'singular_name' => __('Play'),
				),
				'description' => 'Bracket plays for the WP Bracket Builder plugin',
				'public' => true,
				'has_archive' => true,
				'supports' => array('title', 'author', 'thumbnail', 'custom-fields'),
				'show_ui' => true,
				'show_in_rest' => true,
				// 'rest_controller_class' => 'Wp_Bracket_Builder_Bracket_Api',
				// 'rest_controller_class' => array($bracket_api, 'register_routes'),
				'taxonomies' => array('post_tag'),
				'rewrite' => array('slug' => 'plays'),
			)
		);
		register_post_type(
			'bracket_tournament',
			array(
				'labels' => array(
					'name' => __('Tournaments'),
					'singular_name' => __('Tournament'),
				),
				'description' => 'Tournaments for the WP Bracket Builder plugin',
				'public' => true,
				'has_archive' => true,
				'supports' => array('title', 'author', 'thumbnail', 'custom-fields'),
				'show_ui' => true,
				'show_in_rest' => true,
				// 'rest_controller_class' => 'Wp_Bracket_Builder_Bracket_Api',
				// 'rest_controller_class' => array($bracket_api, 'register_routes'),
				'taxonomies' => array('post_tag'),
				'rewrite' => array('slug' => 'tournaments'),
			)
		);
	}

	public function register_custom_post_status() {

		// Custom post status for completed tournaments
		register_post_status("complete", array(
			'label' => 'Complete',
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Completed <span class="count">(%s)</span>', 'Complete <span class="count">(%s)</span>'),
		));

		register_post_status("score", array(
			'label' => 'Scored',
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Scored <span class="count">(%s)</span>', 'Scored <span class="count">(%s)</span>'),
		));

		register_post_status("archive", array(
			'label' => 'Archive',
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Completed <span class="count">(%s)</span>', 'Archive <span class="count">(%s)</span>'),
		));
	}
	public function sort_plays($clauses, $query_object) {
		print_r($clauses);
		echo 'HIIII';
		error_log('HIIII');
	}
}
