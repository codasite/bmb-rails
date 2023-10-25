<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket.php';

class BracketTest extends WPBB_UnitTestCase {
  public function test_get_post_type() {
    $this->assertEquals('bracket', Wpbb_Bracket::get_post_type());
  }

  public function test_constructor() {
    $now = new DateTimeImmutable();
    $args = [
      'title' => 'Test Template',
      'status' => 'publish',
      'author' => 1,
      'num_teams' => 2,
      'wildcard_placement' => 0,
      'results_first_updated_at' => $now,
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
      ],
      'results' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 1,
        ]),
      ],
    ];
    $bracket = new Wpbb_Bracket($args);
    $this->assertInstanceOf(Wpbb_Bracket::class, $bracket);
    $this->assertEquals(2, $bracket->num_teams);
    $this->assertEquals(0, $bracket->wildcard_placement);
    $this->assertEquals(1, count($bracket->matches));
    $this->assertEquals(1, count($bracket->results));
    $this->assertEquals($now, $bracket->results_first_updated_at);
  }

  public function test_from_array() {
    $args = [
      'id' => 1,
      'title' => 'Test Template',
      'status' => 'publish',
      'author' => 1,
      'num_teams' => 2,
      'wildcard_placement' => 0,
      'results_first_updated_at' => '2020-01-01 00:00:00',
      'matches' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'team1' => [
            'name' => 'Team 1',
          ],
          'team2' => [
            'name' => 'Team 2',
          ],
        ],
      ],
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 1,
        ],
      ],
    ];
    $bracket = Wpbb_Bracket::from_array($args);
    $this->assertInstanceOf(Wpbb_Bracket::class, $bracket);
    $this->assertEquals(2, $bracket->num_teams);
    $this->assertEquals(0, $bracket->wildcard_placement);
    $this->assertEquals(1, count($bracket->matches));
    $this->assertEquals(1, count($bracket->results));
    $this->assertEquals(
      '2020-01-01 00:00:00',
      $bracket->results_first_updated_at->format('Y-m-d H:i:s')
    );
  }
}
