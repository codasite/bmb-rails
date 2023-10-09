<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wp-bracket-builder-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wp-bracket-builder-bracket-tournament-repo.php';

class TournamentAPITest extends WPBB_UnitTestCase {

	private $tournament_repo;

	public function set_up() {
		parent::set_up();

		$this->tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
	}

	public function test_create_tournament() {
		$template = self::factory()->template->create_and_get();

		$data = [
			'title' => 'Test Tournament',
			'status' => 'publish',
			'author' => 1,
			'bracket_template_id' => $template->id,
		];

		$request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/tournaments');

		$request->set_body_params($data);
		$request->set_header('Content-Type', 'application/json');
		$request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

		$response = rest_do_request($request);

		$this->assertEquals(201, $response->get_status());
		$this->assertEquals('Test Tournament', $response->get_data()->title);
		$this->assertEquals('publish', $response->get_data()->status);
		$this->assertEquals(1, $response->get_data()->author);

		$tournament = $this->tournament_repo->get($response->get_data()->id);
		$this->assertNotNull($tournament);
	}


	public function test_update_tournament() {

		$template = self::factory()->template->create_and_get();
		$tournament = self::factory()->tournament->create_and_get([
			'bracket_template_id' => $template->id
		]);

		$data = [
			'title' => 'Test Tournament',
		];

		$request = new WP_REST_Request('PATCH', '/wp-bracket-builder/v1/tournaments/' . $tournament->id);

		$request->set_body_params($data);
		$request->set_header('Content-Type', 'application/json');
		$request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

		$response = rest_do_request($request);

		$this->assertEquals(200, $response->get_status());
		$this->assertEquals('Test Tournament', $response->get_data()->title);

		$tournament = $this->tournament_repo->get($response->get_data()->id);
		$this->assertNotNull($tournament);
		$this->assertEquals('Test Tournament', $tournament->title);
	}

	public function test_notification_is_sent_when_results_are_updated() {
	}
}
