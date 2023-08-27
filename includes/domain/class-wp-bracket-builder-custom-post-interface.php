<?php

interface Wp_Bracket_Builder_Custom_Post_Interface {

	static public function get_post_type(): string;

	/**
	 * Get the post data as a wp_insert_post compatible array
	 * 
	 * @return array
	 */
	public function get_post_data(): array;


	/**
	 * Get all the post meta as a key-value associative array
	 * 
	 * @return array
	 */
	public function get_post_meta(): array;
}
