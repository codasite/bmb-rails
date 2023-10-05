<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-product-integration-interface.php';
require_once 'class-wp-bracket-builder-gelato-admin-hooks.php';
require_once 'class-wp-bracket-builder-gelato-public-hooks.php';


class Wp_Bracket_Builder_Gelato_Product_Integration implements Wp_Bracket_Builder_Product_Integration_Interface {

	/**
	 * @var Wp_Bracket_Builder_Gelato_Admin_Hooks
	 */
	private $admin_hooks;

	/**
	 * @var Wp_Bracket_Builder_Gelato_Public_Hooks
	 */
	private $public_hooks;

	public function __construct() {
		$this->admin_hooks = new Wp_Bracket_Builder_Gelato_Admin_Hooks();
		$this->public_hooks = new Wp_Bracket_Builder_Gelato_Public_Hooks();
	}

	// Admin hooks
	public function after_variable_attributes($loop, $variation_data, $variation): void {
		$this->admin_hooks->variation_settings_fields($loop, $variation_data, $variation);
	}

	public function save_product_variation($variation_id, $i): void {
		$this->admin_hooks->validate_variation_fields($variation_id, $i);
		$this->admin_hooks->save_variation_settings_fields($variation_id, $i);
	}

	public function admin_notices(): void {
		$this->admin_hooks->display_custom_admin_error();
	}

	// Public hooks
	public function add_to_cart_validation($passed, $product_id, $quantity, $variation_id = null, $variations = null): bool {
		return $this->public_hooks->bracket_product_add_to_cart_validation($passed, $product_id, $quantity, $variation_id, $variations);
	}

	public function add_cart_item_data($cart_item_data, $product_id, $variation_id): array {
		return $this->public_hooks->add_bracket_to_cart_item_data($cart_item_data, $product_id, $variation_id);

	}

	public function checkout_create_order_line_item($item, $cart_item_key, $values, $order): void {
		$this->public_hooks->add_bracket_to_order_item($item, $cart_item_key, $values, $order);
	}

	public function before_checkout_process(): void {
		$this->public_hooks->handle_before_checkout_process();
	}

	public function payment_complete($order_id): void {
		$this->public_hooks->handle_payment_complete($order_id);
	}

	public function available_variation($available_array, $this_obj, $variation): array {
		return $this->public_hooks->filter_variation_availability($available_array, $this_obj, $variation);
	}
}
