<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wp-bracket-builder-bracket-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/domain/class-wp-bracket-builder-bracket.php';

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
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
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

		// wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wp-bracket-builder-public.css', array(), $this->version, 'all');
		wp_enqueue_style('index.css', plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-bracket-builder-public.js', array( 'jquery' ), $this->version, false );

		$post = get_post();
		$bracket_repo = new Wp_Bracket_Builder_Bracket_Repository();
		$bracket = $bracket_repo->get(post: $post);
		$css_file = plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.css';


		// For product page
		$product = wc_get_product($post->ID);

		function set_session_value($key, $value) {
			if (!session_id()) {
				session_start();
			}
			$_SESSION[$key] = $value;
		}

		// TODO: Replace with actual bracket url
		$bracket_url = 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/SNice.svg/1200px-SNice.svg.png';

		// Set bracket url in session
		set_session_value('bracket_url', $bracket_url);


		// Only get product details on product pages.
		if ($product) {
			$product_id = $product->get_id();
			$default_color = get_default_product_color($product);
			$variation_gallery_mapping = get_product_variation_galleries($product);
		}

		wp_enqueue_script('wpbb-bracket-builder-react', plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.js', array('wp-element'), $this->version, true);

		wp_localize_script(
			'wpbb-bracket-builder-react',
			'wpbb_ajax_obj',
			array(
				'nonce' => wp_create_nonce('wp_rest'),
				'page' => 'user-bracket',
				'ajax_url' => admin_url('admin-ajax.php'),
				'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
				'post' => $post,
				'bracket' => $bracket,
				'css_file' => $css_file,
				'variation_gallery_mapping' => $variation_gallery_mapping, // used for preview page
				'default_product_color' => $default_color, // used for preview page
				'product_id' => $product_id, // used for preview page
				'bracket_url' => $bracket_url, // used for preview page
			)
		);
	}

	/**
	 * Render bracket builder
	 *
	 * @return void
	 */
	public function render_bracket_builder() {
		ob_start();
?>
		<div id="wpbb-bracket-builder">
		</div>
<?php
		return ob_get_clean();
	}


	/**
	 * Add shortcode to render events
	 *
	 * @return void
	 */
	public function add_shortcodes() {
		add_shortcode('wpbb-bracket-builder', [$this, 'render_bracket_builder']);
	}
}


/**
 * Get the image ids for each variation
 *
 * @param WC_Product $product
 * @return array
 */
function get_product_variation_galleries($product) {
	// TODO: Add error handling

	// Check if the product is variable
	if ($product->is_type('variable')) {
		// Initialize an empty mapping array
		$variation_gallery_mapping = array();

		// Get the product variations
		$variations = $product->get_available_variations();

		foreach ($variations as $variation) {
			// Get the variation image ids
			$variation_image_ids = get_variation_image_ids($variation);

			// Get the variation image urls
			$variation_gallery_image_urls = get_image_urls($variation_image_ids);


			// Get the default variation color (should be set in admin panel)
			$variation_color = get_variation_color($variation);

			// Map variation_colors to gallery image urls
			// Note: I map from colors, not ids, because the color is what is
			// used for the select element, in the form, on the product page.
			$variation_gallery_mapping[$variation_color] = $variation_gallery_image_urls;
		}
	}
	return $variation_gallery_mapping;
}

/**
 * Get the default product color. This should be set in the admin panel.
 */
function get_default_product_color($product) {
	$defaults = $product->get_default_attributes();
	$default_color = $defaults['color'];
	return $default_color;
}

function get_variation_color($variation) {
	$variation_id = $variation['variation_id'];
	$variation_obj = wc_get_product($variation_id);

	$variation_data = $variation_obj->get_data();
	$variation_attributes = $variation_data['attributes'];
	$variation_color = $variation_attributes['color'];
	
	return $variation_color;
}

function get_image_urls($image_ids) {
	$image_urls = array();

	foreach ($image_ids as $imageId) {
		$imageSrc = wp_get_attachment_image_src($imageId, 'full');
		$imageUrl = $imageSrc[0];
		$image_urls[] = $imageUrl;
	}

	return $image_urls;
}


/**
 * Merge variation_image_id (default image) with gallery_image_ids
 * if variation_image_id is not already in gallery_image_ids
 *
 * @param int $variation_image_id
 * @param array $variation_gallery_image_ids
 * @return array
 */
function get_variation_image_ids($variation) {
	// There are various nested, protected values throughout the variation object,
	// so we need to go through this malarky to get the image ids.
	$variation_id = $variation['variation_id'];
	$variation_obj = wc_get_product($variation_id);

	$variation_data = $variation_obj->get_data();
	$variation_attributes = $variation_data['attributes'];
	$variation_color = $variation_attributes['color'];
	$variation_image_id = $variation_data['image_id']; // combine with variation_gallery_images_ids
	$variation_meta_data = $variation_data['meta_data'];
	$variation_current_data = $variation_meta_data[0]->get_data();
	$variation_gallery_image_ids = $variation_current_data['value'];

	// Merge variation_image_id (default image) with gallery_image_ids
	// if variation_image_id is not already in gallery_image_ids
	$merged_variation_gallery_image_ids = merge_gallery_images($variation_image_id, $variation_gallery_image_ids);

	return $merged_variation_gallery_image_ids;
}


/**
 * Merge the unmerged image id with the gallery image ids if it's not already in there.
 *
 * @param int $unmerged_image_id
 * @param array $gallery_images
 * @return array
 */
function merge_gallery_images($unmerged_image_id, $gallery_image_ids) {
	// Copy the gallery images array
	$gallery_image_ids_copy = array();

	foreach ($gallery_image_ids as $gallery_image_id) {
		array_push($gallery_image_ids_copy, $gallery_image_id);
	}

	// Merge the unmergeed image id with the gallery images if it's not already in there
	if (!in_array($unmerged_image_id, $gallery_image_ids_copy)) {
		$gallery_image_ids_copy = array_merge(array($unmerged_image_id),$gallery_image_ids_copy);
	}
	return $gallery_image_ids_copy;
}

// Add the bracket url to the cart item data
function add_bracket_to_cart_item($cart_item_data, $product_id, $variation_id) {
	$bracket_url = $_GET['bracket_url']; // get bracket url from query params

	$cart_item_data['bracket_url'] = $bracket_url;
	return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_bracket_to_cart_item',10,3);


// Get value from user session
function get_session_value($key) {
	if (!session_id()) {
		session_start();
	}
	if (isset($_SESSION[$key])) {
		return $_SESSION[$key];
	}
	return null;
}

// Add the bracket url to the order
function add_bracket_to_order($item, $cart_item_key, $values, $order) {
	if (empty($values)) {
		return;
	}

	// Get bracket url from session
	$bracket_url = get_session_value('bracket_url');
	$item->add_meta_data('bracket_url', $bracket_url );
}
add_action('woocommerce_checkout_create_order_line_item','add_bracket_to_order',10,4);

