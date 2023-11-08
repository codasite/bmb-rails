<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/domain/class-wpbb-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/service/class-wpbb-aws-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/service/class-wpbb-pdf-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/domain/class-wpbb-bracket-config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wpbb-bracket-config-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/bracket-product/class-wpbb-bracket-product-utils.php';

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
class Wpbb_Public {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	private $utils;
	private $bracket_config_repo;
	private $s3;
	private $pdf_service;
	private $lambda_service;

	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->utils = new Wpbb_Utils();
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
		 * defined in Wpbb_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpbb_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpbb-public.css', array(), $this->version, 'all');
		wp_enqueue_style('index.css', plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/wordpress/index.css', array(), null, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script('tailwind', 'https://cdn.tailwindcss.com', array(), $this->version, false);
		wp_enqueue_script('wpbb-bracket-builder-react', plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/wordpress/index.js', array('wp-element'), $this->version, true);

		$sentry_env = (defined('WP_SENTRY_ENV')) ? WP_SENTRY_ENV : 'production';
		$sentry_dsn = (defined('WP_SENTRY_PHP_DSN')) ? WP_SENTRY_PHP_DSN : '';

		wp_localize_script(
			'wpbb-bracket-builder-react',
			'wpbb_app_obj',
			array(
				'sentry_env' => $sentry_env,
				'sentry_dsn' => $sentry_dsn,
				'nonce' => wp_create_nonce('wp_rest'),
				'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
				'my_brackets_url' => get_permalink(get_page_by_path('dashboard')) . '?tab=brackets',
				'bracket_builder_url' => get_permalink(get_page_by_path('bracket-builder')),
				'user_can_share_bracket' => current_user_can('wpbb_share_bracket') ? true : false,
				'upgrade_account_url' => $this->get_bmb_plus_permalink(),
				'bracket_product_archive_url' => $this->get_bracket_product_archive_url(),
				'play_history_url' => get_permalink(get_page_by_path('dashboard')) . '?tab=play-history',
			));
	}

	private function get_bmb_plus_permalink() {
		// use wp query to get the post for the bmb subscription
		$args = array(
			'name'        => 'bmb-plus',
			'post_type'   => 'product',
			'post_status' => 'publish',
			'numberposts' => 1
		);
		$bmb_plus_post = get_posts($args)[0];
		return get_permalink($bmb_plus_post->ID);
	}

	private function get_bracket_product_archive_url() {
		$bracket_product_utils = new Wpbb_BracketProductUtils();
		return $bracket_product_utils->get_bracket_product_archive_url();
	}
}
