<?php
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'domain/class-wp-bracket-builder-bracket-interface.php';

interface Wp_Bracket_Builder_Print_Integration_Interface {

	public function add_to_cart_validation($passed, $product_id, $quantity, $variation_id = null, $variations = null): bool;

	public function add_to_cart_item_data($cart_item_data, $product_id, $variation_id): array;

	public function checkout_create_order_line_item($item, $cart_item_key, $values, $order): void;

	public function before_checkout_process(): void;

	public function payment_complete($order_id): void;

	public function available_variation($available_array, $this_obj, $variation): array;
}
