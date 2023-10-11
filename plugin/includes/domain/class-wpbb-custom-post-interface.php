<?php

interface Wpbb_CustomPostInterface
{

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

	/**
	 * Get all the post data to update as a wp_update_post compatible array
	 * 
	 * @return array
	 */
	public function get_update_post_data(): array;

	/**
	 * Get all the post meta to update as a key-value associative array
	 * 
	 * @return array
	 */
	public function get_update_post_meta(): array;
}
