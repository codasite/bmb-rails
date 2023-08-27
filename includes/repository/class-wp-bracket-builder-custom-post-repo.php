<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-custom-post-interface.php';

// interface Wp_Bracket_Builder_Repository_Interface {
// 	public function add(mixed $obj): mixed;
// 	public function get(int $id): ?mixed;
// 	public function get_all(): array;
// 	public function delete(int $id): bool;
// }

// abstract class Wp_Bracket_Builder_Custom_Post_Repository_Base implements Wp_Bracket_Builder_Repository_Interface {
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
}
