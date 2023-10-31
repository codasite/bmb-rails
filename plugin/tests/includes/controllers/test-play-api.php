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
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-product-integration-interface.php';
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-utils.php';

class PlayAPITest extends WPBB_UnitTestCase {
  private $play_repo;

  public function set_up() {
    parent::set_up();

    $this->play_repo = new Wpbb_BracketPlayRepo();
  }

  public function test_create_play_for_bracket() {
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
      'generate_images' => false,
      'bracket_id' => $bracket->id,
      'author' => 1,
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

    $new_play = $this->play_repo->get($response->get_data()->id);

    $this->assertEquals($bracket->id, $new_play->bracket_id);
    $this->assertEquals($data['author'], $new_play->author);

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
      ],
    ]);

    $data = [
      'generate_images' => false,
      'bracket_id' => $bracket->id,
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
    $this->assertEquals(get_current_user_id(), $response->get_data()->author);

    $play = $this->play_repo->get($response->get_data()->id);
    $this->assertNotNull($play);
    $this->assertEquals(get_current_user_id(), $play->author);
  }

  public function test_create_play_generate_images() {
    $integration = $this->createMock(Wpbb_ProductIntegrationInterface::class);
    $integration->expects($this->once())->method('generate_images');

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
      ],
    ]);

    $data = [
      'bracket_id' => $bracket->id,
      'author' => 1,
      'generate_images' => true,
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

    $utils_mock = $this->createMock(Wpbb_Utils::class);
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

    $api = new Wpbb_BracketPlayAPI([
      'product_integration' => $integration,
      'utils' => $utils_mock,
    ]);

    $response = $api->create_item($request);

    $this->assertEquals(201, $response->get_status());
  }

  public function test_update_play_author() {
    $user1 = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $play = self::factory()->play->create_and_get([
      'status' => 'publish',
      'num_teams' => 4,
      'author' => $user1->ID,
    ]);

    $repo = new Wpbb_BracketPlayRepo();

    $repo->update($play->id, [
      'author' => $user2->ID,
    ]);

    $updated = self::factory()->play->get_object_by_id($play->id);

    $this->assertEquals($user2->ID, $updated->author);
  }
}
