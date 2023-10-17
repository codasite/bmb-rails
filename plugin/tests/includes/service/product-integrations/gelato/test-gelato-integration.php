<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/gelato/class-wpbb-gelato-product-integration.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-product-integration-interface.php';

class GelatoIntgrationTest extends WPBB_UnitTestCase {
  public function test_generate_images() {
    $post = $this->factory()->post->create_and_get([
      'post_type' => 'bracket_play',
    ]);
    $bracket_mock = $this->createMock(Wpbb_PostBracketInterface::class);
    $bracket_mock->method('get_post_id')->willReturn($post->ID);
    $bracket_mock->method('get_title')->willReturn('Test Bracket');
    $bracket_mock->method('get_date')->willReturn('2020-01-01');
    $bracket_mock->method('get_num_teams')->willReturn(4);
    $bracket_mock->method('get_matches')->willReturn([
      new Wpbb_Match([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Wpbb_Team([
          'id' => 1,
          'name' => 'Team 1',
        ]),
        'team2' => new Wpbb_Team([
          'id' => 2,
          'name' => 'Team 2',
        ]),
      ]),
      new Wpbb_Match([
        'round_index' => 0,
        'match_index' => 1,
        'team1' => new Wpbb_Team([
          'id' => 3,
          'name' => 'Team 3',
        ]),
        'team2' => new Wpbb_Team([
          'id' => 4,
          'name' => 'Team 4',
        ]),
      ]),
    ]);
    $bracket_mock->method('get_picks')->willReturn([
      new Wpbb_MatchPick([
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => $bracket_mock->get_matches()[0]->team1->id,
      ]),
      new Wpbb_MatchPick([
        'round_index' => 0,
        'match_index' => 1,
        'winning_team_id' => $bracket_mock->get_matches()[1]->team2->id,
      ]),
      new Wpbb_MatchPick([
        'round_index' => 1,
        'match_index' => 0,
        'winning_team_id' => $bracket_mock->get_matches()[0]->team1->id,
      ]),
    ]);

    $request_factory = $this->createMock(
      Wpbb_BracketImageRequestFactory::class
    );
    $request_factory
      ->method('get_request_data')
      ->willReturn(['test' => 'test']);
    $client = $this->createMock(Wpbb_HttpClientInterface::class);
    $client->method('send_many')->willReturn([
      'light_top' => [
        'image_url' => 'https://test.com/light_top.png',
      ],
      'dark_top' => [
        'image_url' => 'https://test.com/dark_top.png',
      ],
    ]);

    $gelato = new Wpbb_GelatoProductIntegration([
      'request_factory' => $request_factory,
      'client' => $client,
    ]);

    $meta_key = $gelato->get_post_meta_key();

    $gelato->generate_images($bracket_mock);

    $meta = json_decode(get_post_meta($post->ID, $meta_key, true));

    $this->assertEquals(
      'https://test.com/light_top.png',
      $meta->light_top->image_url
    );
    $this->assertEquals(
      'https://test.com/dark_top.png',
      $meta->dark_top->image_url
    );
  }
}
