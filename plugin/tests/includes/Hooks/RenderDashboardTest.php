<?php
namespace WStrategies\BMB\tests\includes\Hooks;

use Spatie\Snapshots\MatchesSnapshots;
use WPBB_UnitTestCase;
use WStrategies\BMB\Public\Partials\dashboard\DashboardPage;

class RenderDashboardTest extends WPBB_UnitTestCase {
  use MatchesSnapshots;

  public function test_render_dashboard() {
    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'publish',
      'num_teams' => 4,
      'author' => get_current_user_id(),
      'title' => 'Test Bracket',
    ]);
    $rendered = do_shortcode('[wpbb-dashboard]');
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
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
    ]);
    $play = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'author' => get_current_user_id(),
      'is_winner' => true,
    ]);
    $rendered = DashboardPage::render('profile');
    $this->assertMatchesHtmlSnapshot($rendered);
  }
}
