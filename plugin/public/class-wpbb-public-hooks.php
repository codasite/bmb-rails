<?php
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';

class Wpbb_PublicHooks
{

	public function add_rewrite_tags() {
		add_rewrite_tag('%tab%', '([^&]+)');
		add_rewrite_tag('%view%', '([^&]+)');
	}

	public function add_rewrite_rules() {
		// Be sure to flush the rewrite rules after adding new rules
		add_rewrite_rule('^dashboard/profile/?', 'index.php?pagename=dashboard&tab=profile', 'top');
		add_rewrite_rule('^dashboard/brackets/page/([0-9]+)/?', 'index.php?pagename=dashboard&tab=brackets&paged=$matches[1]', 'top');
		add_rewrite_rule('^dashboard/brackets/?', 'index.php?pagename=dashboard&tab=brackets', 'top');
		add_rewrite_rule('^dashboard/play-history/page/([0-9]+)/?', 'index.php?pagename=dashboard&tab=play-history&paged=$matches[1]', 'top');
		add_rewrite_rule('^dashboard/play-history/?', 'index.php?pagename=dashboard&tab=play-history', 'top');
		add_rewrite_rule('^plays/([^/]+)/([^/]+)/?', 'index.php?bracket_play=$matches[1]&view=$matches[2]', 'top');
		add_rewrite_rule('^brackets/([^/]+)/([^/]+)/?', 'index.php?bracket=$matches[1]&view=$matches[2]', 'top');
	}

	public function add_query_vars($vars) {
		$vars[] = 'tab';
		$vars[] = 'status';
		$vars[] = 'view';
		return $vars;
	}

	public function template_redirect() {
		if (is_page('dashboard') && !is_user_logged_in()) {
			global $wp;
			wp_redirect(wp_login_url($wp->request));
			exit;
		}
	}

	public function add_roles() {
		add_role(
			'bmb_plus',
			'BMB Plus',	
			array(
				'wpbb_share_bracket' => true,
				'wpbb_bust_play' => true,
				'wpbb_enable_chat' => true,
			),
		);
	}


	public function user_cap_filter($allcaps, $cap, $args) {
		// check if user is admin. if so, bail
		$requested = $args[0];
		if (!str_starts_with($requested, 'wpbb_')) {
			return $allcaps;
		}
		if (isset($allcaps['administrator']) && $allcaps['administrator'] === true) {
			return $allcaps;
		}
		$dynamic_caps = [
			'wpbb_delete_bracket',
			'wpbb_edit_bracket',
		];
		if (!in_array($requested, $dynamic_caps)) {
			return $allcaps;
		}
		$user_id = $args[1];
		$post_id = $args[2];
		switch ($requested) {
			case 'wpbb_delete_bracket':
			case 'wpbb_edit_bracket':
				$post = get_post($post_id);
				if ($post->post_type === 'bracket' && (int) $post->post_author === (int) $user_id) {
					$allcaps[$cap[0]] = true;
				}
				break;
			default:
				break;
		}
		return $allcaps;
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

	public function add_bmb_plus_role(WC_Subscription $subscription) {
		$user_id = $subscription->get_user_id();
		$user = get_user_by('id', $user_id);
		$user->add_role('bmb_plus');
	}

	public function remove_bmb_plus_role(WC_Subscription $subscription) {
		$user_id = $subscription->get_user_id();
		$user = get_user_by('id', $user_id);
		$user->remove_role('bmb_plus');
	}


	public function mark_play_printed($play_id) {
		if (!$play_id) {
		  return;
		}
		$data = [
		  'is_printed' => true,
		  // maybe: 'is_printed' => 1
		];
		$play_repo = new Wpbb_BracketPlayRepo();

		$play_repo->update($play_id, $data);
	  }
}
