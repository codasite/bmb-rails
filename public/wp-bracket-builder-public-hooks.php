<?php


class Wp_Bracket_Builder_Public_Hooks {

	/**
	 * Sort plays by a field in the plays table
	 * 
	 * adapted from: https://wordpress.stackexchange.com/questions/4852/post-meta-vs-separate-database-tables
	 */
	public function sort_plays($clauses, $query_object) {
		$play_sort_options = [
			'total_score',
			'accuracy_score',
		];

		// Only affect queries for bracket_play post type and sorting by a valid option
		if ($query_object->get('post_type') === 'bracket_play' && in_array($query_object->get('orderby'), $play_sort_options)) {
			global $wpdb;
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wp-bracket-builder-bracket-play-repo.php';
			$play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();

			$join = &$clauses['join'];
			if (!empty($join)) $join .= ' '; // Add space only if we need to
			$join .= "JOIN {$play_repo->plays_table()} plays ON plays.post_id = {$wpdb->posts}.ID";

			$orderby = &$clauses['orderby'];
			$orderby = "plays.{$query_object->get('orderby')} {$query_object->get('order')}";
		}
		return $clauses;
	}
}
