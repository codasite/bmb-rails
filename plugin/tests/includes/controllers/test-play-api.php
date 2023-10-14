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
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-product-integration-interface.php';

//namespace phpunit

class PlayAPITest extends WPBB_UnitTestCase {
  private $play_repo;

  public function set_up() {
    parent::set_up();

    $this->play_repo = new Wpbb_BracketPlayRepo();
  }

  public function test_create_play_for_tournament() {
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
    ]);

    $data = [
      'generate_images' => false,
      'tournament_id' => $tournament->id,
      'author' => 1,
      'picks' => [
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

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');

    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $response = rest_do_request($request);

    $this->assertEquals(201, $response->get_status());

    $new_play = $this->play_repo->get($response->get_data()->id);

    $this->assertEquals($tournament->id, $new_play->tournament_id);
    $this->assertEquals($data['author'], $new_play->author);

    $new_picks = $new_play->picks;

    $this->assertEquals(3, count($new_picks));
    $this->assertEquals(0, $new_picks[0]->round_index);
    $this->assertEquals(0, $new_picks[0]->match_index);
    $this->assertEquals(
      $template->matches[0]->team1->id,
      $new_picks[0]->winning_team_id
    );
    $this->assertEquals(0, $new_picks[1]->round_index);
    $this->assertEquals(1, $new_picks[1]->match_index);
    $this->assertEquals(
      $template->matches[1]->team2->id,
      $new_picks[1]->winning_team_id
    );
    $this->assertEquals(1, $new_picks[2]->round_index);
    $this->assertEquals(0, $new_picks[2]->match_index);
    $this->assertEquals(
      $template->matches[0]->team1->id,
      $new_picks[2]->winning_team_id
    );
  }

  public function test_create_play_for_template() {
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
      ],
    ]);

    $data = [
      'generate_images' => false,
      'template_id' => $template->id,
      'author' => 1,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $template->matches[0]->team1->id,
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

    $this->assertNull($new_play->tournament_id);
    $this->assertEquals($data['author'], $new_play->author);
  }

  public function test_create_play_generate_images() {
    $integration = $this->createMock(Wpbb_ProductIntegrationInterface::class);
    $integration->expects($this->once())->method('generate_images');

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
      ],
    ]);

    $data = [
      'template_id' => $template->id,
      'author' => 1,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $template->matches[0]->team1->id,
        ],
      ],
    ];

    $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
    $request->set_body_params($data);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $api = new Wpbb_BracketPlayAPI([
      'product_integration' => $integration,
    ]);

    $response = $api->create_item($request);

    $this->assertEquals(201, $response->get_status());
  }
}
