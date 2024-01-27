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

  public function set_up(): void {
    parent::set_up();

    $this->bracket_repo = new BracketRepo();
  }

  public function test_leadboard_shortcode() {
    $bracket = $this->create_bracket([
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

    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
      'author' => $user,
      'is_tournament_entry' => true,
    ]);
    $updated_bracket = $this->update_bracket($bracket, [
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

  public function test_render_leaderboard() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
      'title' => 'Test Bracket',
      'status' => 'score',
      'slug' => 'test-bracket',
    ]);
    $user = self::factory()->user->create_object([
      'user_login' => 'test_user2',
      'user_email' => 'test2',
      'user_pass' => 'test',
      'first_name' => 'Test',
      'last_name' => 'User',
    ]);
    $customer = new \WC_Customer($user);
    $customer->set_billing_state('CA');
    $customer->save();

    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user,
      'accuracy_score' => 1,
      'slug' => 'test-play-1',
      'is_tournament_entry' => true,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user,
      'accuracy_score' => 0.5,
      'slug' => 'test-play-2',
      'is_tournament_entry' => true,
    ]);

    $leaderboard_service = new BracketLeaderboardService($bracket->id);

    $leaderboard_page = new LeaderboardPage([
      'leaderboard_service' => $leaderboard_service,
    ]);

    $this->assertMatchesHtmlSnapshot($leaderboard_page->render());
  }
}
