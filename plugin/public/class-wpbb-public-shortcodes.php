<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wpbb-bracket-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wpbb-bracket-play-repo.php';


class Wpbb_Public_Shortcodes {

	/**
	 * Render the bracket preview
	 *
	 * @return void
	 */
	public function render_bracket_preview() {
		ob_start();
		?>
		<div id="wpbb-bracket-preview-controller" style="width: 100%">
		</div>
	<?php
		return ob_get_clean();
	}

	public function render_bracket_builder() {
		wp_localize_script(
			'wpbb-bracket-builder-react',
			'wpbb_ajax_obj',
			array(
				'my_brackets_url' => get_permalink(get_page_by_path('dashboard')) . '?tab=brackets',
				'nonce' => wp_create_nonce('wp_rest'),
				'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
			)
		);
		ob_start();
	?>
		<div id="wpbb-bracket-builder"></div>
<?php

		return ob_get_clean();
	}


	public function render_dashboard() {
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/dashboard/wpbb-dashboard.php';
		return ob_get_clean();
	}

	public function render_official_brackets() {
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/wpbb-official-tournaments.php';
		return ob_get_clean();
	}

	public function render_celebrity_picks() {
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/wpbb-celebrity-picks.php';
		return ob_get_clean();
	}


	public function render_bracket_page() {
		$current_user_id = get_current_user_id();
		$post_author_id = get_post()->post_author;
		$user_is_admin = current_user_can('administrator');

		if (!$user_is_admin && $current_user_id !== $post_author_id) {
			header('HTTP/1.0 401 Unauthorized');
			ob_start();
			include('error/401.php');
			return ob_get_clean();
		}
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/wpbb-bracket-page.php';
		return ob_get_clean();
	}

	public function render_bracket_play_page() {
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/play-page/wpbb-play-page.php';
		return ob_get_clean();
	}

	public function render_print_page() {
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/print-page/wpbb-print-page.php';
		return ob_get_clean();
	}

	/**
	 * Add shortcode to render events
	 *
	 * @return void
	 */
	public function add_shortcodes() {
		add_shortcode('wpbb-bracket-builder', [$this, 'render_bracket_builder']); // This is a page with slug `bracket-template-builder
		add_shortcode('wpbb-dashboard', [$this, 'render_dashboard']); // This is a page with slug `dashboard`
		add_shortcode('wpbb-official-brackets', [$this, 'render_official_brackets']); // This is a page with slug `official-tournaments`
		add_shortcode('wpbb-celebrity-picks', [$this, 'render_celebrity_picks']); // This is a page with slug `celebrity-picks`
		add_shortcode('wpbb-bracket-page', [$this, 'render_bracket_page']); // This is a single post type template for bracket_template posts
		add_shortcode('wpbb-bracket-play', [$this, 'render_bracket_play_page']); // This is a single post type template for bracket_play posts
	}
}
