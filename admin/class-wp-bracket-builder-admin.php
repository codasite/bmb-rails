<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-bracket-builder-utils.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wp-bracket-builder-bracket-config-repo.php';

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

	private $config_repo;
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->config_repo = new Wp_Bracket_Builder_Bracket_Config_Repository();
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
		add_submenu_page('bracketbuilder', 'Bracket Builder Settings', 'Settings', 'manage_options', 'bracket-builder-settings', array($this, 'bracket_builder_settings_page'));
	}

	public function bracket_builder_admin_page() {
		require_once plugin_dir_path(__FILE__) . 'templates/admin-panel.php';
	}
	
	public function bracket_builder_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title() ?></h1>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'bracket-builder-settings' ); // settings group name
					do_settings_sections( 'bracket-builder-settings-page' ); // just a page slug
					submit_button(); // "Save Changes" button
					?>
			</form>
		</div>
		<?php
	}

	public function add_capabilities() {
		$role = get_role('administrator');
		$role->add_cap('manage_bracket_builder');
	}

	//  add a custom text field for the bmb-logo-theme in the admin product variation settings
	// Attach to `woocommerce_product_after_variable_attributes` hook
	public function variation_settings_fields($loop, $variation_data, $variation) {
		// Get the parent product
		$parent_product_id = wp_get_post_parent_id($variation->ID);

		// Check if the parent product has the 'bracket-ready' category
		if (has_term(BRACKET_PRODUCT_CATEGORY, 'product_cat', $parent_product_id)) {
			$front_design_value = get_post_meta($variation->ID, 'wpbb_front_design', true);
			$bracket_theme_value = get_post_meta($variation->ID, 'wpbb_bracket_theme', true);

			// Text input for front design url
			woocommerce_wp_text_input(
				array(
					'id'            => 'wpbb_front_design[' . $variation->ID . ']',
					'label'         => __('Front design URL', 'woocommerce'),
					'description'   => __('The design to print on the front of this product, in PDF format. Typically an S3 object URL.', 'woocommerce'),
					'desc_tip'      => 'true',
					'value'         => $front_design_value,
				)
			);
			// Select input for bracket theme
			woocommerce_wp_select(
				array(
					'id'            => 'wpbb_bracket_theme[' . $variation->ID . ']',
					'label'         => __('Bracket theme', 'woocommerce'),
					'description'   => __('The bracket theme to be used on this variation', 'woocommerce'),
					'desc_tip'      => 'true',
					'value'         => $bracket_theme_value,
					'options'       => array(
						'' => __('Choose Theme', 'woocommerce'),
						'dark' => __('Dark', 'woocommerce'),
						'light' => __('Light', 'woocommerce'),
					),
				)
			);
		}
	}

	// save the value of this field when the product variation is saved
	// Attach to `woocommerce_save_product_variation` hook
	public function save_variation_settings_fields($variation_id, $i) {
		if (isset($_POST['wpbb_front_design'][$variation_id])) {
			$front_design = $_POST['wpbb_front_design'][$variation_id];
			update_post_meta($variation_id, 'wpbb_front_design', esc_attr($front_design));
		}
		if (isset($_POST['wpbb_bracket_theme'][$variation_id])) {
			$bracket_theme = $_POST['wpbb_bracket_theme'][$variation_id];
			update_post_meta($variation_id, 'wpbb_bracket_theme', esc_attr($bracket_theme));
		}
	}

	public function validate_variation_fields($variation_id, $i) {
		// Check for Front Design URL
		if (empty(get_post_meta($variation_id, 'wpbb_front_design', true))) {
			update_option('custom_admin_error', 'WARNING: Front Design URL is blank for variation ID ' . $variation_id . '. Customer will be unable to add this product to their cart.');
		}

		// Check for Bracket Theme
		if (empty(get_post_meta($variation_id, 'wpbb_bracket_theme', true))) {
			update_option('custom_admin_error', 'WARNING: Bracket theme is blank for variation ID ' . $variation_id . '. Customer will be unable to customize this product.');
		}
	}

	// Display the custom error message
	// hooked to `admin_notices` action hook
	public function display_custom_admin_error() {
		$message = get_option('custom_admin_error');
		if ($message) {
			echo '<div class="error notice">
	          <p>' . $message . '</p>
	      </div>';
			delete_option('custom_admin_error');
		}
	}

	public function add_bracket_pick_meta_box() {
		add_meta_box(
			'bracket_pick_html_meta_box', // id of the meta box
			'Bracket HTML', // title
			array($this, 'display_bracket_pick_html_meta_box'), // callback function that will echo the box content
			'bracket_pick', // post type where to add it
			'normal', // position
			'high' // priority
		);
	}


	// Meta box content
	public function display_bracket_pick_html_meta_box($post) {
		$html = get_post_meta($post->ID, 'bracket_pick_html', true);
		wp_nonce_field('bracket_pick_html_nonce', 'bracket_pick_html_nonce_field');
		// echo '<label for="bracket_pick">Prediction</label>';
		// echo '<input type="text" id="bracket_pick" name="bracket_pick" value="' . esc_attr($pick) . '">';
		echo '<textarea id="bracket_pick_html" name="bracket_pick_html" rows="20" style="width:100%;" >' . esc_attr($html) . '</textarea>';
	}

	// Save meta box content
	public function save_bracket_pick_html_meta_box($post_id) {
		// Verify nonce
		if (!isset($_POST['bracket_pick_html_nonce_field']) || !wp_verify_nonce($_POST['bracket_pick_html_nonce_field'], 'bracket_pick_html_nonce')) {
			return $post_id;
		}
		// Check the user's permissions.
		if (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}
		// Save/Update the meta field in the database.
		// update_post_meta($post_id, 'bracket_pick_html', sanitize_text_field($_POST['bracket_pick_html']));
		update_post_meta($post_id, 'bracket_pick_html', wp_kses_post($_POST['bracket_pick_html']));
	}

	public function add_bracket_pick_img_urls_meta_box() {
		add_meta_box(
			'bracket_pick_img_urls_meta_box', // id of the meta box
			'Bracket Image URLs', // title
			array($this, 'display_bracket_pick_images_meta_box'), // callback function that will echo the box content
			'bracket_pick', // post type where to add it
			'normal', // position
			'high' // priority
		);
	}

	public function display_bracket_pick_images_meta_box($post) {
		$urls = get_post_meta($post->ID, 'bracket_pick_images', true);
		wp_nonce_field('bracket_pick_images_nonce', 'bracket_pick_images_nonce_field');
		// echo '<label for="bracket_pick_images">Image URLs</label>';
		// echo '<input type="text" id="bracket_pick_images" name="bracket_pick_images" value="' . esc_attr($urls) . '" style="width:100%;">';
		ob_start();
?>
		<table id="bracket_pick_images_table" class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="bracket_pick_images">Image URLs</label>
					</th>
					<td>
						<input type="text" id="bracket_pick_images" name="bracket_pick_images" value="<?php echo esc_attr($urls); ?>">
					</td>
				</tr>
			</tbody>
		</table>
<?php
		echo ob_get_clean();
	}

	public function save_bracket_pick_images_meta_box($post_id) {
		// Verify nonce
		if (!isset($_POST['bracket_pick_images_nonce_field']) || !wp_verify_nonce($_POST['bracket_pick_images_nonce_field'], 'bracket_pick_images_nonce')) {
			return $post_id;
		}
		// Check the user's permissions.
		if (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}
		// Save/Update the meta field in the database.
		update_post_meta($post_id, 'bracket_pick_images', sanitize_text_field($_POST['bracket_pick_images']));
	}

	public function add_bracket_pick_columns($columns) {
		$columns['author'] = 'Author';
		return $columns;
	}

	public function show_backet_pick_data($column, $post_id) {
		if ('author' === $column) {
			$author_id = get_post_field('post_author', $post_id);
			$author_name = get_the_author_meta('display_name', $author_id);
			echo $author_name;
		}
	}
}
?>
<?php
/* Bracket builder settings page related code */

add_action( 'admin_init',  'bracket_builder_settings_fields' );

function bracket_builder_settings_fields(){
    // settings section
	add_settings_section(
        'bracket_builder_setting_section_id',  // Custom slug for the setting section.
        '', // Setting section title.
        '',
		'bracket-builder-settings-page' // The page slug that we want to add our settings section.

    );

    // settings fields
	add_settings_field(
		'bracket_builder_setting_field_id', // Custom slug for the setting field.
		'Max teams', // Setting the field title.
		'show_bracket_builder_settings_fields', // Callback function that adds markups to the settings section. 
		'bracket-builder-settings-page', // The page slug of which we want to show setting field on it.
		'bracket_builder_setting_section_id' // The section that we want to show setting field under it.
	 );

	 register_setting( 'bracket-builder-settings', // group name
		'bracket_builder_max_teams', // field name (column name) to be create in database
		'absint'  // type of data (absint converts a value in non-negative integer)
	);

}

function show_bracket_builder_settings_fields() {
    ?>
	<?php settings_errors(); ?>
    <input type="number" id="bracket_builder_max_teams" name="bracket_builder_max_teams" value=<?php echo get_option('bracket_builder_max_teams'); ?> />
    <?php
}