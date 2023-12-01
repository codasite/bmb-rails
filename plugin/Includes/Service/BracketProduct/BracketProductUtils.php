<?php
namespace WStrategies\BMB\Includes\Service\BracketProduct;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;

class BracketProductUtils {
  /**
   * @var BracketRepo
   */
  private $bracket_repo;

  public function __construct($args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
  }
  public function get_bracket_product_archive_url() {
    // $category_slug = BRACKET_PRODUCT_CATEGORY;
    // $redirect_url = get_term_link($category_slug, 'product_cat');
    // return $redirect_url;
    $shop_slug = defined('BRACKET_PRODUCT_SHOP_SLUG')
      ? BRACKET_PRODUCT_SHOP_SLUG
      : 'bracket-shop';
    return get_permalink(get_page_by_path($shop_slug));
  }
  // Helper method to check if product is a bracket product
  public function is_bracket_product($product) {
    if (!$product) {
      return false;
    }
    return $this->product_has_category($product, BRACKET_PRODUCT_CATEGORY);
  }

  // Helper method to get the bracket theme
  public function get_bracket_theme($variation_id) {
    return get_post_meta($variation_id, 'wpbb_bracket_theme', true);
  }

  // Helper method to get the bracket placement
  public function get_bracket_placement($product) {
    if (
      $product &&
      $this->product_has_category($product, BRACKET_PLACEMENT_CENTER_CAT)
    ) {
      return 'center';
    }
    return 'top';
  }

  public function product_has_category($product, $category_slug) {
    if ($product->is_type('variation')) {
      return has_term($category_slug, 'product_cat', $product->get_parent_id());
    } else {
      return has_term($category_slug, 'product_cat', $product->get_id());
    }
  }

  public function get_bracket_fee(Bracket|int $bracket): float {
    if ($bracket instanceof Bracket) {
      $bracket = $bracket->id;
    }
    $fee = floatval(get_post_meta($bracket, 'bracket_fee', true));
    return $fee ? $fee : 0;
  }

  public function get_bracket_fee_name(Bracket|int $bracket): string {
    $bracket =
      $bracket instanceof Bracket
        ? $bracket
        : $this->bracket_repo->get($bracket);
    $bracket_title = $bracket->title;
    return 'Tournament fee: ' . $bracket_title;
  }
}
