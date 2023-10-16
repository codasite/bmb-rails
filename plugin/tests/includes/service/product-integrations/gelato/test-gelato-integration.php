<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/gelato/class-wpbb-gelato-product-integration.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-product-integration-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/object-storage/class-wpbb-object-storage-interface.php';

class GelatoIntgrationTest extends WPBB_UnitTestCase {
  public function test_get_request_data() {
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

    $play = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team2->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    // $object_storage = $this->createMock(Wpbb_ObjectStorageInterface::class)
    //   ->method('get_upload_options')
    //   ->willReturn('testOptions');
    // $integration = new Wpbb_GelatoProductIntegration([
    //   'object_storage' => $object_storage,
    // ]);
  }
  public function test_get_body() {
    $object_storage = $this->createMock(Wpbb_ObjectStorageInterface::class);
    $object_storage->method('get_upload_options')->willReturn([
      'test_key' => 'test_value',
    ]);
    $integration = new Wpbb_GelatoProductIntegration([
      'object_storage' => $object_storage,
    ]);
    $bracket_id = 1;
    $base_data = [
      'test_key' => 'test_value',
    ];
    $base_query = [
      'test_key' => 'test_value',
    ];
    $theme = 'light';
    $position = 'top';

    $body = $integration->get_body(
      $bracket_id,
      $base_data,
      $base_query,
      $theme,
      $position
    );

    $this->assertEquals(
      json_encode([
        'test_key' => 'test_value',
        'storageOptions' => [
          'test_key' => 'test_value',
        ],
        'queryParams' => [
          'test_key' => 'test_value',
          'theme' => 'light',
          'position' => 'top',
        ],
      ]),
      $body
    );
  }
}
