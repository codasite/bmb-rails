<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-custom-post-interface.php';

abstract class Wp_Bracket_Builder_Custom_Post_Repository_Base {

	public function insert_post(Wp_Bracket_Builder_Custom_Post_Interface $post, $wp_error = false): int {
		$post_id = wp_insert_post($post->get_post_data(), $wp_error);

		if (0 === $post_id || $post_id instanceof WP_Error) {
			return $post_id;
		}

		// insert post metadata
		foreach ($post->get_post_meta() as $key => $value) {
			update_post_meta($post_id, $key, $value);
		}
		return $post_id;
	}

	public function update_post(Wp_Bracket_Builder_Custom_Post_Interface $post, $wp_error = false): int {
		$post_id = wp_update_post($post->get_update_post_data(), $wp_error);

		if (0 === $post_id || $post_id instanceof WP_Error) {
			return $post_id;
		}

		// // update post metadata
		// foreach ($post->get_update_post_meta() as $key => $value) {
		// 	update_post_meta($post_id, $key, $value);
		// }
		return $post_id;
	}
}
