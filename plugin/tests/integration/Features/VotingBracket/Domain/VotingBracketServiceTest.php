<?php
namespace WStrategies\BMB\tests\integration\Features\VotingBracket\Domain;

use WStrategies\BMB\Features\VotingBracket\Domain\VotingBracketService;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\Team;

class VotingBracketServiceTest extends WPBB_UnitTestCase {
  public function test_should_increment_live_round_index() {
    $bracket = $this->create_bracket();
    $voting_service = new VotingBracketService();
    $updated = $voting_service->complete_bracket_round($bracket->id);
    $this->assertEquals($updated->live_round_index, 1);
  }

  public function test_should_set_status_to_complete_when_last_round_is_completed() {
    $bracket = $this->create_bracket([
      'live_round_index' => 1,
      'is_voting' => true,
      'num_teams' => 4,
    ]);
    $voting_service = new VotingBracketService();
    $updated = $voting_service->complete_bracket_round($bracket->id);
    $this->assertEquals($updated->status, 'complete');
  }

  public function test_should_not_change_bracket_status_if_not_last_round() {
    $bracket = $this->create_bracket([
      'live_round_index' => 0,
      'is_voting' => true,
      'num_teams' => 4,
    ]);
    $voting_service = new VotingBracketService();
    $updated = $voting_service->complete_bracket_round($bracket->id);
    $this->assertEquals($updated->status, 'publish');
  }

  public function test_should_update_results_with_next_round_of_most_popular_picks() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
      'is_voting' => true,
      'live_round_index' => 0,
    ]);
    $team1_id = $bracket->matches[0]->team1->id;
    $team2_id = $bracket->matches[0]->team2->id;
    $team3_id = $bracket->matches[1]->team1->id;

    $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team2_id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team3_id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team3_id,
        ]),
      ],
    ]);
    $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team1_id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team3_id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team1_id,
        ]),
      ],
    ]);
    $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team1_id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team3_id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team1_id,
        ]),
      ],
    ]);
    $voting_service = new VotingBracketService();
    $updated = $voting_service->complete_bracket_round($bracket->id);
    $this->assertCount(2, $updated->results);
    $this->assertEquals($team1_id, $updated->results[0]->winning_team_id);
    $this->assertEquals(0.6667, $updated->results[0]->popularity);
    $this->assertEquals($team3_id, $updated->results[1]->winning_team_id);
    $this->assertEquals(1, $updated->results[1]->popularity);
  }
}
