<?php
require_once WPBB_PLUGIN_DIR .
  'includes/service/bracket-product/class-wpbb-bracket-product-utils.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/gelato/class-wpbb-gelato-product-integration.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-product-integration-interface.php';
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-utils.php';

class Wpbb_ProductPreviewService {
  /**
   * @var Wpbb_BracketProductUtils
   */
  private $bracket_product_utils;

  /**
   * @var Wpbb_BracketPlayRepo
   */
  private $play_repo;

  /**
   * @var Wpbb_ProductIntegrationInterface
   */
  private $product_integration;

  /**
   * @var Wpbb_Utils
   */
  private $utils;

  public function __construct() {
    $this->bracket_product_utils = new Wpbb_BracketProductUtils();
    $this->play_repo = new Wpbb_BracketPlayRepo();
    $this->product_integration = new Wpbb_GelatoProductIntegration();
    $this->utils = new Wpbb_Utils();
  }

  public function get_ajax_obj() {
    $this->utils->log('get_ajax_obj');
    $post = get_post();
    // check if post is product
    if (!$post || $post->post_type !== 'product') {
      $this->utils->warn('post is not product');
      echo 'post is not product';
      return;
    }

    $product = wc_get_product($post->ID);
    $is_bracket_product = $this->bracket_product_utils->is_bracket_product(
      $product
    );
    if (!$is_bracket_product) {
      $this->utils->warn('product is not bracket product');
      echo 'product is not bracket product';
      return;
    }

    $play_id = $this->utils->get_cookie('play_id');
    if (!$play_id) {
      $this->utils->warn('play_id not found');
      echo 'play_id not found';
      return;
    }
    $play = $this->play_repo->get($play_id);
    if (!$play) {
      $this->utils->warn('play not found');
      echo 'play not found';
      return;
    }


    $gallery_images = $this->get_product_gallery($product);
    $color_options = $this->get_attribute_options($product, 'color');
    $placement = $this->bracket_product_utils->get_bracket_placement(
      $product
    );

    $overlay_map = $this->product_integration->get_overlay_map($play, $placement);

    return array(
    		'bracket_url_theme_map' => $overlay_map, 
    		'gallery_images' => $gallery_images,
    		'color_options' => $color_options,
        'play_id' => $play_id,
    );
  }


  public function get_archive_url() {
    $category_slug = 'bracket-ready';
    $redirect_url = get_term_link($category_slug, 'product_cat');
    return $redirect_url;
  }

  // get all attribute options for a product
  public function get_attribute_options(
    mixed $product,
    string $attribute_name
  ) {
    $attributes = $product->get_attributes();
    if (!array_key_exists($attribute_name, $attributes)) {
      return [];
    }
    $attribute = $attributes[$attribute_name];
    $attribute_options = $attribute->get_options();
    return $attribute_options;
  }

  /**
   * Get all gallery images for the product
   *
   * @param WC_Product $product
   * @return array
   */

  public function get_product_gallery($product) {
    // get all gallery images for the product
    $attachment_ids = $product->get_gallery_image_ids();
    $gallery_images = $this->get_images($attachment_ids);
    return $gallery_images;
  }
  public function get_images($image_ids) {
    $images = [];

    foreach ($image_ids as $imageId) {
      $image_attrs = [
        'src' => wp_get_attachment_url($imageId),
        'title' => get_the_title($imageId),
      ];
      $images[] = $image_attrs;
    }

    return $images;
  }
}
