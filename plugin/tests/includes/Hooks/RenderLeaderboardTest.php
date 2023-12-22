<?php
namespace WStrategies\BMB\tests\includes\Hooks;

use Spatie\Snapshots\MatchesSnapshots;
use WPBB_UnitTestCase;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Service\ScoreService;

class RenderLeaderboardTest extends WPBB_UnitTestCase {
  use MatchesSnapshots;
  private $bracket_repo;

  public function set_up() {
    parent::set_up();

    $this->bracket_repo = new BracketRepo();
  }

  public function test_render_leaderboard() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 2,
      'matches' => [
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
      ],
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

    $play1 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
      'author' => $user,
    ]);
    $updated_bracket = self::factory()->bracket->update_object($bracket, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ]);
    $score_service = new ScoreService([
      'ignore_late_plays' => true,
    ]);

    $affected = $score_service->score_bracket_plays($updated_bracket);
    $this->assertEquals(1, $affected);
    $customer = new \WC_Customer($user);
    $customer->set_billing_state('CA');
    $customer->save();

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
