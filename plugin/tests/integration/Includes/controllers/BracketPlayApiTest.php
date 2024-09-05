<?php
namespace WStrategies\BMB\tests\integration\Includes\controllers;

use WP_REST_Request;
use WStrategies\BMB\Includes\Controllers\BracketPlayApi;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\ProductIntegrations\ProductIntegrationInterface;
use WStrategies\BMB\Includes\Utils;
use WStrategies\BMB\tests\integration\Traits\SetupAdminUser;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class BracketPlayApiTest extends WPBB_UnitTestCase {
  use SetupAdminUser;
  private $play_repo;

  /**
   * @before
   */
  public function set_up(): void {
    parent::set_up();

    $this->play_repo = new PlayRepo();
  }

  public function test_create_play_for_bracket() {
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
      'generate_images' => false,
      'bracket_id' => $bracket->id,
      'set_cookie' => false,
      'picks' => [
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

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');

    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(201, $response->get_status());

    $new_play = $this->play_repo->get($response->get_data()['id']);

    $this->assertEquals($bracket->id, $new_play->bracket_id);

    $new_picks = $new_play->picks;

    $this->assertEquals(3, count($new_picks));
    $this->assertEquals(0, $new_picks[0]->round_index);
    $this->assertEquals(0, $new_picks[0]->match_index);
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $new_picks[0]->winning_team_id
    );
    $this->assertEquals(0, $new_picks[1]->round_index);
    $this->assertEquals(1, $new_picks[1]->match_index);
    $this->assertEquals(
      $bracket->matches[1]->team2->id,
      $new_picks[1]->winning_team_id
    );
    $this->assertEquals(1, $new_picks[2]->round_index);
    $this->assertEquals(0, $new_picks[2]->match_index);
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $new_picks[2]->winning_team_id
    );
  }

  public function test_create_play_current_user_is_author() {
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
      'generate_images' => false,
      'bracket_id' => $bracket->id,
      'set_cookie' => false,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);
    $this->assertEquals(201, $response->get_status());
    $this->assertEquals(get_current_user_id(), $response->get_data()['author']);

    $play = $this->play_repo->get($response->get_data()['id']);
    $this->assertNotNull($play);
    $this->assertEquals(get_current_user_id(), $play->author);
  }

  public function test_should_update_picks() {
    $bracket = $this->create_bracket([
      'is_voting' => true,
      'live_round_index' => 1,
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
    $team1_id = $bracket->matches[0]->team1->id;
    $team2_id = $bracket->matches[0]->team2->id;
    $team3_id = $bracket->matches[1]->team1->id;
    $team4_id = $bracket->matches[1]->team2->id;
    $play = new Play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team2_id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team3_id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team3_id,
        ]),
      ],
    ]);
    $play = $this->play_repo->add($play);
    $data = [
      'picks' => [
        [
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team2_id,
        ],
      ],
    ];

    $request = new WP_REST_Request(
      'PATCH',
      '/wp-bracket-builder/v1/plays/' . $play->id
    );

    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(
      200,
      $response->get_status(),
      print_r($response->data, true)
    );
    $play = $this->play_repo->get($play);
    $this->assertEquals(3, count($play->picks));
    $this->assertEquals(
      $team2_id,
      $play->picks[0]->winning_team_id,
      'Team 2 should be the winner'
    );
  }

  public function test_update_play_author() {
    $user1 = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
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
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $repo = new PlayRepo();

    $repo->update($play->id, [
      'author' => $user2->ID,
    ]);

    $updated = $this->get_play($play->id);

    $this->assertEquals($user2->ID, $updated->author);
  }

  public function test_author_can_play_private_bracket() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'private',
      'num_teams' => 2,
    ]);

    $data = [
      'bracket_id' => $bracket->id,
      'generate_images' => false,
      'set_cookie' => false,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    wp_set_current_user($user->ID);
    $response = rest_do_request($request);

    $this->assertEquals(201, $response->get_status());
  }

  public function test_non_author_cannot_play_private_bracket() {
    $user1 = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket([
      'author' => $user1->ID,
      'status' => 'private',
      'num_teams' => 2,
    ]);

    $data = [
      'bracket_id' => $bracket->id,
      'generate_images' => false,
      'set_cookie' => false,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    wp_set_current_user($user2->ID);

    $response = rest_do_request($request);

    $this->assertEquals(403, $response->get_status());
  }

  public function test_upcoming_bracket_cannot_be_played() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket([
      'status' => 'upcoming',
      'num_teams' => 2,
    ]);

    $data = [
      'bracket_id' => $bracket->id,
      'generate_images' => false,
      'set_cookie' => false,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    wp_set_current_user($user->ID);
    $response = rest_do_request($request);

    $this->assertEquals(403, $response->get_status());
  }

  public function test_public_play_can_be_busted() {
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    // set post tag for play
    wp_add_post_tags($play->id, 'bmb_vip_featured');

    $data = [
      'busted_id' => $play->id,
      'bracket_id' => $bracket->id,
      'generate_images' => false,
      'set_cookie' => false,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(201, $response->get_status());
  }

  public function test_non_public_play_cannot_be_busted() {
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);

    $data = [
      'busted_id' => $play->id,
      'bracket_id' => $bracket->id,
      'generate_images' => false,
      'set_cookie' => false,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(403, $response->get_status());
  }

  public function test_play_for_bmb_official_bracket_is_bmb_official() {
    $bracket = $this->create_bracket();
    // add tag bmb_official
    wp_add_post_tags($bracket->id, 'bmb_official');

    $data = [
      'bracket_id' => $bracket->id,
      'generate_images' => false,
      'set_cookie' => false,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(201, $response->get_status());

    $play = $this->play_repo->get($response->get_data()['id']);

    $this->assertTrue($play->bmb_official);
  }

  public function test_create_play_generate_images_if_no_configs() {
    $integration = $this->createMock(ProductIntegrationInterface::class);
    $integration->method('has_all_configs')->willReturn(false);
    $integration->expects($this->once())->method('generate_images');

    $bracket = $this->create_bracket();

    $data = [
      'bracket_id' => $bracket->id,
      'author' => 1,
      'generate_images' => true,
      'set_cookie' => false,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $api = new BracketPlayAPI([
      'product_integration' => $integration,
    ]);

    $response = $api->create_item($request);

    $this->assertEquals(201, $response->get_status());
  }

  public function test_generate_images_endpoint_generates_images() {
    $integration = $this->createMock(ProductIntegrationInterface::class);
    $integration->expects($this->once())->method('generate_images');

    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);

    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/plays/' . $play->id . '/generate-images'
    );

    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_param('item_id', $play->id);

    $utils_mock = $this->createMock(Utils::class);

    $api = new BracketPlayAPI([
      'product_integration' => $integration,
      'utils' => $utils_mock,
    ]);

    $response = $api->generate_images($request);

    $this->assertEquals(201, $response->get_status());
  }

  public function test_generate_images_endpoint_sets_cookie() {
    $integration = $this->createMock(ProductIntegrationInterface::class);
    $integration->expects($this->once())->method('generate_images');

    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);

    $utils_mock = $this->createMock(Utils::class);
    $utils_mock
      ->expects($this->once())
      ->method('set_cookie')
      ->with(
        $this->equalTo('play_id'),
        $this->equalTo($play->id),
        $this->equalTo([
          'days' => 30,
        ])
      );

    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/plays/' . $play->id . '/generate-images'
    );

    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_param('item_id', $play->id);

    $api = new BracketPlayAPI([
      'product_integration' => $integration,
      'utils' => $utils_mock,
    ]);

    $response = $api->generate_images($request);

    $this->assertEquals(201, $response->get_status());
  }

  public function test_play_is_marked_as_tournament_entry() {
    $bracket = $this->create_bracket();

    $data = [
      'bracket_id' => $bracket->id,
      'author' => 1,
      'generate_images' => false,
      'set_cookie' => false,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $res = rest_do_request($request);

    $this->assertEquals(201, $res->get_status());

    $play = $this->play_repo->get($res->get_data()['id']);

    $this->assertTrue($play->is_tournament_entry);
  }

  public function test_create_play_sets_cookie() {
    $bracket = $this->create_bracket();
    $utils_mock = $this->createMock(Utils::class);
    $utils_mock
      ->expects($this->once())
      ->method('set_cookie')
      ->with(
        $this->equalTo('play_id'),
        $this->equalTo($bracket->id + 1),
        $this->equalTo([
          'days' => 30,
        ])
      );

    $data = [
      'bracket_id' => $bracket->id,
      'author' => 1,
      'generate_images' => false,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');

    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_param('item_id', $bracket->id);

    $api = new BracketPlayAPI([
      'utils' => $utils_mock,
    ]);

    $response = $api->create_item($request);

    $this->assertEquals(201, $response->get_status());
  }
}
