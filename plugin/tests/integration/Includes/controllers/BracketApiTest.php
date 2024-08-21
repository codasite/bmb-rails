<?php
namespace WStrategies\BMB\tests\integration\Includes\controllers;

use Spatie\Snapshots\MatchesSnapshots;
use WP_REST_Request;
use WStrategies\BMB\Includes\Controllers\BracketApi;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketResultsRepo;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationService;
use WStrategies\BMB\Includes\Service\ScoreServiceInterface;
use WStrategies\BMB\Includes\Utils;
use WStrategies\BMB\tests\integration\Traits\SetupAdminUser;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

//namespace phpunit

class BracketApiTest extends WPBB_UnitTestCase {
  use SetupAdminUser;
  use MatchesSnapshots;
  const BRACKET_API_ENDPOINT = '/wp-bracket-builder/v1/brackets';
  private $bracket_repo;

  /**
   * @before
   */
  public function set_up(): void {
    parent::set_up();

    $this->bracket_repo = new BracketRepo();
  }

  public function test_create_bracket() {
    $data = [
      'title' => 'Test Bracket',
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
    $data = (object) $response->get_data();
    $this->assertEquals('Test Bracket', $data->title);
    $this->assertEquals('private', $data->status);
    $this->assertEquals('test month', $data->month);
    $this->assertEquals('test year', $data->year);
    $this->assertEquals(8, $data->num_teams);
    $this->assertEquals(0, $data->wildcard_placement);
    $this->assertFalse($data->is_voting);
    $this->assertEquals(2, count($data->matches));
    $this->assertEquals(0, $data->matches[0]['round_index']);
    $this->assertEquals(0, $data->matches[0]['match_index']);
    $this->assertEquals('Team 1', $data->matches[0]['team1']['name']);
    $this->assertEquals('Team 2', $data->matches[0]['team2']['name']);
    $this->assertEquals(0, $data->matches[1]['round_index']);
    $this->assertEquals(1, $data->matches[1]['match_index']);
    $this->assertEquals('Team 3', $data->matches[1]['team1']['name']);
    $this->assertEquals('Team 4', $data->matches[1]['team2']['name']);
    $bracket = $this->bracket_repo->get($data->id);
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
    $data = (object) $response->get_data();
    $this->assertEquals(get_current_user_id(), $data->author);

    $bracket = $this->bracket_repo->get($data->id);
    $this->assertNotNull($bracket);
    $this->assertEquals(get_current_user_id(), $bracket->author);
  }

  public function test_create_bracket_validation_exception() {
    $request = new WP_REST_Request('POST', self::BRACKET_API_ENDPOINT);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);
    $this->assertEquals(400, $response->get_status());
    $data = $response->get_data();
    $this->assertEquals(
      'Missing required fields: title, num_teams, wildcard_placement, matches',
      $data['message']
    );
  }

