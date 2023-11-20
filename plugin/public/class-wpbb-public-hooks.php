<?php
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/service/custom-query/class-wpbb-play-query.php';

class Wpbb_PublicHooks
{

	private $play_repo;
	private $bracket_repo;
	private $play_query;
	private $utils;

	public function __construct($opts = [])
	{
		$this->play_query = $opts['play_query'] ?? new Wpbb_CustomPlayQuery();
		$this->utils = $opts['utils'] ?? new Wpbb_Utils();
		$this->play_repo = $opts['play_repo'] ?? new Wpbb_BracketPlayRepo();
		$this->bracket_repo = $opts['bracket_repo'] ?? new Wpbb_BracketRepo();
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
		add_role(
			'bmb_vip',
			'BMB VIP',
			array(
				'wpbb_share_bracket' => true,
				'wpbb_bust_play' => true,
				'wpbb_enable_chat' => true,
			),
		);
	}


	/**
	 * Authorization checks. Be sure to add any new caps to the admin role
	 */
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
			'wpbb_play_bracket',
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
			case 'wpbb_play_bracket':
				$bracket = $this->bracket_repo->get($post_id);
				$can_play = false;
				$playable_status = ['publish', 'score', 'complete'];
				if (in_array($bracket->status, $playable_status)) {
					$can_play = true;
				} else if ($bracket->status === 'private') {
					if ($bracket->author === (int) $user_id) {
						$can_play = true;
					}
				} 
				$allcaps[$cap[0]] = $can_play;
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

		$this->play_repo->update($play_id, $data);
	  }

	/**
	 * this function gets hooked to the 'wp_login' action
	 */
	public function link_anonymous_bracket_to_user_on_login($user_login, WP_User $user) {
		$this->link_anonymous_post_to_user_from_cookie($user->ID, 'wpbb_anonymous_bracket_id', 'wpbb_anonymous_bracket_key');
	}

	public function link_anonymous_bracket_to_user_on_register($user_id) {
		$this->link_anonymous_post_to_user_from_cookie($user_id, 'wpbb_anonymous_bracket_id', 'wpbb_anonymous_bracket_key');
	}

	public function link_anonymous_play_to_user_on_login($user_login, WP_User $user) {
		$this->link_anonymous_post_to_user_from_cookie($user->ID, 'play_id', 'wpbb_anonymous_play_key');
	}

	public function link_anonymous_play_to_user_on_register($user_id) {
		$this->link_anonymous_post_to_user_from_cookie($user_id, 'play_id', 'wpbb_anonymous_play_key');
	}

	// this is hooked by the 'wpbb_after_printed_play' action
	public function link_anonymous_printed_play_to_user($play_id, $user_id) {
		$this->link_anonymous_post_to_user($play_id, $user_id);
	}

	public function link_anonymous_post_to_user_from_cookie($user_id, $cookie_id_name, $cookie_verify_key_name) {
		$post_id = $this->utils->pop_cookie($cookie_id_name);
		$cookie_key = $this->utils->pop_cookie($cookie_verify_key_name);
		$post_meta = get_post_meta($post_id, $cookie_verify_key_name);
		if (isset($post_meta) && !empty($post_meta)) {
			$meta_key = $post_meta[0];
		} else {
			return;
		}

		if ($cookie_key !== $meta_key) {
			return;
		}

		$this->link_anonymous_post_to_user($post_id, $user_id);
	}

	public function link_anonymous_post_to_user($post_id, $user_id) {
		$post = get_post($post_id);
		if (!$post) {
			return;
		}

		if ((int) $post->post_author === 0) {
			wp_update_post([
				'ID' => $post_id,
				'post_author' => $user_id,
			]);
		}
	}

	// This hooks into `woocommerce_cart_calculate_fees` action
	public function add_paid_bracket_fee_to_cart($cart) {
		// check if the cart contains a bracket-ready product
		// if so, get the bracket id from the cart item data 
		// then check if the bracket is associated with one of the "fee" tags. ('bmb-fee-1', 'bmb-fee-2', etc.)
		// if so, find the fee product associated with that tag and add it to the cart
	}
}
