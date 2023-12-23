<?php
namespace WStrategies\BMB\tests\includes\Hooks;

use Spatie\Snapshots\MatchesSnapshots;
use WPBB_UnitTestCase;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Service\BracketLeaderboardService;
use WStrategies\BMB\Includes\Service\ScoreService;
use WStrategies\BMB\Public\Partials\LeaderboardPage;

class LeaderboardPageTest extends WPBB_UnitTestCase {
  use MatchesSnapshots;
  private $bracket_repo;

  public function set_up() {
    parent::set_up();

    $this->bracket_repo = new BracketRepo();
  }

  public function test_render_leaderboard() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
      'title' => 'Test Bracket',
      'status' => 'score',
    ]);
    $user = self::factory()->user->create_object([
      'user_login' => 'test_user',
      'user_email' => 'test',
      'user_pass' => 'test',
      'first_name' => 'Test',
      'last_name' => 'User',
    ]);
    $customer = new \WC_Customer($user);
    $customer->set_billing_state('CA');
    $customer->save();

    $play1 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'author' => $user,
      'accuracy_score' => 1,
      'slug' => 'test-play-1',
    ]);
    $play2 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'author' => $user,
      'accuracy_score' => 0.5,
      'slug' => 'test-play-2',
    ]);

    $leaderboard_service = new BracketLeaderboardService($bracket->id);

    $leaderboard_page = new LeaderboardPage([
      'leaderboard_service' => $leaderboard_service,
    ]);

    $this->assertMatchesHtmlSnapshot($leaderboard_page->render());
  }
  /**
   * TODO: To add more tests to this file you need to move leaderboard.php to a class so you don't get function already defined errors
   */
}
