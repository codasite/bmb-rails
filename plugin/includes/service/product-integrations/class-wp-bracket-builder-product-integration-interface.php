<?php

interface Wp_Bracket_Builder_Product_Integration_Interface {

	// admin hooks 
	public function after_variable_attributes($loop, $variation_data, $variation): void;

	public function save_product_variation($variation_id, $i): void;

	public function admin_notices(): void;

}
