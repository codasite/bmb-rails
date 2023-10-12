<?php
require_once plugin_dir_path(dirname(__FILE__, 3)) .
  'includes/service/class-wpbb-product-preview-service.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) .
  'includes/service/bracket-product/class-wpbb-bracket-product-utils.php';

class Wpbb_ProductPreviewService {
  /**
   * @var Wpbb_BracketProductUtils
   */
  private $bracket_product_utils;

  public function __construct() {
    $this->bracket_product_utils = new Wpbb_BracketProductUtils();
  }

  public function localize_script() {
    $post = get_post();
    // check if post is product

    // For product page
    $product = wc_get_product($post->ID);
    $bracket_product_archive_url = $this->get_archive_url();

    $bracket_placement = $this->bracket_product_utils->get_bracket_placement(
      $product
    );

    $is_bracket_product = $this->bracket_product_utils->is_bracket_product(
      $product
    );
    // Only get product details on product pages.
    $gallery_images = $is_bracket_product
      ? $this->get_product_gallery($product)
      : [];
    $color_options = $is_bracket_product
      ? $this->get_attribute_options($product, 'color')
      : [];
    $overlay_map = $is_bracket_product
      ? $this->build_overlay_map($bracket_placement)
      : [];

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

  public function build_overlay_map($placement): array {
    $dark = $this->bracket_config_repo->get('dark', $placement);
    $light = $this->bracket_config_repo->get('light', $placement);

    $overlay_map = [
      'dark' => $dark->img_url,
      'light' => $light->img_url,
    ];

    return $overlay_map;
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
      // $imageSrc = wp_get_attachment_image_src($imageId, 'full');
      // $imageUrl = $imageSrc[0];
      // $image_urls[] = $imageUrl;
      $image_attrs = [
        'src' => wp_get_attachment_url($imageId),
        'title' => get_the_title($imageId),
      ];
      $images[] = $image_attrs;
    }

    return $images;
  }
}
