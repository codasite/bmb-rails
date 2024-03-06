<?php
namespace WStrategies\BMB\tests\integration\Includes\domain;


use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\Team;

class BracketTest extends WPBB_UnitTestCase {
  public function test_get_post_type() {
    $this->assertEquals('bracket', Bracket::get_post_type());
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
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
      'results' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 1,
        ]),
      ],
    ];
    $bracket = new Bracket($args);
    $this->assertInstanceOf(Bracket::class, $bracket);
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
    $bracket = Bracket::from_array($args);
    $this->assertInstanceOf(Bracket::class, $bracket);
    $this->assertEquals(1, $bracket->id);
    $this->assertEquals('Test Template', $bracket->title);
    $this->assertEquals('publish', $bracket->status);
    $this->assertEquals(1, $bracket->author);
    $this->assertEquals(2, $bracket->num_teams);
    $this->assertEquals(0, $bracket->wildcard_placement);
    $this->assertEquals(1, count($bracket->matches));
    $this->assertEquals(1, count($bracket->results));
    $this->assertEquals(
      '2020-01-01 00:00:00',
      $bracket->results_first_updated_at->format('Y-m-d H:i:s')
    );
  }

  public function test_to_array() {
    $now = new DateTimeImmutable();
    $args = [
      'id' => 1,
      'title' => 'Test Template',
      'status' => 'publish',
      'author' => 1,
      'num_teams' => 2,
      'wildcard_placement' => 0,
      'results_first_updated_at' => $now,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
      'results' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 1,
        ]),
      ],
    ];
    $bracket = new Bracket($args);
    $array = $bracket->to_array();
    $this->assertEquals(1, $array['id']);
    $this->assertEquals('Test Template', $array['title']);
    $this->assertEquals('publish', $array['status']);
    $this->assertEquals(1, $array['author']);
    $this->assertEquals(2, $array['num_teams']);
    $this->assertEquals(0, $array['wildcard_placement']);
    $this->assertEquals(1, count($array['matches']));
    $this->assertEquals(1, count($array['results']));
    $this->assertEquals(
      $now->format('Y-m-d H:i:s'),
      $array['results_first_updated_at']
    );
  }

  public function test_published_bracket_open() {
    $bracket = new Bracket([
      'status' => 'publish',
    ]);

    $this->assertTrue($bracket->is_open());
  }
  public function test_scored_bracket_is_not_open() {
    $bracket = new Bracket([
      'status' => 'score',
    ]);

    $this->assertFalse($bracket->is_open());
  }
  public function test_private_bracket_is_not_open() {
    $bracket = new Bracket([
      'status' => 'private',
    ]);

    $this->assertFalse($bracket->is_open());
  }
  public function test_complete_bracket_is_not_open() {
    $bracket = new Bracket([
      'status' => 'complete',
    ]);

    $this->assertFalse($bracket->is_open());
  }
  public function test_published_bracket_is_printable() {
    $bracket = new Bracket([
      'status' => 'publish',
    ]);

    $this->assertTrue($bracket->is_printable());
  }
  public function test_scored_bracket_is_printable() {
    $bracket = new Bracket([
      'status' => 'score',
    ]);

    $this->assertTrue($bracket->is_printable());
  }
  public function test_private_bracket_is_printable() {
    $bracket = new Bracket([
      'status' => 'private',
    ]);

    $this->assertTrue($bracket->is_printable());
  }
  public function test_complete_bracket_is_printable() {
    $bracket = new Bracket([
      'status' => 'complete',
    ]);

    $this->assertTrue($bracket->is_printable());
  }
  public function test_upcoming_bracket_is_not_printable() {
    $bracket = new Bracket([
      'status' => 'upcoming',
    ]);

    $this->assertFalse($bracket->is_printable());
  }
}
