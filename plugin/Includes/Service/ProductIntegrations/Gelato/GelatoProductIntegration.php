<?php
namespace WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato;

use WStrategies\BMB\Includes\Domain\BracketConfig;
use WStrategies\BMB\Includes\Domain\PostBracketInterface;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Service\Http\BracketImageRequestFactory;
use WStrategies\BMB\Includes\Service\Http\GuzzleClient;
use WStrategies\BMB\Includes\Service\Http\HttpClientInterface;
use WStrategies\BMB\Includes\Service\ObjectStorage\ObjectStorageInterface;
use WStrategies\BMB\Includes\Service\ObjectStorage\S3Storage;
use WStrategies\BMB\Includes\Service\ProductIntegrations\ProductIntegrationInterface;
use WStrategies\BMB\Includes\Utils;

class GelatoProductIntegration implements
  ProductIntegrationInterface,
  HooksInterface {
  /**
   * @var GelatoAdminHooks
   */
  private $admin_hooks;

  /**
   * @var GelatoPublicHooks
   */
  private $public_hooks;

  /**
   * @var HttpClientInterface
   */
  private $client;

  /**
   * @var ObjectStorageInterface
   */
  public $object_storage;

  /**
   * @var BracketImageRequestFactory
   */
  private $request_factory;

  /**
   * @var Utils
   */
  public $utils;

  /**
   * @var BracketPlayRepo
   */
  private $play_repo;

  public function __construct($args = []) {
    $this->object_storage = $args['object_storage'] ?? new S3Storage();
    $this->request_factory =
      $args['request_factory'] ??
      new BracketImageRequestFactory([
        'object_storage' => $this->object_storage,
      ]);
    $this->admin_hooks = $args['admin_hooks'] ?? new GelatoAdminHooks();
    $this->public_hooks = $args['public_hooks'] ?? new GelatoPublicHooks($this);
    $this->client = $args['client'] ?? new GuzzleClient();
    $this->utils = new Utils();
    $this->play_repo = new BracketPlayRepo();
  }

  public function get_http_client(): HttpClientInterface {
    return $this->client;
  }

  public function get_request_factory(): BracketImageRequestFactory {
    return $this->request_factory;
  }

  public function get_play_repo(): BracketPlayRepo {
    return $this->play_repo;
  }

  public function load(Loader $loader): void {
    $loader->add_filter(
      'woocommerce_add_to_cart_validation',
      [$this, 'add_to_cart_validation'],
      10,
      5
    );
    $loader->add_action(
      'woocommerce_add_cart_item_data',
      [$this, 'add_cart_item_data'],
      10,
      3
    );
    $loader->add_action(
      'woocommerce_checkout_create_order_line_item',
      [$this, 'checkout_create_order_line_item'],
      10,
      4
    );
    $loader->add_action('woocommerce_before_checkout_process', [
      $this,
      'before_checkout_process',
    ]);
    $loader->add_action('woocommerce_payment_complete', [
      $this,
      'payment_complete',
    ]);
    $loader->add_filter(
      'woocommerce_available_variation',
      [$this, 'available_variation'],
      10,
      3
    );
    $loader->add_action(
      'woocommerce_product_after_variable_attributes',
      [$this, 'after_variable_attributes'],
      10,
      3
    );
    $loader->add_action(
      'woocommerce_save_product_variation',
      [$this, 'save_product_variation'],
      10,
      2
    );
    $loader->add_action('admin_notices', [$this, 'admin_notices']);
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

  public function generate_images(PostBracketInterface $bracket): void {
    $request_data = $this->request_factory->get_request_data($bracket);
    $responses = $this->client->send_many($request_data);
    if (defined('DISABLE_IMAGE_GENERATOR_CALLS')) {
      $responses = [
        'top_light' => [
          'image_url' => 'https://test.com/top_light.png',
        ],
      ];
    }
    update_post_meta(
      $bracket->get_post_id(),
      $this->get_post_meta_key(),
      json_encode($responses)
    );
  }

  /**
   * Given a placement ('top' or 'center') returns an overlay map that can get passed direcly to the bracket preview page
   *
   * @var PostBracketInterface $bracket
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
    PostBracketInterface $bracket,
    string $placement
  ): array {
    $meta = $this->get_meta($bracket);
    $overlay_map = [];
    foreach ($meta as $key => $value) {
      if (strpos($key, $placement) !== false) {
        list($placement, $theme) = explode('_', $key);
        $overlay_map[$theme] = $value['image_url'];
      }
    }
    return $overlay_map;
  }

  public function has_bracket_config() {
    $play = $this->play_repo->get();
    return $play !== null;
  }

  public function get_bracket_config($theme, $placement) {
    $play = $this->play_repo->get();
    if (!$play) {
      return null;
    }
    $meta = $this->get_meta($play);
    foreach ($meta as $key => $value) {
      if (
        strpos($key, $placement) !== false &&
        strpos($key, $theme) !== false
      ) {
        list($placement, $theme) = explode('_', $key);
        $config = new BracketConfig(
          $play->id,
          $play->bracket_id,
          $theme,
          $placement,
          $value['image_url']
        );
        return $config;
      }
    }
    return null;
  }

  private function get_meta(PostBracketInterface $bracket): array {
    $meta = json_decode(
      get_post_meta($bracket->get_post_id(), $this->get_post_meta_key(), true),
      true
    );
    if (!$meta) {
      return [];
    }
    return $meta;
  }
}
