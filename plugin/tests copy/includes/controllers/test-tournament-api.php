<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR .
  'includes/domain/class-wpbb-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/controllers/class-wpbb-bracket-tournament-api.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/class-wpbb-notification-service-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/class-wpbb-score-service-interface.php';

//namespace phpunit

class TournamentAPITest extends WPBB_UnitTestCase {
  private $tournament_repo;

  public function set_up() {
    parent::set_up();

    $this->tournament_repo = new Wpbb_BracketTournamentRepo();
  }

  public function test_create_tournament() {
    $template = self::factory()->template->create_and_get();

    $data = [
      'title' => 'Test Tournament',
      'status' => 'publish',
      'author' => 1,
      'bracket_template_id' => $template->id,
      'date' => 'test date',
    ];

    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/tournaments'
    );

    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(201, $response->get_status());
    $this->assertEquals('Test Tournament', $response->get_data()->title);
    $this->assertEquals('publish', $response->get_data()->status);
    $this->assertEquals(1, $response->get_data()->author);
    $this->assertEquals('test date', $response->get_data()->date);

    $tournament = $this->tournament_repo->get($response->get_data()->id);
    $this->assertNotNull($tournament);
  }

  public function test_create_tournament_validation_exception() {
    $data = [
      'bracket_template_id' => 1,
    ];
    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/tournaments'
    );

    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);
    $this->assertEquals(400, $response->get_status());
    $this->assertEquals('title is required', $response->get_data()['message']);
  }

  public function test_update_tournament() {
    $template = self::factory()->template->create_and_get([
      'matches' => [
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Wpbb_Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Wpbb_Team([
            'name' => 'Team 2',
          ]),
        ]),
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Wpbb_Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Wpbb_Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);
    $tournament = self::factory()->tournament->create_and_get([
      'bracket_template_id' => $template->id,
      'results' => [],
    ]);

    $data = [
      'title' => 'Test Tournament',
      'date' => 'Test Date',
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $template->matches[0]->team1->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $template->matches[1]->team2->id,
        ],
        [
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $template->matches[0]->team1->id,
        ],
      ],
    ];

    $request = new WP_REST_Request(
      'PATCH',
      '/wp-bracket-builder/v1/tournaments/' . $tournament->id
    );

    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(200, $response->get_status());
    $this->assertEquals('Test Tournament', $response->get_data()->title);
    $this->assertEquals('Test Date', $response->get_data()->date);

    $tournament = $this->tournament_repo->get($response->get_data()->id);
    $this->assertNotNull($tournament);
    $this->assertEquals('Test Tournament', $tournament->title);
    $this->assertEquals('Test Date', $tournament->date);
    $this->assertEquals(3, count($tournament->results));
    $this->assertEquals(0, $tournament->results[0]->round_index);
    $this->assertEquals(0, $tournament->results[0]->match_index);
    $this->assertEquals(
      $template->matches[0]->team1->id,
      $tournament->results[0]->winning_team_id
    );
    $this->assertEquals(0, $tournament->results[1]->round_index);
    $this->assertEquals(1, $tournament->results[1]->match_index);
    $this->assertEquals(
      $template->matches[1]->team2->id,
      $tournament->results[1]->winning_team_id
    );
    $this->assertEquals(1, $tournament->results[2]->round_index);
    $this->assertEquals(0, $tournament->results[2]->match_index);
    $this->assertEquals(
      $template->matches[0]->team1->id,
      $tournament->results[2]->winning_team_id
    );
  }

  public function test_notification_is_sent_when_results_are_updated() {
    $notification_service = $this->getMockBuilder(
      'Wpbb_Notification_Service_Interface'
    )
      ->disableOriginalConstructor()
      ->getMock();

    $api = new Wpbb_BracketTournamentApi([
      'notification_service' => $notification_service,
    ]);

    $template = self::factory()->template->create_and_get();
    $tournament = self::factory()->tournament->create_and_get([
      'bracket_template_id' => $template->id,
    ]);

    $data = [
      'title' => 'Test Tournament',
      'update_notify_players' => true,
    ];

    $request = new WP_REST_Request(
      'PATCH',
      '/wp-bracket-builder/v1/tournaments/' . $tournament->id
    );

    $request->set_body_params($data);
    $request->set_param('item_id', $tournament->id);

    $notification_service
      ->expects($this->once())
      ->method('notify_bracket_results_updated')
      ->with($tournament->id);

    $api->update_item($request);
  }

  public function test_tournament_is_scored_on_update_results() {
    $score_service = $this->getMockBuilder('Wpbb_Score_Service_Interface')
      ->disableOriginalConstructor()
      ->getMock();

    $api = new Wpbb_BracketTournamentApi(['score_service' => $score_service]);

    $template = self::factory()->template->create_and_get();
    $tournament = self::factory()->tournament->create_and_get([
      'bracket_template_id' => $template->id,
    ]);

    $data = [
      'title' => 'Test Tournament',
    ];

    $request = new WP_REST_Request(
      'PATCH',
      '/wp-bracket-builder/v1/tournaments/' . $tournament->id
    );

    $request->set_body_params($data);
    $request->set_param('item_id', $tournament->id);

    $score_service->expects($this->once())->method('score_bracket_plays');

    $api->update_item($request);
  }
}
