<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wpbb-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repository/class-wpbb-bracket-play-repo.php';


class Wpbb_Public_Shortcodes
{

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

	public function render_options_bracket_preview() {
		ob_start();
	?>
		<div id="wpbb-bracket-option-preview" style="width: 100%">
		</div>
	<?php
		return ob_get_clean();
	}

	public function render_template_builder() {
		wp_localize_script(
			'wpbb-bracket-builder-react',
			'wpbb_ajax_obj',
			array(
				'my_templates_url' => get_permalink(get_page_by_path('dashboard')) . '?tab=templates',
				'my_tournaments_url' => get_permalink(get_page_by_path('dashboard')) . '?tab=tournaments',
				'nonce' => wp_create_nonce('wp_rest'),
				'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
			)
		);
		ob_start();
	?>
		<div id="wpbb-template-builder">
		</div>
<?php

		return ob_get_clean();
	}


	public function render_dashboard() {
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/dashboard/wpbb-dashboard.php';

		wp_localize_script(
			'wpbb-bracket-builder-react',
			'wpbb_ajax_obj',
			array(
				'my_templates_url' => get_permalink() . 'templates',
				'my_tournaments_url' => get_permalink() . 'tournaments',
				'bracket_template_builder_url' => get_permalink(get_page_by_path('bracket-template-builder')),
				'home_url' => get_home_url(),
				'user_can_create_tournament' => current_user_can('wpbb_create_tournament'),
				'nonce' => wp_create_nonce('wp_rest'),
				'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
			)
		);

		return ob_get_clean();
	}

	public function render_official_tournamnets() {
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/wpbb-official-tournaments.php';

		return ob_get_clean();
	}

	public function render_celebrity_picks() {
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/wpbb-celebrity-picks.php';

		return ob_get_clean();
	}


	public function render_bracket_template_page() {
		$post = get_post();
		if (!$post || $post->post_type !== 'bracket_template') {
			return
				'<div class="alert alert-danger" role="alert">
					Template not found.
				</div>';
		}
		$template_repo = new Wpbb_BracketTemplateRepo();
		$template = $template_repo->get(post: $post);
		$play_history_url = get_permalink(get_page_by_path('dashboard')) . '?tab=play-history';

		// $bracket_product_archive_url = $this->get_archive_url();
		$css_file = plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.css';

		wp_localize_script(
			'wpbb-bracket-builder-react',
			'wpbb_ajax_obj',
			array(
				'template' => $template,
				// 'sentry_env' => $sentry_env,
				// 'sentry_dsn' => $sentry_dsn,
				'my_templates_url' => get_permalink(get_page_by_path('dashboard')) . '?tab=templates',
				'my_tournaments_url' => get_permalink(get_page_by_path('dashboard')) . '?tab=tournaments',
				'nonce' => wp_create_nonce('wp_rest'),
				'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
				'css_file' => $css_file,
				'redirect_url' => $play_history_url, // used to redirect to bracket-ready category page
				// 'bracket_product_archive_url' => $bracket_product_archive_url, // used to redirect to bracket-ready category page
			)
		);
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/wpbb-bracket-template-page.php';
		return ob_get_clean();
	}

	public function render_bracket_tournament_page() {
		$post = get_post();
		if (!$post || $post->post_type !== 'bracket_tournament') {
			return
				'<div class="alert alert-danger" role="alert">
					Tournament not found.
				</div>';
		}
		$tournament_repo = new Wpbb_BracketTournamentRepo();
		$tournament = $tournament_repo->get(post: $post);
		$play_history_url = get_permalink(get_page_by_path('dashboard')) . '?tab=play-history';
		$my_tournaments_url = get_permalink(get_page_by_path('dashboard')) . '?tab=tournaments';

		// $bracket_product_archive_url = $this->get_archive_url();
		$css_file = plugin_dir_url(dirname(__FILE__)) . 'includes/react-bracket-builder/build/index.css';

		wp_localize_script(
			'wpbb-bracket-builder-react',
			'wpbb_ajax_obj',
			array(
				'tournament' => $tournament,
				// 'sentry_env' => $sentry_env,
				// 'sentry_dsn' => $sentry_dsn,
				'nonce' => wp_create_nonce('wp_rest'),
				'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
				'css_file' => $css_file,
				'redirect_url' => $play_history_url, // used to redirect to bracket-ready category page
				'my_tournaments_url' => $my_tournaments_url, // used to redirect back to my tournaments page
			)
		);
		ob_start();
		include plugin_dir_path(__FILE__) . 'partials/wpbb-bracket-tournament-page.php';

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
		add_shortcode('wpbb-template-builder', [$this, 'render_template_builder']); // This is a page with slug `bracket-template-builder
		add_shortcode('wpbb-dashboard', [$this, 'render_dashboard']); // This is a page with slug `dashboard`
		add_shortcode('wpbb-official-tournaments', [$this, 'render_official_tournamnets']); // This is a page with slug `official-tournaments`
		add_shortcode('wpbb-celebrity-picks', [$this, 'render_celebrity_picks']); // This is a page with slug `celebrity-picks`
		add_shortcode('wpbb-bracket-template', [$this, 'render_bracket_template_page']); // This is a single post type template for bracket_template posts
		add_shortcode('wpbb-bracket-tournament', [$this, 'render_bracket_tournament_page']); // This is a single post type template for bracket_tournament posts
		add_shortcode('wpbb-bracket-play', [$this, 'render_bracket_play_page']); // This is a single post type template for bracket_play posts
		add_shortcode('wpbb-print-page', [$this, 'render_print_page']); // This is a page with slug `print` used to generate bracket images
	}
}
