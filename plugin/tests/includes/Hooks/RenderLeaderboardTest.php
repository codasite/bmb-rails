<?php
namespace WStrategies\BMB\tests\includes\Hooks;

use Spatie\Snapshots\MatchesSnapshots;
use WPBB_UnitTestCase;

class RenderLeaderboardTest extends WPBB_UnitTestCase {
  use MatchesSnapshots;

  public function test_render_leaderboard() {
    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'publish',
      'num_teams' => 4,
      'author' => get_current_user_id(),
      'title' => 'Test Bracket',
    ]);
    self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'author' => get_current_user_id(),
    ]);
    global $post;
    $post = get_post($bracket->id);
    set_query_var('view', 'leaderboard');
    $rendered = do_shortcode('[wpbb-bracket-page]');
    $rendered = preg_replace(
      '/\?bracket=[a-zA-Z0-9_-]{8}/',
      '?bracket=test/',
      $rendered
    );
    $rendered = preg_replace(
      '/\?bracket_play=[a-zA-Z0-9_-]{8}/',
      '?bracket_play=test/',
      $rendered
    );
    $this->assertMatchesHtmlSnapshot($rendered);
  }
  /**
   * TODO: To add more tests to this file you need to move leaderboard.php to a class so you don't get function already defined errors
   */
}
