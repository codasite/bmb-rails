<?php

require_once plugin_dir_path(dirname(__FILE__, 2)) .
  'bracket-product/class-wpbb-bracket-product-utils.php';
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-utils.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-config-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-config.php';
require_once WPBB_PLUGIN_DIR . 'includes/service/class-wpbb-aws-service.php';
require_once WPBB_PLUGIN_DIR . 'includes/service/class-wpbb-pdf-service.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-wc-functions.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';

class Wpbb_GelatoPublicHooks {
  /**
   * @var Wpbb_BracketProductUtils
   */
  private $bracket_product_utils;

  /**
   * @var Wpbb_Utils
   */
  private $utils;

  /**
   * @var Wpbb_GelatoProductIntegration
   */
  private $gelato;

  /**
   * @var Wpbb_S3Service
   */
  private $s3;

  /**
   * @var Wpbb_PdfService
   */
  private $pdf_service;

  /**
   * @var Wpbb_WcFunctions
   */
  private $wc;

  public function __construct(
    Wpbb_GelatoProductIntegration $gelato,
    $opts = []
  ) {
    $this->gelato = $gelato;
    $this->bracket_product_utils =
      $opts['bracket_product_utils'] ?? new Wpbb_BracketProductUtils();
    $this->utils = $opts['utils'] ?? new Wpbb_Utils();
    $this->s3 = $opts['s3'] ?? new Wpbb_S3Service();
    $this->pdf_service = $opts['pdf_service'] ?? new Wpbb_PdfService();
    $this->wc = $opts['wc'] ?? new Wpbb_WcFunctions();
  }

  private function is_bracket_product($product) {
    return $this->bracket_product_utils->is_bracket_product($product);
  }

  private function get_bracket_theme($variation_id) {
    return $this->bracket_product_utils->get_bracket_theme($variation_id);
  }

  private function get_bracket_placement($product) {
    return $this->bracket_product_utils->get_bracket_placement($product);
  }

  // Validate bracket product data before adding to cart
  // Hooks into woocommerce_add_to_cart_validation
  public function bracket_product_add_to_cart_validation(
    $passed,
    $product_id,
    $quantity,
    $variation_id = null,
    $variations = null
  ): bool {
    $product = $this->wc->wc_get_product($product_id);

    if (
      !$this->is_bracket_product($product) ||
      !$this->gelato->has_bracket_config()
    ) {
      // Not a bracket product. Treat as a normal product.
      return $passed;
    }

    // The bracket theme must be set in the variation meta data.
    // Errors out if this is not set. NOTE: The preview can still render the correct theme even if this is not set.
    // This is because the preview obtains the bracket theme from the image title.
    $bracket_theme = $this->get_bracket_theme($variation_id);

    if (empty($bracket_theme)) {
      $this->handle_add_to_cart_error(
        $product,
        $variation_id,
        $product_id,
        'No bracket theme found.'
      );
      return false;
    }

    $bracket_placement = $this->get_bracket_placement($product);

    if (empty($bracket_placement)) {
      $this->handle_add_to_cart_error(
        $product,
        $variation_id,
        $product_id,
        'No bracket placement found.'
      );
      return false;
    }

    $config = $this->gelato->get_bracket_config(
      $bracket_theme,
      $bracket_placement
    );

    if (empty($config)) {
      $this->handle_add_to_cart_error(
        $product,
        $variation_id,
        $product_id,
        'No bracket config found.'
      );
      return false;
    }

    return $passed;
  }

  // Add the bracket to the cart item data
  // This hooks into the woocommerce_add_cart_item_data filter
  // This fires when a product is added to the cart
  public function add_bracket_to_cart_item_data(
    $cart_item_data,
    $product_id,
    $variation_id
  ) {
    $product = $this->wc->wc_get_product($product_id);

    // Perform similar checks as above to make sure we are dealing with a bracket product and that we have a bracket config
    if (
      !$this->is_bracket_product($product) ||
      !$this->gelato->has_bracket_config()
    ) {
      return $cart_item_data;
    }

    $bracket_theme = $this->get_bracket_theme($variation_id);
    $bracket_placement = $this->get_bracket_placement($product);

    // Get the bracket config for the given theme and placement
    $config = $this->gelato->get_bracket_config(
      $bracket_theme,
      $bracket_placement
    );

    $cart_item_data['bracket_config'] = $config;

    return $cart_item_data;
  }

