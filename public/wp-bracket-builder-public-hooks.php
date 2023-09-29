<?php


class Wp_Bracket_Builder_Public_Hooks {

	public function add_rewrite_tags() {
		add_rewrite_tag('%tab%', '([^&]+)');
		add_rewrite_tag('%pagename%', '([^&]+)');
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
		add_rewrite_rule('^bracket_tournament/([^/]+)/([^/]+)', 'index.php?bracket_tournament=$matches[1]&view=$matches[2]', 'top');
		add_rewrite_rule('^bracket_play/([^/]+)/([^/]+)', 'index.php?bracket_play=$matches[1]&view=$matches[2]', 'top');
		add_rewrite_rule('^bracket_template/([^/]+)/([^/]+)', 'index.php?bracket_template=$matches[1]&view=$matches[2]', 'top');
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

	private $key = 'my_secret_key';
	private $iv = 'my_secret_iv';

	private function hash_slug($slug) {
		$hashed_slug = openssl_encrypt($slug, 'AES-256-CBC', $this->key, 0, $this->iv);
		$base64_encoded_slug = base64_encode($hashed_slug);
		return $base64_encoded_slug;
	}

	private function unhash_slug($hashed) {
		// $slug = 'official-tournament';
		echo 'unhashing';
		echo $hashed;
		$decoded_slug = (string) base64_decode($hashed);
		echo 'decoded' . $decoded_slug;
		$slug = openssl_decrypt($decoded_slug, 'AES-256-CBC', $this->key, 0, $this->iv);
		echo 'slug' . $slug;
		return $slug;
	}

	public function hash_tournament_slug($permalink, $post) {
		if ($post->post_type === 'bracket_tournament') {
			$slug = $post->post_name;
			$hashed_slug = $this->hash_slug($slug);
			$permalink = home_url('/bracket_tournament/' . $hashed_slug . '/'); //. $post->post_name);
		}
		return $permalink;
	}

	public function unhash_tournament_slug($query) {
		// if ($query->is_main_query() && $query->is_singular('bracket_tournament')) {
		if (!$query->is_main_query()) {
			return;
		}
		$hashed = get_query_var('bracket_tournament');
		if (empty($hashed)) {
			return;
		}
		echo 'unhashing';
		$slug = $this->unhash_slug($hashed);
		$query->set('bracket_tournament', $slug);
		// if ($query->is_main_query()) { //} && $query->is_post_type_archive('bracket_tournament')) {
		// 	$hashed_slug = get_query_var('bracket_tournament');
		// 	echo $hashed_slug;
		// 	$slug = $this->unhash_slug($hashed_slug);
		// 	$query->set('bracket_tournament', $slug);
		// }
	}
}
