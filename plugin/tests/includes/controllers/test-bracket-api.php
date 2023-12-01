<?php

use WStrategies\BMB\Includes\Controllers\BracketApi;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Service\NotificationServiceInterface;
use WStrategies\BMB\Includes\Service\ScoreServiceInterface;
use WStrategies\BMB\Includes\Utils;

//namespace phpunit

class BracketAPITest extends WPBB_UnitTestCase {
  const BRACKET_API_ENDPOINT = '/wp-bracket-builder/v1/brackets';
  private $bracket_repo;

  public function set_up() {
    parent::set_up();

    $this->bracket_repo = new BracketRepo();
  }

  public function test_create_bracket() {
    $data = [
      'title' => 'Test Bracket',
      'status' => 'publish',
      'author' => 1,
      'month' => 'test month',
      'year' => 'test year',
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
    $this->assertEquals('test month', $response->get_data()->month);
    $this->assertEquals('test year', $response->get_data()->year);
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
      'month' => 'test month',
      'year' => 'test year',
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
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);

    $data = [
      'title' => 'Test Bracket',
      'month' => 'Test Month',
      'year' => 'Test Year',
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

    $bracket = $this->bracket_repo->get($response->get_data()->id);
    $this->assertNotNull($bracket);
    $this->assertEquals('Test Bracket', $bracket->title);
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

  public function test_author_can_edit_bracket() {
    $user = self::factory()->user->create_and_get();
    wp_set_current_user($user->ID);
    $bracket = self::factory()->bracket->create_and_get([
      'author' => $user->ID,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $data = [
      'title' => 'Test Bracket',
      'month' => 'Test Month',
      'year' => 'Test Year',
    ];

    $request = new WP_REST_Request(
      'PATCH',
      self::BRACKET_API_ENDPOINT . '/' . $bracket->id
    );

    $request->set_body_params($data);
    $request->set_param('item_id', $bracket->id);

    $response = rest_do_request($request);

    $this->assertEquals(200, $response->get_status());
    $this->assertEquals('Test Bracket', $response->get_data()->title);
    $this->assertEquals('Test Month', $response->get_data()->month);
    $this->assertEquals('Test Year', $response->get_data()->year);

    $bracket = $this->bracket_repo->get($response->get_data()->id);
    $this->assertNotNull($bracket);
    $this->assertEquals('Test Bracket', $bracket->title);
    $this->assertEquals('Test Month', $bracket->month);
    $this->assertEquals('Test Year', $bracket->year);
  }

  public function test_non_author_cannot_edit_bracket() {
    $user = self::factory()->user->create_and_get();
    wp_set_current_user($user->ID);
    $bracket = self::factory()->bracket->create_and_get([
      'author' => $user->ID + 1,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $data = [
      'title' => 'Test Bracket',
      'month' => 'Test Month',
      'year' => 'Test Year',
    ];

    $request = new WP_REST_Request(
      'PATCH',
      self::BRACKET_API_ENDPOINT . '/' . $bracket->id
    );

    $request->set_body_params($data);
    $request->set_param('item_id', $bracket->id);

    $response = rest_do_request($request);

    $this->assertEquals(403, $response->get_status());

    $bracket = $this->bracket_repo->get($bracket->id);
    $this->assertNotNull($bracket);
    $this->assertNotEquals('Test Bracket', $bracket->title);
    $this->assertNotEquals('Test Month', $bracket->month);
    $this->assertNotEquals('Test Year', $bracket->year);
  }

  public function test_delete_bracket_is_soft() {
    $bracket = self::factory()->bracket->create_and_get([
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $request = new WP_REST_Request(
      'DELETE',
      self::BRACKET_API_ENDPOINT . '/' . $bracket->id
    );

    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(200, $response->get_status());

    $bracket = $this->bracket_repo->get($bracket->id);
    $this->assertNotNull($bracket);
    $this->assertEquals('trash', $bracket->status);
  }

  public function test_author_can_delete_bracket() {
    $user = self::factory()->user->create_and_get();
    wp_set_current_user($user->ID);
    $bracket = self::factory()->bracket->create_and_get([
      'author' => $user->ID,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $request = new WP_REST_Request(
      'DELETE',
      self::BRACKET_API_ENDPOINT . '/' . $bracket->id
    );

    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(200, $response->get_status());

    $bracket = $this->bracket_repo->get($bracket->id);
    $this->assertNotNull($bracket);
    $this->assertEquals('trash', $bracket->status);
  }

  public function test_non_author_cannot_delete_bracket() {
    $user = self::factory()->user->create_and_get();
    wp_set_current_user($user->ID);
    $bracket = self::factory()->bracket->create_and_get([
      'author' => $user->ID + 1,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $request = new WP_REST_Request(
      'DELETE',
      self::BRACKET_API_ENDPOINT . '/' . $bracket->id
    );

    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(403, $response->get_status());

    $bracket = $this->bracket_repo->get($bracket->id);
    $this->assertNotNull($bracket);
    $this->assertEquals('publish', $bracket->status);
  }

  public function test_notification_is_sent_when_results_are_updated() {
    $notification_service = $this->getMockBuilder(
      NotificationServiceInterface::class
    )
      ->disableOriginalConstructor()
      ->getMock();

    $api = new BracketApi([
      'notification_service' => $notification_service,
    ]);

    $bracket = self::factory()->bracket->create_and_get([
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $data = [
      'title' => 'Test Bracket',
      'update_notify_players' => true,
      'results' => [
        [
          'round_index' => 0,
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
    $request->set_param('item_id', $bracket->id);

    $notification_service
      ->expects($this->once())
      ->method('notify_bracket_results_updated')
      ->with($bracket->id);

    $res = $api->update_item($request);
  }

  public function test_bracket_is_scored_on_update_results() {
    $score_service = $this->getMockBuilder(ScoreServiceInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $api = new BracketApi(['score_service' => $score_service]);

    $bracket = self::factory()->bracket->create_and_get([
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $data = [
      'title' => 'Test Bracket',
      'results' => [
        [
          'round_index' => 0,
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
    $request->set_param('item_id', $bracket->id);

    $score_service->expects($this->once())->method('score_bracket_plays');

    $api->update_item($request);
  }
  public function test_user_with_permission_can_create_published_bracket() {
    $user = self::factory()->user->create_and_get();
    $user->add_cap('wpbb_share_bracket');
    wp_set_current_user($user->ID);

    $data = [
      'title' => 'Test Bracket',
      'status' => 'publish',
      'month' => 'test month',
      'year' => 'test year',
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
    $this->assertEquals('publish', $response->get_data()->status);
  }
  public function test_user_without_permission_cannot_create_published_bracket() {
    $user = self::factory()->user->create_and_get();
    $user->remove_cap('wpbb_share_bracket');
    wp_set_current_user($user->ID);

    $data = [
      'title' => 'Test Bracket',
      'status' => 'publish',
      'month' => 'test month',
      'year' => 'test year',
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
    $this->assertEquals('private', $response->get_data()->status);
  }

  public function test_user_with_permission_can_publish_bracket() {
    $user = self::factory()->user->create_and_get();
    $user->add_cap('wpbb_share_bracket');
    wp_set_current_user($user->ID);

    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'private',
      'author' => $user->ID,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $data = [
      'status' => 'publish',
    ];

    $request = new WP_REST_Request(
      'PATCH',
      self::BRACKET_API_ENDPOINT . '/' . $bracket->id
    );
    $request->set_body_params($data);
    $request->set_param('item_id', $bracket->id);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $this->assertEquals(200, $response->get_status());
    $this->assertEquals('publish', $response->get_data()->status);
  }

  public function test_user_without_permission_cannot_publish_bracket() {
    $user = self::factory()->user->create_and_get();
    $user->remove_cap('wpbb_share_bracket');
    wp_set_current_user($user->ID);

    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'private',
      'author' => $user->ID,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $data = [
      'status' => 'publish',
    ];

    $request = new WP_REST_Request(
      'PATCH',
      self::BRACKET_API_ENDPOINT . '/' . $bracket->id
    );
    $request->set_body_params($data);
    $request->set_param('item_id', $bracket->id);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $this->assertEquals(200, $response->get_status());
    $this->assertEquals('private', $response->get_data()->status);
  }

  public function test_user_with_permission_can_update_results() {
    $user = self::factory()->user->create_and_get();
    $user->add_cap('wpbb_share_bracket');
    wp_set_current_user($user->ID);

    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'publish',
      'author' => $user->ID,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $data = [
      'results' => [
        [
          'round_index' => 0,
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
    $request->set_param('item_id', $bracket->id);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $this->assertEquals(200, $response->get_status());
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $response->get_data()->results[0]->winning_team_id
    );
  }

  public function test_user_without_permission_cannot_update_results() {
    $user = self::factory()->user->create_and_get();
    $user->remove_cap('wpbb_share_bracket');
    wp_set_current_user($user->ID);

    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'publish',
      'author' => $user->ID,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $data = [
      'results' => [
        [
          'round_index' => 0,
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
    $request->set_param('item_id', $bracket->id);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $this->assertEquals(200, $response->get_status());
    $updated = $this->bracket_repo->get($bracket->id);
    $this->assertEquals(0, count($updated->results));
  }

  public function test_update_some_results_sets_status_to_score() {
    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'publish',
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);

    $data = [
      'results' => [
        [
          'round_index' => 0,
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
    $request->set_param('item_id', $bracket->id);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $this->assertEquals(200, $response->get_status());
    $updated = $this->bracket_repo->get($bracket->id);
    $this->assertEquals('score', $updated->status);
  }

  public function test_update_all_results_sets_status_to_complete() {
    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'publish',
      'num_teams' => 4,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);

    $data = [
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
    $request->set_param('item_id', $bracket->id);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $this->assertEquals(200, $response->get_status());
    $updated = $this->bracket_repo->get($bracket->id);
    $this->assertEquals('complete', $updated->status);
  }

  public function test_update_all_partial_results_sets_status_to_complete() {
    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'publish',
      'num_teams' => 4,
    ]);

    $this->bracket_repo->update($bracket->id, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ],
      ],
    ]);

    $data = [
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
    $request->set_param('item_id', $bracket->id);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $this->assertEquals(200, $response->get_status());
    $updated = $this->bracket_repo->get($bracket->id);
    $this->assertEquals('complete', $updated->status);

    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $updated->results[0]->winning_team_id
    );
    $this->assertEquals(
      $bracket->matches[1]->team2->id,
      $updated->results[1]->winning_team_id
    );
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $updated->results[2]->winning_team_id
    );

    $this->assertEquals(3, count($updated->results));
  }

  public function test_anonymous_bracket_sets_cookies() {
    $utils_mock = $this->createMock(Utils::class);

    $utils_mock
      ->expects($this->exactly(2))
      ->method('set_cookie')
      ->withConsecutive(
        [$this->equalTo('wpbb_anonymous_bracket_id'), $this->isType('int')],
        [$this->equalTo('wpbb_anonymous_bracket_key'), $this->isType('string')]
      );

    $bracket_api = new BracketApi([
      'utils' => $utils_mock,
    ]);

    // set current user to anonymous user
    wp_set_current_user(0);

    $request = new WP_REST_Request('POST', '/wp/v2/brackets');
    $request->set_body_params([
      'title' => 'test bracket',
      'wildcard_placement' => 0,
      'num_teams' => 4,
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
    ]);

    $res = $bracket_api->create_item($request);
  }
}
