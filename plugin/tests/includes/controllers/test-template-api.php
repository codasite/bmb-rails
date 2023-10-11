<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wp-bracket-builder-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/controllers/class-wp-bracket-builder-bracket-tournament-api.php';
require_once WPBB_PLUGIN_DIR . 'includes/service/class-wpbb-notification-service-interface.php';
require_once WPBB_PLUGIN_DIR . 'includes/service/class-wpbb-score-service-interface.php';

//namespace phpunit

class TemplateAPITest extends WPBB_UnitTestCase
{

	private Wpbb_BracketTemplateRepo $template_repo;
	const TEMPLATE_API_ENDPOINT = '/wp-bracket-builder/v1/templates';

	public function set_up() {
		parent::set_up();

		$this->template_repo = new Wpbb_BracketTemplateRepo();
	}

	public function test_create_template() {
		$data = [
			'title' => 'Test Template',
			'status' => 'publish',
			'author' => 1,
			'date' => 'test date',
			'num_teams' => 8,
			'wildcard_placement' => 0,
			'matches' => []
		];
		$request = new WP_REST_Request('POST', self::TEMPLATE_API_ENDPOINT);
		$request->set_body_params($data);
		$request->set_header('Content-Type', 'application/json');
		$request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
		$response = rest_do_request($request);
		$this->assertEquals(201, $response->get_status());
		$this->assertEquals('Test Template', $response->get_data()->title);
		$this->assertEquals('publish', $response->get_data()->status);
		$this->assertEquals(1, $response->get_data()->author);
		$this->assertEquals('test date', $response->get_data()->date);
		$this->assertEquals(8, $response->get_data()->num_teams);
		$this->assertEquals(0, $response->get_data()->wildcard_placement);
		$this->assertEquals([], $response->get_data()->matches);
		$template = $this->template_repo->get($response->get_data()->id);
		$this->assertNotNull($template);
	}


	public function test_update_template() {
		$template = self::factory()->template->create_and_get();

		$data = [
			'title' => 'Test Template',
			'date' => 'Test Date',
			'matches' => [],
		];

		$request = new WP_REST_Request('PATCH', self::TEMPLATE_API_ENDPOINT . '/' . $template->id);

		$request->set_body_params($data);
		$request->set_header('Content-Type', 'application/json');
		$request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

		$response = rest_do_request($request);

		$this->assertEquals(200, $response->get_status());
		$this->assertEquals('Test Template', $response->get_data()->title);
		$this->assertEquals('Test Date', $response->get_data()->date);

		$tournament = $this->template_repo->get($response->get_data()->id);
		$this->assertNotNull($tournament);
		$this->assertEquals('Test Template', $tournament->title);
	}
}
