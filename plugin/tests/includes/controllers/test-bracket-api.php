<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/controllers/class-wpbb-bracket-api.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/class-wpbb-notification-service-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/class-wpbb-score-service-interface.php';

//namespace phpunit

class BracketAPITest extends WPBB_UnitTestCase {
  const BRACKET_API_ENDPOINT = '/wp-bracket-builder/v1/brackets';
  private $bracket_repo;

  public function set_up() {
    parent::set_up();

    $this->bracket_repo = new Wpbb_BracketRepo();
  }

  public function test_create_bracket() {
    $data = [
      'title' => 'Test Bracket',
      'status' => 'publish',
      'author' => 1,
      'date' => 'test date',
      'num_teams' => 8,
      'wildcard_placement' => 0,
      'matches' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'team1' => [
            'name' => 'Team 1',
          ],
          'team2' => [
            'name' => 'Team 2',
          ],
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'team1' => [
            'name' => 'Team 3',
          ],
          'team2' => [
            'name' => 'Team 4',
          ],
        ],
      ],
    ];
    $request = new WP_REST_Request('POST', self::BRACKET_API_ENDPOINT);
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $this->assertEquals(201, $response->get_status());
    $this->assertEquals('Test Bracket', $response->get_data()->title);
    $this->assertEquals('publish', $response->get_data()->status);
    $this->assertEquals(1, $response->get_data()->author);
    $this->assertEquals('test date', $response->get_data()->date);
    $this->assertEquals(8, $response->get_data()->num_teams);
    $this->assertEquals(0, $response->get_data()->wildcard_placement);
    $this->assertEquals(2, count($response->get_data()->matches));
    $this->assertEquals(0, $response->get_data()->matches[0]->round_index);
    $this->assertEquals(0, $response->get_data()->matches[0]->match_index);
    $this->assertEquals(
      'Team 1',
      $response->get_data()->matches[0]->team1->name
    );
    $this->assertEquals(
      'Team 2',
      $response->get_data()->matches[0]->team2->name
    );
    $this->assertEquals(0, $response->get_data()->matches[1]->round_index);
    $this->assertEquals(1, $response->get_data()->matches[1]->match_index);
    $this->assertEquals(
      'Team 3',
      $response->get_data()->matches[1]->team1->name
    );
    $this->assertEquals(
      'Team 4',
      $response->get_data()->matches[1]->team2->name
    );
    $bracket = $this->bracket_repo->get($response->get_data()->id);
    $this->assertNotNull($bracket);
  }

  public function test_create_bracket_current_user_is_author() {
    $data = [
      'title' => 'Test Bracket',
      'status' => 'publish',
      'date' => 'test date',
      'num_teams' => 8,
      'wildcard_placement' => 0,
      'matches' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'team1' => [
            'name' => 'Team 1',
          ],
          'team2' => [
            'name' => 'Team 2',
          ],
        ],
      ],
    ];
    $request = new WP_REST_Request('POST', self::BRACKET_API_ENDPOINT);
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $this->assertEquals(201, $response->get_status());
    $this->assertEquals(get_current_user_id(), $response->get_data()->author);

    $bracket = $this->bracket_repo->get($response->get_data()->id);
    $this->assertNotNull($bracket);
    $this->assertEquals(get_current_user_id(), $bracket->author);
  }

  public function test_create_bracket_validation_exception() {
    $request = new WP_REST_Request('POST', self::BRACKET_API_ENDPOINT);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);
    $this->assertEquals(400, $response->get_status());
    $this->assertEquals(
      'num_teams, wildcard_placement, title, matches is required',
      $response->get_data()['message']
    );
  }

  public function test_update_bracket() {
    $bracket = self::factory()->bracket->create_and_get([
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

    $data = [
      'title' => 'Test Bracket',
      'date' => 'Test Date',
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team2->id,
        ],
        [
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ];

    $request = new WP_REST_Request(
      'PATCH',
      self::BRACKET_API_ENDPOINT . '/' . $bracket->id
    );

    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(200, $response->get_status());
    $this->assertEquals('Test Bracket', $response->get_data()->title);
    $this->assertEquals('Test Date', $response->get_data()->date);

    $bracket = $this->bracket_repo->get($response->get_data()->id);
    $this->assertNotNull($bracket);
    $this->assertEquals('Test Bracket', $bracket->title);
    $this->assertEquals('Test Date', $bracket->date);
    $this->assertEquals(3, count($bracket->results));
    $this->assertEquals(0, $bracket->results[0]->round_index);
    $this->assertEquals(0, $bracket->results[0]->match_index);
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $bracket->results[0]->winning_team_id
    );
    $this->assertEquals(0, $bracket->results[1]->round_index);
    $this->assertEquals(1, $bracket->results[1]->match_index);
    $this->assertEquals(
      $bracket->matches[1]->team2->id,
      $bracket->results[1]->winning_team_id
    );
    $this->assertEquals(1, $bracket->results[2]->round_index);
    $this->assertEquals(0, $bracket->results[2]->match_index);
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $bracket->results[2]->winning_team_id
    );
  }

  public function test_notification_is_sent_when_results_are_updated() {
    $notification_service = $this->getMockBuilder(
      'Wpbb_Notification_Service_Interface'
    )
      ->disableOriginalConstructor()
      ->getMock();

    $api = new Wpbb_BracketApi([
      'notification_service' => $notification_service,
    ]);

    $bracket = self::factory()->bracket->create_and_get();

    $data = [
      'title' => 'Test Bracket',
      'update_notify_participants' => true,
    ];

    $request = new WP_REST_Request(
      'PATCH',
      self::BRACKET_API_ENDPOINT . $bracket->id
    );

    $request->set_body_params($data);
    $request->set_param('item_id', $bracket->id);

    $notification_service
      ->expects($this->once())
      ->method('notify_bracket_results_updated')
      ->with($bracket->id);

    $api->update_item($request);
  }

  public function test_bracket_is_scored_on_update_results() {
    $score_service = $this->getMockBuilder('Wpbb_Score_Service_Interface')
      ->disableOriginalConstructor()
      ->getMock();

    $api = new Wpbb_BracketApi(['score_service' => $score_service]);

    $bracket = self::factory()->bracket->create_and_get();

    $data = [
      'title' => 'Test Bracket',
    ];

    $request = new WP_REST_Request(
      'PATCH',
      self::BRACKET_API_ENDPOINT . $bracket->id
    );

    $request->set_body_params($data);
    $request->set_param('item_id', $bracket->id);

    $score_service->expects($this->once())->method('score_bracket_plays');

    $api->update_item($request);
  }
}