  // Add the bracket config to the order line item data when the order is created
  // This is needed to ensure that data added to the cart item is persisted in the order
  public function add_bracket_to_order_item(
    $item,
    $cart_item_key,
    $values,
    $order
  ) {
    if (array_key_exists('bracket_config', $values)) {
      $item->add_meta_data('bracket_config', $values['bracket_config']);
    }
    if (array_key_exists('s3_url', $values)) {
      $item->add_meta_data('s3_url', $values['s3_url']);
    }
  }

  // Helper method to log error and show notice
  public function handle_add_to_cart_error(
    $product,
    $variation_id,
    $product_id,
    $error_message
  ) {
    $product_name = $product->get_name();
    $msg =
      'Error adding ' .
      $product_name .
      ' to cart. ' .
      $error_message .
      '. Variation ID: ' .
      $variation_id .
      ' Product ID: ' .
      $product_id;
    $this->log($msg, 'warning');
    $this->wc->wc_add_notice(
      __(
        'Error adding item to cart. Please contact the site administrator.',
        'wp-bracket-builder'
      ),
      'error'
    );
  }

  // this function hooks into woocommerce_before_checkout_process
  public function handle_before_checkout_process() {
    $cart = $this->wc->WC()->cart;
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
          throw new Exception(
            'An error occurred while processing your order. Please contact the site administrator.'
          );
        }
      }
      $updated_cart_items[$cart_item_key] = $cart_item;
    }

    $cart->set_cart_contents($updated_cart_items);
  }

  public function process_bracket_product_item($cart_item) {
    // get the url for the front design
    $front_url = get_post_meta(
      $cart_item['variation_id'],
      'wpbb_front_design',
      true
    );

    // a random filename for uploaded file
    $temp_filename = 'temp-' . uniqid() . '.pdf';

    if (empty($front_url)) {
      $error_data = [
        'error' => 'Front design not found',
        'front_url' => $front_url,
      ];
      throw new Exception(json_encode($error_data));
    }

    // Extract config from the cart item
    $bracket_config = $cart_item['bracket_config'] ?? null;

    if ($bracket_config) {
      $result = $this->handle_front_and_back_design(
        $front_url,
        $bracket_config,
        $temp_filename
      );
    } else {
      // If no config was found, use only the front design
      $result = $this->handle_front_design_only(
        $front_url,
        $temp_filename,
        12,
        16
      );
    }

    // Store the S3 URL in the cart item
    $cart_item['s3_url'] = $result; // The S3 URL of the final PDF

    return $cart_item;
  }

  public function log_error($message) {
    $this->log($message, 'error');
  }

  public function log($message, $log_level = 'debug') {
    $this->utils->log($message, $log_level);
  }

  public function handle_front_design_only(
    $front_url,
    $temp_filename,
    $back_width,
    $back_height
  ) {
    // If no config was found, use only the front design
    // However, Gelato still requires a two page PDF so we append a blank page to the front design
    // $result = $this->s3->copy_from_url($front_url, BRACKET_BUILDER_S3_ORDER_BUCKET, $temp_filename);
    $front = $this->s3->get_from_url($front_url);
    $merged = $this->pdf_service->merge_pdfs([
      [
        'content' => $front,
      ],
      [
        'content' => '',
        'size' => [$back_width, $back_height],
      ],
    ]);
    $result = $this->s3->put(
      BRACKET_BUILDER_S3_ORDER_BUCKET,
      $temp_filename,
      $merged
    );
    return $result;
  }

  public function handle_front_and_back_design(
    $front_url,
    $bracket_config,
    $temp_filename
  ) {
    // Use config to generate the back design and merge it with the front design in a two-page PDF
    $play_id = $bracket_config->play_id;
    $play = $this->gelato->play_repo->get($play_id);
    $theme = $bracket_config->theme_mode;
    $placement = $bracket_config->bracket_placement;

    $request_data = $this->gelato->request_factory->get_request_data($play, [
      'themes' => [$theme],
      'positions' => [$placement],
      'inch_height' => 16,
      'inch_width' => 12,
      'pdf' => true,
    ]);

    $response = null;
    try {
      $response = $this->gelato->client->send_many($request_data);
    } catch (Exception $e) {
      $this->log_error('Error sending request to Gelato: ' . $e->getMessage());
      throw new Exception(
        'An error occurred while processing your order. Please contact the site administrator.'
      );
    }

    if (!$response) {
      $error_data = [
        'error' => 'Error converting bracket to PDF. No response found.',
        'response' => $response,
      ];
      throw new Exception(json_encode($error_data));
    }

    $convert_res = reset($response);
    // check if convert res is wp_error
    if (!isset($convert_res['image_url']) || empty($convert_res['image_url'])) {
      $error_data = [
        'error' => 'Error converting bracket to PDF. No image_url found.',
        'convert_res' => $convert_res,
      ];
      throw new Exception(json_encode($error_data));
    }

    $back_url = $convert_res['image_url'];

    // merge pdfs
    $front = $this->s3->get_from_url($front_url);
    $back = $this->s3->get_from_url($back_url);
    $merged = $this->pdf_service->merge_pdfs([
      [
        'content' => $front,
      ],
      [
        'content' => $back,
      ],
    ]);
    $result = $this->s3->put(
      BRACKET_BUILDER_S3_ORDER_BUCKET,
      $temp_filename,
      $merged
    );
    return $result;
  }

  // this function hooks into woocommerce_payment_complete
  public function handle_payment_complete($order_id) {
    $order = $this->wc->wc_get_order($order_id);
    if ($order) {

      // Find user from order email address
      $email = $order->get_billing_email();
      $user = get_user_by('email', $email);
      $user_id = $user->ID;
      
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
              $error_msg =
                'ACTION NEEDED: S3 URL not found for completed order: ' .
                $order_id .
                ' item: ' .
                $item->get_id();
              $this->utils->log_sentry_message(
                $error_msg,
                \Sentry\Severity::error()
              );
              continue;
            }

            // rename the file
            $order_url = $this->s3->rename_from_url($s3_url, $order_filename);

            // update the cart item with the new s3 url for record keeping
            $item->update_meta_data('s3_url', $order_url);
            $item->save();
            // if all went well, do the play_printed action
            $config = $item->get_meta('bracket_config');
            do_action('wpbb_play_printed', $config->play_id);


            // update play author id with user_id
            $play_repo = new Wpbb_BracketPlayRepo();
            $play_repo->update($play->id, ['author' => $user_id]);



          } catch (Exception $e) {
            $this->utils->log_sentry_message(
              $e->getMessage(),
              \Sentry\Severity::error()
            );
          }
        }
      }
    }
  }

  private function get_gelato_order_filename($order, $item) {
    $order_id = $order->get_id();
    $item_id = $item->get_id();
    $filename = $order_id . '_' . $item_id . '.pdf';
    return $filename;
  }

  // Disallow purchase of variations that don't have a front design
  // hooks into filter `woocommerce_available_variation`
  public function filter_variation_availability(
    $available_array,
    $this_obj,
    $variation
  ) {
    // bail if not bracket product
    if (!$this->is_bracket_product($variation)) {
      return $available_array;
    }
    // Check if config exists
    $custom_back = !!$this->gelato->has_bracket_config(); // If config is not empty, the product has a custom back design so bracket theme is needed
    $front_design = get_post_meta(
      $variation->get_id(),
      'wpbb_front_design',
      true
    );
    $bracket_theme = get_post_meta(
      $variation->get_id(),
      'wpbb_bracket_theme',
      true
    );

    // If front design is empty, or bracket theme is empty AND config is set, make not purchasable
    if (empty($front_design) || (empty($bracket_theme) && $custom_back)) {
      $available_array['is_purchasable'] = false; // Make not purchasable
      $available_array['variation_is_active'] = false; // Grey out unavailable variation
    }

    return $available_array;
  }
}
