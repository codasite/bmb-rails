<?php
namespace WStrategies\BMB\tests\integration\Includes\service\ProductIntegrations\Gelato;


use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\PostBracketInterface;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\Http\BracketImageRequestFactory;
use WStrategies\BMB\Includes\Service\Http\HttpClientInterface;
use WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato\GelatoProductIntegration;

class GelatoIntegrationTest extends WPBB_UnitTestCase {
  public function test_generate_images() {
    $post = $this->create_post([
      'post_type' => 'bracket_play',
    ]);
    $bracket_mock = $this->createMock(PostBracketInterface::class);
    $bracket_mock->method('get_post_id')->willReturn($post->ID);
    $bracket_mock->method('get_title')->willReturn('Test Bracket');
    $bracket_mock->method('get_date')->willReturn('2020-01-01');
    $bracket_mock->method('get_num_teams')->willReturn(4);
    $bracket_mock->method('get_matches')->willReturn([
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team([
          'id' => 1,
          'name' => 'Team 1',
        ]),
        'team2' => new Team([
          'id' => 2,
          'name' => 'Team 2',
        ]),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 1,
        'team1' => new Team([
          'id' => 3,
          'name' => 'Team 3',
        ]),
        'team2' => new Team([
          'id' => 4,
          'name' => 'Team 4',
        ]),
      ]),
    ]);
    $bracket_mock->method('get_picks')->willReturn([
      new Pick([
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => $bracket_mock->get_matches()[0]->team1->id,
      ]),
      new Pick([
        'round_index' => 0,
        'match_index' => 1,
        'winning_team_id' => $bracket_mock->get_matches()[1]->team2->id,
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 0,
        'winning_team_id' => $bracket_mock->get_matches()[0]->team1->id,
      ]),
    ]);

    $request_factory = $this->createMock(BracketImageRequestFactory::class);
    $request_factory
      ->method('get_request_data')
      ->willReturn(['test' => 'test']);
    $client = $this->createMock(HttpClientInterface::class);
    $client->method('send_many')->willReturn([
      'top_light' => [
        'image_url' => 'https://test.com/top_light.png',
      ],
      'top_dark' => [
        'image_url' => 'https://test.com/top_dark.png',
      ],
    ]);

    $gelato = new GelatoProductIntegration([
      'request_factory' => $request_factory,
      'client' => $client,
    ]);

    $meta_key = $gelato->get_post_meta_key();

    $gelato->generate_images($bracket_mock);

    $meta = json_decode(get_post_meta($post->ID, $meta_key, true));

    $this->assertEquals(
      'https://test.com/top_light.png',
      $meta->top_light->image_url
    );
    $this->assertEquals(
      'https://test.com/top_dark.png',
      $meta->top_dark->image_url
    );
  }

  public function test_generate_images_empty_response_throws() {
    $bracket_mock = $this->createMock(PostBracketInterface::class);
    $request_factory = $this->createMock(BracketImageRequestFactory::class);
    $request_factory
      ->method('get_request_data')
      ->willReturn(['test' => 'test']);
    $client = $this->createMock(HttpClientInterface::class);
    $client->method('send_many')->willReturn([]);

    $gelato = new GelatoProductIntegration([
      'request_factory' => $request_factory,
      'client' => $client,
    ]);

    $this->expectException(\Exception::class);
    $gelato->generate_images($bracket_mock);
  }

  public function test_get_overlay_map() {
    $post = $this->create_post([
      'post_type' => 'bracket_play',
    ]);
    $bracket_mock = $this->createMock(PostBracketInterface::class);
    $bracket_mock->method('get_post_id')->willReturn($post->ID);

    $integration = new GelatoProductIntegration();

    $meta_key = $integration->get_post_meta_key();

    $image_urls = [
      'top_light' => [
        'image_url' => 'https://test.com/top_light.png',
      ],
      'top_dark' => [
        'image_url' => 'https://test.com/top_dark.png',
      ],
      'center_light' => [
        'image_url' => 'https://test.com/center_light.png',
      ],
      'center_dark' => [
        'image_url' => 'https://test.com/center_dark.png',
      ],
    ];

    update_post_meta($post->ID, $meta_key, json_encode($image_urls));

    $top_overlay = [
      'light' => 'https://test.com/top_light.png',
      'dark' => 'https://test.com/top_dark.png',
    ];

    $center_overlay = [
      'light' => 'https://test.com/center_light.png',
      'dark' => 'https://test.com/center_dark.png',
    ];

    $this->assertEquals(
      $top_overlay,
      $integration->get_overlay_map($bracket_mock, 'top')
    );
    $this->assertEquals(
      $center_overlay,
      $integration->get_overlay_map($bracket_mock, 'center')
    );
  }

  public function test_get_bracket_config() {
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);

    $play_repo_mock = $this->createMock(PlayRepo::class);
    $play_repo_mock->method('get')->willReturn($play);

    $integration = new GelatoProductIntegration([
      'play_repo' => $play_repo_mock,
    ]);

    $meta_key = $integration->get_post_meta_key();

    $image_urls = [
      'top_light' => [
        'image_url' => 'https://test.com/top_light.png',
      ],
    ];

    update_post_meta($play->id, $meta_key, json_encode($image_urls));

    $config = $integration->get_bracket_config('light', 'top');
    $this->assertEquals($config->play_id, $play->id);
    $this->assertEquals($config->bracket_id, $bracket->id);
    $this->assertEquals($config->theme_mode, 'light');
    $this->assertEquals($config->bracket_placement, 'top');
    $this->assertEquals($config->img_url, 'https://test.com/top_light.png');
  }

  public function test_has_all_configs_true() {
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);

    $play_repo_mock = $this->createMock(PlayRepo::class);
    $play_repo_mock->method('get')->willReturn($play);

    $integration = new GelatoProductIntegration([
      'play_repo' => $play_repo_mock,
    ]);

    $meta_key = $integration->get_post_meta_key();

    $image_urls = [
      'top_light' => [
        'image_url' => 'https://test.com/top_light.png',
      ],
      'top_dark' => [
        'image_url' => 'https://test.com/top_dark.png',
      ],
      'center_light' => [
        'image_url' => 'https://test.com/center_light.png',
      ],
      'center_dark' => [
        'image_url' => 'https://test.com/center_dark.png',
      ],
    ];

    update_post_meta($play->id, $meta_key, json_encode($image_urls));

    $this->assertTrue($integration->has_all_configs($play));
  }

  public function test_has_all_configs_false() {
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);

    $play_repo_mock = $this->createMock(PlayRepo::class);
    $play_repo_mock->method('get')->willReturn($play);

    $integration = new GelatoProductIntegration([
      'play_repo' => $play_repo_mock,
    ]);

    $meta_key = $integration->get_post_meta_key();

    $image_urls = [
      'top_light' => [
        'image_url' => 'https://test.com/top_light.png',
      ],
      'top_dark' => [
        'image_url' => 'https://test.com/top_dark.png',
      ],
      'center_light' => [
        'image_url' => 'https://test.com/center_light.png',
      ],
    ];

    update_post_meta($play->id, $meta_key, json_encode($image_urls));

    $this->assertFalse($integration->has_all_configs($play));
  }
}
