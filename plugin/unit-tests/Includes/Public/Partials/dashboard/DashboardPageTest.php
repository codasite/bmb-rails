<?php

use Spatie\Snapshots\MatchesSnapshots;
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\Dashboard\DashboardService;
use WStrategies\BMB\Public\Partials\dashboard\DashboardPage;
use WStrategies\BMB\Public\Partials\dashboard\ManageBracketsPage;
use WStrategies\BMB\Public\Partials\dashboard\PlayHistoryPage;

class DashboardPageTest extends TestCase {
  use MatchesSnapshots;

  public function test_render_dashboard() {
    WP_Mock::userFunction('get_permalink', [
      'return' => 'http://example.com',
    ]);
    WP_Mock::userFunction('get_page_by_path', [
      'return' => (object) ['ID' => 1],
    ]);
    WP_Mock::userFunction('get_query_var', [
      'return' => 'brackets',
    ]);
    WP_Mock::userFunction('absint', [
      'return' => 1,
    ]);
    WP_Mock::userFunction('comments_open', [
      'return' => true,
    ]);
    WP_Mock::userFunction('current_user_can', [
      'return' => true,
    ]);
    WP_Mock::userFunction('get_current_user_id', [
      'return' => 1,
    ]);
    $post_mock = $this->mockPost([
      'ID' => 1,
      'post_author' => 1,
      'post_status' => 'publish',
      'post_title' => 'Test Bracket',
      'post_name' => 'test-bracket',
      'post_type' => 'bracket',
    ]);
    WP_Mock::userFunction('get_post', [
      'return' => $post_mock,
    ]);
    $rendered = (new DashboardPage([
      'manage_brackets_page' => new ManageBracketsPage([
        'dashboard_service' => new class extends DashboardService {
          public function get_hosted_brackets(int $paged, string $status) {
            return [
              'brackets' => [new Bracket(['title' => 'Bracket 1'])],
              'max_num_pages' => 1,
            ];
          }
        },
      ]),
    ]))->render();
    $rendered = preg_replace(
      '/\?bracket=[a-zA-Z0-9_-]{8}/',
      '?bracket=test/',
      $rendered
    );
    $rendered = preg_replace(
      "/data-bracket-id='\d+'/",
      'data-bracket-id="1"',
      $rendered
    );
    $this->assertMatchesHtmlSnapshot($rendered);
  }

  public function test_render_dashboard_my_profile() {
    WP_Mock::userFunction('get_permalink', [
      'return' => 'http://example.com',
    ]);
    WP_Mock::userFunction('get_page_by_path', [
      'return' => (object) ['ID' => 1],
    ]);
    WP_Mock::userFunction('wp_get_current_user', [
      'return' => (object) ['ID' => 1],
    ]);
    $wp_query_mock = Mockery::mock('overload:WP_Query');
    $wp_query_mock
      ->shouldReceive('__construct')
      ->andSet('found_posts', 1)
      ->andSet('posts', []);
    $rendered = (new DashboardPage())->render('profile');
    $this->assertMatchesHtmlSnapshot($rendered);
  }

  public function test_render_play_history_page() {
    WP_Mock::userFunction('get_permalink', [
      'return' => 'http://example.com',
    ]);
    WP_Mock::userFunction('get_page_by_path', [
      'return' => (object) ['ID' => 1],
    ]);
    WP_Mock::userFunction('get_query_var', [
      'return' => '1',
    ]);
    WP_Mock::userFunction('absint', [
      'return' => 1,
    ]);
    WP_Mock::userFunction('get_current_user_id', [
      'return' => 1,
    ]);
    $wp_query_mock = Mockery::mock('overload:WP_Query');
    $wp_query_mock->shouldReceive('__construct')->andSet('max_num_pages', 1);
    $rendered = (new DashboardPage([
      'play_history_page' => new PlayHistoryPage([
        'play_repo' => new class extends PlayRepo {
          public function get_all(
            WP_Query|array $query = [],
            array $options = []
          ): array {
            return [
              new BracketPlay([
                'id' => 1,
                'bracket' => new Bracket([
                  'title' => 'Bracket 1',
                  'num_teams' => 64,
                ]),
                'accuracy_score' => 0.34,
                'busted_id' => 1,
                'printed' => true,
                'is_winner' => true,
                'is_tournament_entry' => true,
                'bmb_official' => true,
                'is_paid' => true,
              ]),
            ];
          }
        },
      ]),
    ]))->render('play-history');
    $this->assertMatchesHtmlSnapshot($rendered);
  }
}
