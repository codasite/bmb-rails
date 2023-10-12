<?php


class Wpbb_Public_Hooks
{

	public function add_rewrite_tags() {
		add_rewrite_tag('%tab%', '([^&]+)');
	}

	public function add_rewrite_rules() {
		// Be sure to flush the rewrite rules after adding new rules
		add_rewrite_rule('^dashboard/profile/?', 'index.php?pagename=dashboard&tab=profile', 'top');
		add_rewrite_rule('^dashboard/templates/page/([0-9]+)/?', 'index.php?pagename=dashboard&tab=templates&paged=$matches[1]', 'top');
		add_rewrite_rule('^dashboard/templates/?', 'index.php?pagename=dashboard&tab=templates', 'top');
		add_rewrite_rule('^dashboard/tournaments/page/([0-9]+)/?', 'index.php?pagename=dashboard&tab=tournaments&paged=$matches[1]', 'top');
		add_rewrite_rule('^dashboard/tournaments/?', 'index.php?pagename=dashboard&tab=tournaments', 'top');
		add_rewrite_rule('^dashboard/play-history/page/([0-9]+)/?', 'index.php?pagename=dashboard&tab=play-history&paged=$matches[1]', 'top');
		add_rewrite_rule('^dashboard/play-history/?', 'index.php?pagename=dashboard&tab=play-history', 'top');
		add_rewrite_rule('^tournaments/([^/]+)/([^/]+)/?', 'index.php?bracket_tournament=$matches[1]&view=$matches[2]', 'top');
		add_rewrite_rule('^plays/([^/]+)/([^/]+)/?', 'index.php?bracket_play=$matches[1]&view=$matches[2]', 'top');
		add_rewrite_rule('^templates/([^/]+)/([^/]+)/?', 'index.php?bracket_template=$matches[1]&view=$matches[2]', 'top');
	}

	public function add_query_vars($vars) {
		$vars[] = 'tab';
		$vars[] = 'status';
		$vars[] = 'view';
		return $vars;
	}

	public function add_roles() {
		add_role(
			'bmb_plus',
			'BMB Plus',
			array('wpbb_create_tournament' => true),
		);
	}

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
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wpbb-bracket-play-repo.php';
			$play_repo = new Wpbb_BracketPlayRepo();

			$join = &$clauses['join'];
			if (!empty($join)) $join .= ' '; // Add space only if we need to
			$join .= "JOIN {$play_repo->plays_table()} plays ON plays.post_id = {$wpdb->posts}.ID";

			$orderby = &$clauses['orderby'];
			$orderby = "plays.{$query_object->get('orderby')} {$query_object->get('order')}";
		}
		return $clauses;
	}
}
