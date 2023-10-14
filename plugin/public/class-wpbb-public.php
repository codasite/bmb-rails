<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/domain/class-wpbb-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/service/class-wpbb-aws-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/service/class-wpbb-pdf-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/domain/class-wpbb-bracket-config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wpbb-bracket-config-repo.php';

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

		$sentry_env = (defined('WP_SENTRY_ENV')) ? WP_SENTRY_ENV : 'production';
		$sentry_dsn = (defined('WP_SENTRY_PHP_DSN')) ? WP_SENTRY_PHP_DSN : '';

		// For product page
		// $product = wc_get_product($post->ID);
		$product = wc_get_product(0);
		$bracket_product_archive_url = $this->get_archive_url();

		$bracket_placement = $this->get_bracket_placement($product);

		$is_bracket_product = $this->is_bracket_product($product);
		// Only get product details on product pages.
		$gallery_images = $is_bracket_product ? $this->get_product_gallery($product) : array();
		$color_options = $is_bracket_product ? $this->get_attribute_options($product, 'color') : array();
		$overlay_map = $is_bracket_product ? $this->build_overlay_map($bracket_placement) : array();

		wp_enqueue_script('wpbb-bracket-builder-react', plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/wordpress/index.js', array('wp-element'), $this->version, true);

		// wp_localize_script(
		// 	'wpbb-bracket-builder-react',
		// 	'wpbb_ajax_obj',
		// 	array(
		// 		'sentry_env' => $sentry_env,
		// 		'sentry_dsn' => $sentry_dsn,
		// 		'nonce' => wp_create_nonce('wp_rest'),
		// 		'page' => 'user-bracket',
		// 		'ajax_url' => admin_url('admin-ajax.php'),
		// 		'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
		// 		'post' => $post,
		// 		'bracket' => $bracket,
		// 		'css_file' => $css_file,
		// 		'bracket_product_archive_url' => $bracket_product_archive_url, // used to redirect to bracket-ready category page

		// 		// For product page
		// 		'bracket_url_theme_map' => $overlay_map, // map of theme mode to bracket image url
		// 		'gallery_images' => $gallery_images,
		// 		'color_options' => $color_options,
		// 	)
		// );
	}

	private function build_overlay_map($placement): array {
		$dark = $this->bracket_config_repo->get('dark', $placement);
		$light = $this->bracket_config_repo->get('light', $placement);

		$overlay_map = array(
			'dark' => $dark->img_url,
			'light' => $light->img_url,
		);

		return $overlay_map;
	}
	// private function build_overlay_map(): array {
	// 	$dark_top = $this->bracket_config_repo->get('dark', 'top');
	// 	$dark_center = $this->bracket_config_repo->get('dark', 'center');
	// 	$light_top = $this->bracket_config_repo->get('light', 'top');
	// 	$light_center = $this->bracket_config_repo->get('light', 'center');

	// 	$bracket_url_theme_map = array(
	// 		'dark' => array(
	// 			'top' => $dark_top->img_url,
	// 			'center' => $dark_center->img_url,
	// 		),
	// 		'light' => array(
	// 			'top' => $light_top->img_url,
	// 			'center' => $light_center->img_url,
	// 		),
	// 	);

	// 	return $bracket_url_theme_map;
	// }

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
	public function bracket_product_add_to_cart_validation($passed, $product_id, $quantity, $variation_id = null, $variations = null) {
		$product = wc_get_product($product_id);

		if (!$this->is_bracket_product($product) || $this->bracket_config_repo->is_empty()) {
			// Not a bracket product. Treat as a normal product.
			return $passed;
		}

		// The bracket theme must be set in the variation meta data.
		// Errors out if this is not set. NOTE: The preview can still render the correct theme even if this is not set.
		// This is because the preview obtains the bracket theme from the image title.
		$bracket_theme = $this->get_bracket_theme($variation_id);

		if (empty($bracket_theme)) {
			$this->handle_add_to_cart_error($product, $variation_id, $product_id, 'No bracket theme found.');
			return false;
		}

		$bracket_placement = $this->get_bracket_placement($product);

		if (empty($bracket_placement)) {
			$this->handle_add_to_cart_error($product, $variation_id, $product_id, 'No bracket placement found.');
			return false;
		}

		// The config is stored in the session and set when "Add to Apparel" button is clicked on the bracket builder page.
		// It contains the bracket theme and HTML to render the bracket.
		$config = $this->bracket_config_repo->get($bracket_theme, $bracket_placement);

		if (empty($config)) {
			$this->handle_add_to_cart_error($product, $variation_id, $product_id, 'No bracket config found.');
			return false;
		}

		$this->log('passed validation');

		return $passed;
	}

	// Add the bracket to the cart item data
	// This hooks into the woocommerce_add_cart_item_data filter
	public function add_bracket_to_cart_item_data($cart_item_data, $product_id, $variation_id) {
		$product = wc_get_product($product_id);

		// Perform similar checks as above to make sure we are dealing with a bracket product and that we have a bracket config
		if (!$this->is_bracket_product($product) || $this->bracket_config_repo->is_empty()) {
			$this->log('in add_bracket_to_cart_item_data: not a bracket product or no bracket config');
			return $cart_item_data;
		}

		$bracket_theme = $this->get_bracket_theme($variation_id);
		$bracket_placement = $this->get_bracket_placement($product);

		$config = $this->bracket_config_repo->get($bracket_theme, $bracket_placement);
		$this->log('in add_bracket_to_cart_item_data: config: ' . json_encode($config));

		$cart_item_data['bracket_config'] = $config;

		return $cart_item_data;
	}

	// Add the bracket config to the order line item data when the order is created
	// This is needed to ensure that data added to the cart item is persisted in the order
	public function add_bracket_to_order_item($item, $cart_item_key, $values, $order) {
		if (array_key_exists('bracket_config', $values)) {
			$item->add_meta_data('bracket_config', $values['bracket_config']);
		}
		if (array_key_exists('s3_url', $values)) {
			$item->add_meta_data('s3_url', $values['s3_url']);
		}
	}

	// Helper method to check if product is a bracket product
	private function is_bracket_product($product) {
		if (!$product) {
			return false;
		}
		return $this->product_has_category($product, BRACKET_PRODUCT_CATEGORY);
	}

	// Helper method to get the bracket theme
	private function get_bracket_theme($variation_id) {
		return get_post_meta($variation_id, 'wpbb_bracket_theme', true);
	}

	// Helper method to get the bracket placement
	private function get_bracket_placement($product) {
		// return get_post_meta($variation_id, 'wpbb_bracket_placement', true);
		if ($product && $this->product_has_category($product, BRACKET_PLACEMENT_CENTER_CAT)) {
			return 'center';
		}
		return 'top';
	}

	// Helper method to log error and show notice
	private function handle_add_to_cart_error($product, $variation_id, $product_id, $error_message) {
		$product_name = $product->get_name();
		$msg = 'Error adding ' . $product_name . ' to cart. ' . $error_message . '. Variation ID: ' . $variation_id . ' Product ID: ' . $product_id;
		$this->log($msg, 'warning');
		wc_add_notice(__('Error adding item to cart. Please contact the site administrator.', 'wp-bracket-builder'), 'error');
	}

	// this function hooks into woocommerce_before_checkout_process
	public function handle_before_checkout_process() {
		$cart = WC()->cart;
		if (!$cart) {
			return;
		}

		$original_cart_items = $cart->get_cart();
		$updated_cart_items = [];

		foreach ($original_cart_items as $cart_item_key => $cart_item) {
			$product = $cart_item['data'];
			if ($this->is_bracket_product($product)) {
				try {
					$cart_item = $this->process_bracket_product_item($cart_item);
				} catch (Exception $e) {
					$this->log_error('Error processing cart item: ' . $e->getMessage());
					throw new Exception('An error occurred while processing your order. Please contact the site administrator.');
				}
			}
			$updated_cart_items[$cart_item_key] = $cart_item;
		}

		$cart->set_cart_contents($updated_cart_items);
	}


	private function process_bracket_product_item($cart_item) {
		// get the url for the front design
		$front_url = get_post_meta($cart_item['variation_id'], 'wpbb_front_design', true);

		// a random filename for uploaded file
		$temp_filename = 'temp-' . uniqid() . '.pdf';

		if (empty($front_url)) {
			$error_data = array(
				'error' => 'Front design not found',
				'front_url' => $front_url,
			);
			throw new Exception(json_encode($error_data));
		}

		// Extract config from the cart item
		$bracket_config = $cart_item['bracket_config'] ?? null;

		if ($bracket_config) {
			$result = $this->handle_front_and_back_design($front_url, $bracket_config, $temp_filename);
		} else {
			// If no config was found, use only the front design
			$result = $this->handle_front_design_only($front_url, $temp_filename, 12, 16);
		}

		// Store the S3 URL in the cart item
		$cart_item['s3_url'] = $result; // The S3 URL of the final PDF

		return $cart_item;
	}

	private function log_error($message) {
		$this->log($message, 'error');
	}

	private function log($message, $log_level = 'debug') {
		$this->utils->log($message, $log_level);
	}

	private function handle_front_design_only($front_url, $temp_filename, $back_width, $back_height) {
		// If no config was found, use only the front design
		// However, Gelato still requires a two page PDF so we append a blank page to the front design
		// $result = $this->s3->copy_from_url($front_url, BRACKET_BUILDER_S3_ORDER_BUCKET, $temp_filename);
		$front = $this->s3->get_from_url($front_url);
		$merged = $this->pdf_service->merge_pdfs(array(
			array(
				'content' => $front,
			),
			array(
				'content' => '',
				'size' => array($back_width, $back_height),
			),
		));
		$result = $this->s3->put(BRACKET_BUILDER_S3_ORDER_BUCKET, $temp_filename, $merged);
		return $result;
	}

	private function handle_front_and_back_design($front_url, $bracket_config, $temp_filename) {
		// Use config to generate the back design and merge it with the front design in a two-page PDF
		$html = $bracket_config->html;

		// Generate a PDF file for the back design (the bracket)
		// We don't reuse the png from the product preview because only a PDF can supply Gelato with multiple designs
		$convert_req = array(
			'inchHeight' => 16,
			'inchWidth' => 12,
			'pdf' => true,
			'html' => $html,
		);

		$convert_res = $this->lambda_service->html_to_image($convert_req);
		// check if convert res is wp_error
		if (!isset($convert_res['imageUrl']) || empty($convert_res['imageUrl'])) {
			$error_data = array(
				'error' => 'Error converting bracket to PDF.',
				'convert_res' => $convert_res,
			);
			throw new Exception(json_encode($error_data));
		}

		$back_url = $convert_res['imageUrl'];

		// merge pdfs
		$front = $this->s3->get_from_url($front_url);
		$back = $this->s3->get_from_url($back_url);
		$merged = $this->pdf_service->merge_pdfs(array(
			array(
				'content' => $front,
			),
			array(
				'content' => $back,
			),
		));
		$result = $this->s3->put(BRACKET_BUILDER_S3_ORDER_BUCKET, $temp_filename, $merged);
		return $result;
	}

	// this function hooks into woocommerce_payment_complete
	public function handle_payment_complete($order_id) {
		$order = wc_get_order($order_id);
		if ($order) {
			$items = $order->get_items();
			foreach ($items as $item) {
				$product = $item->get_product();
				if ($this->is_bracket_product($product)) {
					try {
						// $this->handle_bracket_product_item($order, $item);
						// Once the order has processed, we need to rename the s3 file to include the order ID and item ID
						$order_filename = $this->get_gelato_order_filename($order, $item);
						$item_arr['order_filename'] = $order_filename;

						// get the s3 url from the cart item
						$s3_url = $item->get_meta('s3_url');

						if (empty($s3_url)) {
							// If S3 url is not found, log an error and continue to the next item.
							// Can't do anything else because the order has already been processed at this point.
							$error_msg = 'ACTION NEEDED: S3 URL not found for completed order: ' . $order_id . ' item: ' . $item->get_id();
							$this->utils->log_sentry_message($error_msg, \Sentry\Severity::error());
							continue;
						}

						// rename the file
						$order_url = $this->s3->rename_from_url($s3_url, $order_filename);

						// update the cart item with the new s3 url for record keeping
						$item->update_meta_data('s3_url', $order_url);
						$item->save();
					} catch (Exception $e) {
						$this->utils->log_sentry_message($e->getMessage(), \Sentry\Severity::error());
					}
				}
			}
		}
	}

	// private function handle_bracket_product_item($order, $item) {
	// 	$item_arr = array();

	// 	// Once the order has processed, we need to rename the s3 file to include the order ID and item ID
	// 	$order_filename = $this->get_gelato_order_filename($order, $item);
	// 	$item_arr['order_filename'] = $order_filename;

	// 	$this->utils->log_sentry_message(json_encode($item_arr));
	// }

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

	// Disallow purchase of variations that don't have a front design
	// hooks into filter `woocommerce_available_variation`
	public function filter_variation_availability($available_array, $this_obj, $variation) {
		// bail if not bracket product
		if (!$this->is_bracket_product($variation)) {
			return $available_array;
		}
		// Check if config exists
		$custom_back = !$this->bracket_config_repo->is_empty(); // If config is not empty, the product has a custom back design so bracket theme is needed
		$front_design = get_post_meta($variation->get_id(), 'wpbb_front_design', true);
		$bracket_theme = get_post_meta($variation->get_id(), 'wpbb_bracket_theme', true);

		// If front design is empty, or bracket theme is empty AND config is set, make not purchasable
		if (empty($front_design) || empty($bracket_theme) && $custom_back) {
			$available_array['is_purchasable'] = false; // Make not purchasable
			$available_array['variation_is_active'] = false; // Grey out unavailable variation
		}

		return $available_array;
	}
	/**
	 * Get all gallery images for the product
	 *
	 * @param WC_Product $product
	 * @return array
	 */

	private function get_product_gallery($product) {
		// get all gallery images for the product
		$attachment_ids = $product->get_gallery_image_ids();
		$gallery_images = $this->get_images($attachment_ids);
		return $gallery_images;
	}
	private function get_images($image_ids) {
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
}
