<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://github.com/barrymolina
 * @since      1.0.0
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/admin
 */

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
class Wp_Bracket_Builder_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
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

		// wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wp-bracket-builder-admin.css', array(), $this->version, 'all');
		wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css', array(), null, 'all');
		wp_enqueue_style('index.css', plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.css', array(), null, 'all');
		// enqueue bootstrap
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wp-bracket-builder-admin.js', array('jquery'), $this->version, false);

		wp_enqueue_script('wpbb-admin-panel-react', plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.js', array('wp-element'), $this->version, true);

		wp_localize_script(
			'wpbb-admin-panel-react',
			'wpbb_ajax_obj',
			array(
				'nonce' => wp_create_nonce('wp_rest'),
				'page' => 'settings',
				'ajax_url' => admin_url('admin-ajax.php'),
				'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
			)
		);
	}
	public function bracket_builder_init_menu() {
		add_menu_page(__('Bracket Builder', 'bracketbuilder'), __('Bracket Builder', 'bracketbuilder'), 'manage_options', 'bracketbuilder', array($this, 'bracket_builder_admin_page'), 'dashicons-admin-post', '2.1');
	}

	public function bracket_builder_admin_page() {
		require_once plugin_dir_path(__FILE__) . 'templates/admin-panel.php';
	}

	public function add_capabilities() {
		$role = get_role('administrator');
		$role->add_cap('manage_bracket_builder');
	}

	//  add a custom text field for the bmb-logo-theme in the admin product variation settings
	// Attach to `woocommerce_product_after_variable_attributes` hook
	public function variation_settings_fields($loop, $variation_data, $variation) {
    // woocommerce_wp_text_input(
    //     array(
    //         'id'          => 'wpbb_logo_theme[' . $variation->ID . ']',
    //         'label'       => __('BMB Logo Theme', 'woocommerce'),
    //         'desc_tip'    => 'true',
    //         'description' => __('The theme used for the BMB logo', 'woocommerce'),
    //         'value'       => get_post_meta($variation->ID, 'wpbb_logo_theme', true)
    //     )
    // );
		woocommerce_wp_select(
			array(
					'id'            => 'wpbb_logo_theme[' . $variation->ID . ']',
					'label'         => __('BMB Logo Theme', 'woocommerce'),
					'description'   => __('The theme used for the BMB logo', 'woocommerce'),
					'desc_tip'      => 'true',
					'value'         => get_post_meta($variation->ID, 'wpbb_logo_theme', true),
					'options'       => array(
							'light'  => __('Light', 'woocommerce'),
							'dark'  => __('Dark', 'woocommerce'),
					)
			)
	);
	}

	// save the value of this field when the product variation is saved
	// Attach to `woocommerce_save_product_variation` hook
	function save_variation_settings_fields($variation_id, $i) {
		if(isset($_POST['wpbb_logo_theme'][$variation_id])) {
			$custom_field = $_POST['wpbb_logo_theme'][$variation_id];
			update_post_meta($variation_id, 'wpbb_logo_theme', esc_attr($custom_field));
	}
	}
}
