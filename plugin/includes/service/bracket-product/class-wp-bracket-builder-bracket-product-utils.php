<?php
class Wp_Bracket_Builder_Bracket_Product_Utils {

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
		// return get_post_meta($variation_id, 'wpbb_bracket_placement', true);
		if ($product && $this->product_has_category($product, BRACKET_PLACEMENT_CENTER_CAT)) {
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
}