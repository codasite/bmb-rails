<?php

interface Wp_Bracket_Builder_Product_Integration_Interface {

	// admin hooks 
	public function after_variable_attributes($loop, $variation_data, $variation): void;

	public function save_product_variation($variation_id, $i): void;

	public function admin_notices(): void;

	// public hooks
	public function add_to_cart_validation($passed, $product_id, $quantity, $variation_id = null, $variations = null): bool;

	public function add_cart_item_data($cart_item_data, $product_id, $variation_id): array;

	public function checkout_create_order_line_item($item, $cart_item_key, $values, $order): void;

	public function before_checkout_process(): void;

	public function payment_complete($order_id): void;

	public function available_variation($available_array, $this_obj, $variation): array;

	// product preview
}
