<?php
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/service/custom-query/class-wpbb-play-query.php';

class Wpbb_PublicHooks
{

	private $play_query;
	private $utils;

	public function __construct($opts = [])
	{
		$this->play_query = $opts['play_query'] ?? new Wpbb_CustomPlayQuery();
		$this->utils = $opts['utils'] ?? new Wpbb_Utils();
	}

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

	public function custom_query_fields($clauses, $query_object) {
		if ($query_object->get('post_type') === 'bracket_play') {
			return $this->play_query->handle_custom_query($clauses, $query_object);
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
		];
		$play_repo = new Wpbb_BracketPlayRepo();

		$play_repo->update($play_id, $data);
	  }

	/**
	 * this function gets hooked to the 'wp_login' action
	 */
	public function link_anonymous_bracket_to_user_on_login($user_login, WP_User $user) {
		$this->link_anonymous_bracket_to_user($user->ID);
	}

	public function link_anonymous_bracket_to_user_on_register($user_id) {
		$this->link_anonymous_bracket_to_user($user_id);
	}

	private function link_anonymous_bracket_to_user(int $user_id) {
		$bracket_id = $this->utils->pop_cookie('bracket_id');

		$bracket = get_post($bracket_id);
		$cookie_bracket_nonce = $this->utils->pop_cookie('anonymous_bracket_nonce');
		$session_bracket_nonce = get_post_meta($bracket_id, 'anonymous_bracket_nonce');
		if (!$bracket_id) {
			return;
		}

		$bracket_repo = new Wpbb_BracketRepo();
		$bracket = $bracket_repo->get($bracket_id);

		if ($bracket->author === 0) {
			$bracket_repo->update($bracket_id, ['author'=> $user_id]);
		}
	}
}
