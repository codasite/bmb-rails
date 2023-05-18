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

		$product = wc_get_product($post->ID);
		if ($product) {
			$defaults = $product->get_default_attributes();
			$default_color = $defaults['color'];
			$variation_gallery_mapping = get_product_variation_galleries($product);
		}


		wp_enqueue_script('wpbb-bracket-builder-react', plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.js', array('wp-element'), $this->version, true);

		wp_localize_script(
			'wpbb-bracket-builder-react',
			'wpbb_ajax_obj',
			array(
				'nonce' => wp_create_nonce('wpbb-nonce'),
				'page' => 'user-bracket',
				'ajax_url' => admin_url('admin-ajax.php'),
				'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
				'post' => $post,
				'bracket' => $bracket,
				'variation_gallery_mapping' => $variation_gallery_mapping,
				'default_color' => $default_color,
				// Get bracket url from query params
				// 'bracket_url' => $_GET['bracket_url'],
				// For testing:
				//'bracket_url' => 'https://w0.peakpx.com/wallpaper/86/891/HD-wallpaper-smiley-face-cg-smiley-colors-black-yellow-graffiti-abstract-3d-face.jpg',
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
			// There are various nested protected values throughout the variation object,
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

			// Merge image_id with gallery_image_ids (if not already in there)
			$variation_gallery_image_ids_copy = array();
			foreach ($variation_gallery_image_ids as $variation_gallery_image_id) {
				array_push($variation_gallery_image_ids_copy, $variation_gallery_image_id);
			}

			if (!in_array($variation_image_id, $variation_gallery_image_ids_copy)) {
				$variation_gallery_image_ids_copy = array_merge(array($variation_image_id),$variation_gallery_image_ids_copy);
			}

			// Get the variation gallery image urls
			$variation_gallery_image_urls = array();

			foreach ($variation_gallery_image_ids_copy as $imageId) {
				$imageSrc = wp_get_attachment_image_src($imageId, 'full');
				$imageUrl = $imageSrc[0];
				$variation_gallery_image_urls[] = $imageUrl;
			}

			// Map variation_ids to gallery image urls
			$variation_gallery_mapping[$variation_color] = $variation_gallery_image_urls;
		}
	}
	return $variation_gallery_mapping;
}

// function get_default_variation_id($product) {
// 	// TODO: Error handling and not default check


// 	// Check if the product is variable
// 	if ($product->is_type('variable')) {
// 		// Get the product variations
// 		$variations = $product->get_available_variations();

// 		foreach ($variations as $variation) {
// 			$variation_id = $variation['variation_id'];
// 			$variation_obj = wc_get_product($variation_id);
// 			$variation_data = $variation_obj->get_data();
// 			$variation_attributes = $variation_data['attributes'];
// 			$variation_color = $variation_attributes['color'];
// 			if ($variation_color == $default_color) {
// 				echo 'variation_id: ' . $variation_id . '<br>';
// 				return $variation_id;
// 			}
// 		}
// 	}
// }