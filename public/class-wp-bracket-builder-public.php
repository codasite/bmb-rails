<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wp-bracket-builder-bracket-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/domain/class-wp-bracket-builder-bracket.php';
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

	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->utils = new Wp_Bracket_Builder_Utils();
		$this->bracket_config_repo = new Wp_Bracket_Builder_Bracket_Config_Repository();
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

		// get the bracket config for light and dark mode from the session
		$bracket_config_light = $this->bracket_config_repo->get('light');
		$bracket_config_dark = $this->bracket_config_repo->get('dark');
		$bracket_url_theme_map = array(
			'light' => $bracket_config_light ? $bracket_config_light->img_url : '',
			'dark' => $bracket_config_dark ? $bracket_config_dark->img_url : '',
		);

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

				// For product page
				'bracket_url_theme_map' => $bracket_url_theme_map, // map of theme mode to bracket image url
				'gallery_images' => $gallery_images,
				'color_options' => $color_options,
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

	// Validate bracket product data before adding to cart
	// Hooks into woocommerce_add_to_cart_validation
	public function bracket_product_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = null, $variations = null ) {
		$product = wc_get_product($product_id);

		if (!$this->is_bracket_product($product)) {
			return $passed;
		}

		$configs = $this->bracket_config_repo->get_all();

		if (empty($configs)) {
			return $passed;
		}

		$bracket_theme = $this->get_bracket_theme($variation_id);

		if (empty($bracket_theme)) {
			$this->log_and_show_error($product, $variation_id, $product_id, 'No bracket theme found.');
			return false;
		}

		$config = $configs[$bracket_theme];

		if (empty($config)) {
			$this->log_and_show_error($product, $variation_id, $product_id, 'No bracket config found.');
			return false;
		}

		return $passed;
	}

	public function add_bracket_to_cart_item_data($cart_item_data, $product_id, $variation_id) {
		$product = wc_get_product($product_id);

		if (!$this->is_bracket_product($product)) {
			return $cart_item_data;
		}

		$configs = $this->bracket_config_repo->get_all();

		if (empty($configs)) {
			return $cart_item_data;
		}

		$bracket_theme = $this->get_bracket_theme($variation_id);

		$config = $configs[$bracket_theme];
		$cart_item_data['bracket_config'] = $config;

		return $cart_item_data;
	}

	// Helper method to check if product is a bracket product
	private function is_bracket_product($product) {
		return $this->product_has_category($product, BRACKET_PRODUCT_CATEGORY);
	}

	// Helper method to get the bracket theme
	private function get_bracket_theme($variation_id) {
		return get_post_meta($variation_id, 'wpbb_bracket_theme', true);
	}

	// Helper method to log error and show notice
	private function log_and_show_error($product, $variation_id, $product_id, $error_message) {
		$product_name = $product->get_name();
		$error = array(
			'error' => 'Error adding item to cart. ' . $error_message,
			'product_name' => $product_name,
			'variation_id' => $variation_id,
			'product_id' => $product_id,
		);
		$event_id = $this->utils->log_sentry_message(json_encode($error), \Sentry\Severity::error());
		wc_add_notice(__('Error adding item to cart. Please contact the site administrator. Event ID: ' . $event_id, 'wp-bracket-builder'), 'error');
	}

	// Add the bracket config to the order line item data when the order is created
	public function add_bracket_to_order_item($item, $cart_item_key, $values, $order) {
		if (array_key_exists('bracket_config', $values)) {
			$item->add_meta_data('bracket_config', $values['bracket_config']);
		}
	}

	// this function hooks into woocommerce_payment_complete
	public function handle_payment_complete($order_id) {
		$order = wc_get_order($order_id);
		if ($order) {
			$items = $order->get_items();
			foreach ($items as $item) {
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
		$bracket_config = $item->get_meta('bracket_config');
		// throw error if config not found?
		// $item_arr['config'] = $bracket_config;
		$bracket_theme = $bracket_config->theme_mode;
		$html = $bracket_config->html;

		// Generate a PDF file for the back design (the bracket)
		// We don't reuse the png from the product preview because only a PDF can supply Gelato with multiple designs
		$convert_req = array(
			'inchHeight' => 16,
			'inchWidth' => 12,
			'pdf' => true,
			'html' => $html,
		);
		$lambda_service = new Wp_Bracket_Builder_Lambda_Service();
		$convert_res = $lambda_service->html_to_image($convert_req);
		// check if convert res is wp_error
		if (!isset($convert_res['imageUrl']) || empty($convert_res['imageUrl'])) {
			// throw error
		}
		$back_url = $convert_res['imageUrl'];
		$item_arr['back_url'] = $back_url;

		// get the url for the front design
		// get the product variation for the order item
		$variation = $item->get_product();
		$front_url = get_post_meta($variation->get_id(), 'wpbb_front_design', true);
		if (empty($front_url)) {
			// handle empty
		}

		$item_arr['front_url'] = $front_url;

		$order_filename = $this->get_gelato_order_filename($order, $item);
		$item_arr['order_filename'] = $order_filename;

		// merge pdfs
		$pdf_service = new Wp_Bracket_Builder_PDF_Service();
		$s3 = new Wp_Bracket_Builder_S3_Service();
		$front = $s3->get_from_url($front_url);
		$back = $s3->get_from_url($back_url);
		$merged = $pdf_service->merge_from_string($front, $back);
		$result = $s3->put(BRACKET_BUILDER_S3_ORDER_BUCKET, $order_filename, $merged);
		$item_arr['order-result'] = $result;
		

		// $item_arr['bracket_theme'] = $bracket_theme;
		$item_arr['order_id'] = $order->get_id();
		// $item_arr['data'] = $item->get_data();
		// Get all metadata
		$metadata = $item->get_formatted_meta_data();

		// Filter out bracket_config
		$filtered_meta = array_filter($metadata, function($meta) {
				return $meta->key !== 'bracket_config';
		});

		$item_arr['meta'] = $filtered_meta;
		$item_arr['item_id'] = $item->get_id();



		// $s3_service = new Wp_Bracket_Builder_S3_Service();
		// $source_key = $s3_service->extract_key_from_url($bracket_url);
		// // Copy bracket image to gelato bucket
		// $s3_service->copy('wpbb-gelato-orders', $s3_filename, 'wpbb-bracket-images', $source_key);

		// $item_arr['config'] = $bracket_config;
		$this->utils->log_sentry_message(json_encode($item_arr));
	}

	private function get_front_print_area($product) {
	}

	private function get_variation_attribute_value($variation, $attribute_name) {
		$attributes = $variation->get_attributes();
		if (!array_key_exists($attribute_name, $attributes)) {
			return null;
		}
		$attribute = $attributes[$attribute_name];
		$attribute_value = $attribute;
		return $attribute_value;
	}

	private function get_gelato_order_filename($order, $item) {
		$order_id = $order->get_id();
		$item_id = $item->get_id();
		$filename = $order_id . '_' . $item_id . '.pdf';
		return $filename;
	}

	private function product_has_category($product, $category_slug) {
		if ($product->is_type('variation')) {
			return has_term($category_slug, 'product_cat', $product->get_parent_id());
		} else {
			return has_term($category_slug, 'product_cat', $product->get_id());
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
