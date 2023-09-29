<?php


class Wp_Bracket_Builder_Public_Hooks {

	public function add_rewrite_tags() {
		add_rewrite_tag('%tab%', '([^&]+)');
		add_rewrite_tag('%posttype%', '([^&]+)');
		add_rewrite_tag('%slug%', '([^&]+)');
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
		add_rewrite_rule('^print/([^/]+)/([^/]+)/?', 'index.php?pagename=print&posttype=$matches[1]&slug=$matches[2]', 'top');
		// add_rewrite_rule('^print/([^/]+)/?', 'index.php?pagename=print&posttype=$matches[1]', 'top');
	}

	public function add_query_vars($vars) {
		$vars[] = 'tab';
		$vars[] = 'status';
		$vars[] = 'view';
		$vars[] = 'posttype';
		$vars[] = 'slug';
		$vars[] = 'theme';
		$vars[] = 'position';
		$vars[] = 'inch_height';
		$vars[] = 'inch_width';
		return $vars;
	}

	public function add_roles() {
		add_role(
			'bmb_plus',
			'BMB Plus',
			array('wpbb_create_tournament' => true),
		);

		// This role is to be used by the service user to generate bracket images
		add_role(
			'private_reader',
			'Private Reader',
			array(
				'read' => true,
				'read_private_posts' => true,
			)
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

	public function print_redirect() {
		if (is_user_logged_in()) {
			return;
		}

		$uri = $_SERVER['REQUEST_URI'];
		$path = parse_url($uri, PHP_URL_PATH);

		$service_paths = [
			'redirect-test',
			'print',
		];

		$matching_page = null;

		foreach ($service_paths as $path) {
			if (strpos($path, $path) !== false) {
				$matching_page = $path;
				break;
			}
		}

		if ($matching_page === null) {
			return;
		}

		$service_user = get_user_by('login', SERVICE_LOGIN);

		if (!$service_user) {
			return;
		}

		wp_clear_auth_cookie();
		wp_set_current_user($service_user->ID);
		wp_set_auth_cookie($service_user->ID);

		$redirect_to = get_permalink(get_page_by_path($matching_page));

		if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
			$redirect_to = add_query_arg($this->esc_query_args($_SERVER['QUERY_STRING']), $redirect_to);
		}

		if ($redirect_to) {
			wp_safe_redirect($redirect_to);
			exit;
		}
	}

	private function esc_query_args($query_string) {
		parse_str($query_string, $query_args);
		return array_map('esc_attr', $query_args);
	}
}