  public function test_update_bracket() {
    $bracket = $this->create_bracket([
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
      'is_voting' => true,
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
    $data = (object) $response->get_data();
    $this->assertEquals('Test Bracket', $data->title);

    $bracket = $this->bracket_repo->get($data->id);
    $this->assertNotNull($bracket);
    $this->assertEquals('Test Bracket', $bracket->title);
    $this->assertTrue($bracket->is_voting);
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
    $bracket = $this->create_bracket([
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
    $data = (object) $response->get_data();
    $this->assertEquals('Test Bracket', $data->title);
    $this->assertEquals('Test Month', $data->month);
    $this->assertEquals('Test Year', $data->year);

    $bracket = $this->bracket_repo->get($data->id);
    $this->assertNotNull($bracket);
    $this->assertEquals('Test Bracket', $bracket->title);
    $this->assertEquals('Test Month', $bracket->month);
    $this->assertEquals('Test Year', $bracket->year);
  }

  public function test_non_author_cannot_edit_bracket() {
    $user = self::factory()->user->create_and_get();
    wp_set_current_user($user->ID);
    $bracket = $this->create_bracket([
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
    $bracket = $this->create_bracket([
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
    $bracket = $this->create_bracket([
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
    $bracket = $this->create_bracket([
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

  public function test_should_update_should_notify_results_updated_when_value_is_true() {
    $notification_service = $this->getMockBuilder(
      BracketResultsNotificationService::class
    )
      ->disableOriginalConstructor()
      ->getMock();

    $api = new BracketApi([
      'notification_service' => $notification_service,
    ]);

    $bracket = $this->create_bracket([
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
      'should_notify_results_updated' => true,
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

    $res = $api->update_item($request);
    $bracket = $this->bracket_repo->get($bracket->id);
    $this->assertTrue($bracket->should_notify_results_updated);
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
    $data = (object) $response->get_data();
    $this->assertEquals('private', $data->status);
  }

  public function test_user_with_permission_can_publish_bracket() {
    $user = self::factory()->user->create_and_get();
    $user->add_cap('wpbb_share_bracket');
    wp_set_current_user($user->ID);

    $bracket = $this->create_bracket([
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
    $data = (object) $response->get_data();
    $this->assertEquals('publish', $data->status);
  }

  public function test_user_without_permission_cannot_publish_bracket() {
    $user = self::factory()->user->create_and_get();
    $user->remove_cap('wpbb_share_bracket');
    wp_set_current_user($user->ID);

    $bracket = $this->create_bracket([
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
    $data = (object) $response->get_data();
    $this->assertEquals('private', $data->status);
  }

  public function test_user_with_permission_can_update_results() {
    $user = self::factory()->user->create_and_get();
    $user->add_cap('wpbb_share_bracket');
    wp_set_current_user($user->ID);

    $bracket = $this->create_bracket([
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
    $data = (object) $response->get_data();
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $data->results[0]['winning_team_id']
    );
  }

  public function test_user_without_permission_cannot_update_results() {
    $user = self::factory()->user->create_and_get();
    $user->remove_cap('wpbb_share_bracket');
    wp_set_current_user($user->ID);

    $bracket = $this->create_bracket([
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
    $bracket = $this->create_bracket([
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

  public function test_update_all_partial_results_sets_status_to_complete() {
    $bracket = $this->create_bracket([
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

  public function test_update_all_results_sets_status_to_complete() {
    $bracket = $this->create_bracket([
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

  public function test_bracket_is_scored_on_update_results() {
    $score_service = $this->getMockBuilder(ScoreServiceInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $api = new BracketApi(['score_service' => $score_service]);

    $bracket = $this->create_bracket([
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

  public function test_winners_are_set_when_all_results_updated() {
    $bracket = $this->create_bracket([
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

    $score_service_mock = $this->createMock(ScoreServiceInterface::class);
    $score_service_mock
      ->expects($this->once())
      ->method('score_bracket_plays')
      ->with($this->isInstanceOf(Bracket::class), true);

    $api = new BracketApi([
      'score_service' => $score_service_mock,
    ]);

    $request = new WP_REST_Request(
      'PATCH',
      self::BRACKET_API_ENDPOINT . '/' . $bracket->id
    );
    $request->set_body_params($data);
    $request->set_param('item_id', $bracket->id);

    $response = $api->update_item($request);
    $this->assertEquals(200, $response->get_status());
    $updated = $this->bracket_repo->get($bracket->id);
    $this->assertEquals('complete', $updated->status);
  }

  public function test_winners_not_set_when_not_all_results_updated() {
    $bracket = $this->create_bracket([
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
      ],
    ];

    $score_service_mock = $this->createMock(ScoreServiceInterface::class);
    $score_service_mock
      ->expects($this->once())
      ->method('score_bracket_plays')
      ->with($this->isInstanceOf(Bracket::class), false);

    $api = new BracketApi([
      'score_service' => $score_service_mock,
    ]);

    $request = new WP_REST_Request(
      'PATCH',
      self::BRACKET_API_ENDPOINT . '/' . $bracket->id
    );
    $request->set_body_params($data);
    $request->set_param('item_id', $bracket->id);

    $response = $api->update_item($request);
    $this->assertEquals(200, $response->get_status());
    $updated = $this->bracket_repo->get($bracket->id);
    $this->assertEquals('score', $updated->status);
  }

  public function test_update_bracket_fee() {
    $bracket = $this->create_bracket();

    $data = [
      'fee' => 100,
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

    $this->assertEquals(100, $updated->fee);
  }
}
