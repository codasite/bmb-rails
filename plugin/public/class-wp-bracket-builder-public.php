<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wp-bracket-builder-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wp-bracket-builder-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/domain/class-wp-bracket-builder-bracket-template.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/service/class-wp-bracket-builder-aws-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/service/class-wp-bracket-builder-pdf-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/domain/class-wp-bracket-builder-bracket-config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wp-bracket-builder-bracket-config-repo.php';

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
class Wp_Bracket_Builder_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
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
	private $tournament_repo;

	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->utils = new Wp_Bracket_Builder_Utils();
		$this->bracket_config_repo = new Wp_Bracket_Builder_Bracket_Config_Repository();
		$this->s3 = new Wp_Bracket_Builder_S3_Service();
		$this->lambda_service = new Wp_Bracket_Builder_Lambda_Service();
		$this->pdf_service = new Wp_Bracket_Builder_Pdf_Service();
		$this->tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
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
		 * defined in Wp_Bracket_Builder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Bracket_Builder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wp-bracket-builder-public.css', array(), $this->version, 'all');
		// wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css', array(), null, 'all');
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
	}
}
