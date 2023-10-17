<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  'class-wpbb-product-integration-interface.php';
require_once 'class-wpbb-gelato-admin-hooks.php';
require_once 'class-wpbb-gelato-public-hooks.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/http/class-wpbb-guzzle-client.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/http/class-wpbb-http-client-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/object-storage/class-wpbb-object-storage-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/object-storage/class-wpbb-s3-storage.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/http/class-wpbb-bracket-image-request-factory.php';

class Wpbb_GelatoProductIntegration implements
  Wpbb_ProductIntegrationInterface {
  /**
   * @var Wpbb_GelatoAdminHooks
   */
  private $admin_hooks;

  /**
   * @var Wpbb_GelatoPublicHooks
   */
  private $public_hooks;

  /**
   * @var Wpbb_HttpClientInterface
   */
  private $client;

  /**
   * @var Wpbb_ObjectStorageInterface
   */
  private $object_storage;

  /**
   * @var Wpbb_BracketImageRequestFactory
   */
  private $request_factory;

  public function __construct($args = []) {
    $this->admin_hooks = $args['admin_hooks'] ?? new Wpbb_GelatoAdminHooks();
    $this->public_hooks = $args['admin_hooks'] ?? new Wpbb_GelatoPublicHooks();
    $this->client = $args['client'] ?? new Wpbb_GuzzleClient();
    $this->object_storage = $args['object_storage'] ?? new Wpbb_S3Storage();
    $this->request_factory =
      $args['request_factory'] ??
      new Wpbb_BracketImageRequestFactory([
        'object_storage' => $this->object_storage,
      ]);
  }

  public function get_post_meta_key(): string {
    return 'gelato_product';
  }

  // Admin hooks
  public function after_variable_attributes(
    $loop,
    $variation_data,
    $variation
  ): void {
    $this->admin_hooks->variation_settings_fields(
      $loop,
      $variation_data,
      $variation
    );
  }

  public function save_product_variation($variation_id, $i): void {
    $this->admin_hooks->validate_variation_fields($variation_id, $i);
    $this->admin_hooks->save_variation_settings_fields($variation_id, $i);
  }

  public function admin_notices(): void {
    $this->admin_hooks->display_custom_admin_error();
  }

  // Public hooks
  public function add_to_cart_validation(
    $passed,
    $product_id,
    $quantity,
    $variation_id = null,
    $variations = null
  ): bool {
    return $this->public_hooks->bracket_product_add_to_cart_validation(
      $passed,
      $product_id,
      $quantity,
      $variation_id,
      $variations
    );
  }

  public function add_cart_item_data(
    $cart_item_data,
    $product_id,
    $variation_id
  ): array {
    return $this->public_hooks->add_bracket_to_cart_item_data(
      $cart_item_data,
      $product_id,
      $variation_id
    );
  }

  public function checkout_create_order_line_item(
    $item,
    $cart_item_key,
    $values,
    $order
  ): void {
    $this->public_hooks->add_bracket_to_order_item(
      $item,
      $cart_item_key,
      $values,
      $order
    );
  }

  public function before_checkout_process(): void {
    $this->public_hooks->handle_before_checkout_process();
  }

  public function payment_complete($order_id): void {
    $this->public_hooks->handle_payment_complete($order_id);
  }

  public function available_variation(
    $available_array,
    $this_obj,
    $variation
  ): array {
    return $this->public_hooks->filter_variation_availability(
      $available_array,
      $this_obj,
      $variation
    );
  }

  //implement this
  public function generate_images(Wpbb_PostBracketInterface $bracket): void {
    $request_data = $this->request_factory->get_request_data($bracket);
    $responses = $this->client->send_many($request_data);
    //Save to post meta
    update_post_meta(
      $bracket->get_post_id(),
      'gelato_images',
      json_encode($responses)
    );
  }

  /**
   * Given a placement ('top' or 'center') returns an overlay map that can get passed direcly to the bracket preview page
   *
   * @var Wpbb_PostBracketInterface $bracket
   * @var string $placement - 'top' or 'center'
   *
   * @return array - an array of overlay maps
   *
   * @example
   * [
   * 'light' => 'someS3url',
   * 'dark' => 'someS3url'
   * ]
   */
  public function get_overlay_map(
    Wpbb_PostBracketInterface $bracket,
    string $placement
  ): array {
    return [
      'light' => 'someS3url',
      'dark' => 'someS3url',
    ];
  }
}
