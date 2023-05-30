<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wp-bracket-builder-bracket-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/domain/class-wp-bracket-builder-bracket.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-bracket-builder-utils.php';

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
		wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css', array(), null, 'all');
		wp_enqueue_style('index.css', plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.css', array(), null, 'all');
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
		$sentry_env = (defined('WP_SENTRY_ENV')) ? WP_SENTRY_ENV : 'production';
		$sentry_dsn = (defined('WP_SENTRY_PHP_DSN')) ? WP_SENTRY_PHP_DSN : '';

		$post = get_post();
		$bracket_repo = new Wp_Bracket_Builder_Bracket_Repository();
		$bracket = $bracket_repo->get(post: $post);
		$css_file = plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.css';



		// For product page
		$product = wc_get_product($post->ID);
		$bracket_product_archive_url = $this->get_archive_url();


		// // TODO: Replace with actual bracket url
		// $bracket_url = 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/SNice.svg/1200px-SNice.svg.png';

		// // Set bracket url in session
		// set_session_value('bracket_url', $bracket_url);
		$utils = new Wp_Bracket_Builder_Utils();
		$bracket_url = $utils->get_session_value('bracket_url');


		$is_bracket_product = $product && $this->product_has_category($product, 'bracket-ready');
		// Only get product details on product pages.
		$gallery_images = $is_bracket_product ? get_product_gallery($product) : array();
		$color_options = $is_bracket_product ? $this->get_attribute_options($product, 'color') : array();

		wp_enqueue_script('wpbb-bracket-builder-react', plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.js', array('wp-element'), $this->version, true);

		wp_localize_script(
			'wpbb-bracket-builder-react',
			'wpbb_ajax_obj',
			array(
				'sentry_env' => $sentry_env,
				'sentry_dsn' => $sentry_dsn,
				'nonce' => wp_create_nonce('wp_rest'),
				'page' => 'user-bracket',
				'ajax_url' => admin_url('admin-ajax.php'),
				'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
				'post' => $post,
				'bracket' => $bracket,
				'css_file' => $css_file,
				'bracket_product_archive_url' => $bracket_product_archive_url, // used to redirect to bracket-ready category page

				'bracket_url' => $bracket_url, // used for preview page
				'gallery_images' => $gallery_images, // used for preview page
				'color_options' => $color_options, // used for preview page
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
	 * Render the bracket preview
	 * 
	 * @return void
	 */
	public function render_bracket_preview() {
		ob_start();
	?>
		<div id="wpbb-bracket-preview-controller" style="width: 100%">
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
		add_shortcode('wpbb-bracket-preview', [$this, 'render_bracket_preview']);
	}

	public function get_archive_url() {
		$category_slug = 'bracket-ready';
		$redirect_url = get_term_link($category_slug, 'product_cat');
		return $redirect_url;
	}

	// get all attribute options for a product
	public function get_attribute_options(mixed $product, string $attribute_name) {
		$attributes = $product->get_attributes();
		if (!array_key_exists($attribute_name, $attributes)) {
			return array();
		}
		$attribute = $attributes[$attribute_name];
		$attribute_options = $attribute->get_options();
		return $attribute_options;
	}

	// Add the bracket url to the cart item data
	// This method should be attached to the woocommerce_add_cart_item_data filter
	public function add_bracket_to_cart_item_data($cart_item_data, $product_id, $variation_id) {
		$product = wc_get_product($product_id);
		if ($this->product_has_category($product, 'bracket-ready')) {
			$utils = new Wp_Bracket_Builder_Utils();
			$bracket_url = $utils->get_session_value('bracket_url');
			$cart_item_data['bracket_url'] = $bracket_url;
		}
		return $cart_item_data;
	}

	// Add the bracket url to the order line item data when the order is created
	public function add_bracket_to_order_item( $item, $cart_item_key, $values, $order ) {
    if ( array_key_exists( 'bracket_url', $values ) ) {
        $item->add_meta_data( 'bracket_url', $values['bracket_url'] );
    }
	}

	public function handle_payment_complete($order_id) {
    $order = wc_get_order($order_id);
    if( $order ){
        $items = $order->get_items();
        foreach ( $items as $item ) {
						$product = $item->get_product();
						$is_bracket_product = $this->product_has_category($product, 'bracket-ready');
						if ($is_bracket_product) {
							$this->handle_bracket_product_item($order, $item);
						}
        }
    }
	}

	private function handle_bracket_product_item($order, $item) {
		$item_arr = array();
		$bracket_url = $item->get_meta( 'bracket_url', true );
		$item_arr['bracket_url'] = $bracket_url;
		$item_arr['order_id'] = $order->get_id();
		$item_arr['data'] = $item->get_data();
		$item_arr['meta'] = $item->get_meta_data();
		$item_arr['item_id'] = $item->get_id();
    $utils = new Wp_Bracket_Builder_Utils();
    $utils->log_sentry_message(json_encode($item_arr));
	}

	private function build_attachment_filename($order, $item) {
		$order_id = $order->get_id();
		$item_id = $item->get_id();
	}

	private function product_has_category($product, $category_slug) {
		if ($product->is_type('variation')) {
			return has_term( $category_slug, 'product_cat', $product->get_parent_id() );
		} else {
			return has_term( $category_slug, 'product_cat', $product->get_id() );
		}
	}
}

/**
 * Get all gallery images for the product
 * 
 * @param WC_Product $product
 * @return array
 */

function get_product_gallery($product) {
	// get all gallery images for the product
	$attachment_ids = $product->get_gallery_image_ids();
	$gallery_images = get_images($attachment_ids);
	return $gallery_images;
}

function get_images($image_ids) {
	$images = array();

	foreach ($image_ids as $imageId) {
		// $imageSrc = wp_get_attachment_image_src($imageId, 'full');
		// $imageUrl = $imageSrc[0];
		// $image_urls[] = $imageUrl;
		$image_attrs = array(
			'src' => wp_get_attachment_url($imageId),
			'title' => get_the_title($imageId),
		);
		$images[] = $image_attrs;
	}

	return $images;
}

// // Add the bracket url to the cart item data
// function add_bracket_to_cart_item($cart_item_data, $product_id, $variation_id) {
// 	$bracket_url = $_GET['bracket_url']; // get bracket url from query params

// 	$cart_item_data['bracket_url'] = $bracket_url;
// 	return $cart_item_data;
// }
// add_filter('woocommerce_add_cart_item_data', 'add_bracket_to_cart_item', 10, 3);



// // Add the bracket url to the order
// function add_bracket_to_order($item, $cart_item_key, $values, $order) {
// 	if (empty($values)) {
// 		return;
// 	}

// 	// Get bracket url from session
// 	$bracket_url = get_session_value('bracket_url');
// 	$item->add_meta_data('bracket_url', $bracket_url);
// }
// add_action('woocommerce_checkout_create_order_line_item', 'add_bracket_to_order', 10, 4);
