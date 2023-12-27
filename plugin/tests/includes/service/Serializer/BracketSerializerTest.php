<?php

use Spatie\Snapshots\MatchesSnapshots;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Service\Serializer\BracketSerializer;
use WStrategies\BMB\Includes\Service\Serializer\PostBaseSerializer;

class BracketSerializerTest extends WPBB_UnitTestCase {
  use MatchesSnapshots;

  public function test_serialize() {
    $bracket = $this->create_bracket([
      'id' => 100000,
      'title' => 'Test Bracket',
      'published_date' => '2020-01-01 00:00:00',
      'slug' => 'test-bracket',
      'month' => 'January',
      'year' => '2020',
      'matches' => [
        new BracketMatch([
          'id' => 100001,
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'id' => 100002,
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'id' => 100003,
            'name' => 'Team 2',
          ]),
        ]),
        new BracketMatch([
          'id' => 100004,
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team([
            'id' => 100005,
            'name' => 'Team 3',
          ]),
          'team2' => new Team([
            'id' => 100006,
            'name' => 'Team 4',
          ]),
        ]),
      ],
      'results' => [
        new MatchPick([
          'id' => 100007,
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 100002,
        ]),
        new MatchPick([
          'id' => 100008,
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => 100006,
        ]),
        new MatchPick([
          'id' => 100009,
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => 100006,
        ]),
      ],
    ]);

    $serializer = new BracketSerializer();
    $serialized = $serializer->serialize($bracket);
    $this->assertMatchesSnapshot($serialized);
  }

  public function test_deserialize() {
    $data = [
      'title' => 'Test Bracket',
      'status' => 'publish',
      'author' => 1,
      'month' => 'test month',
      'year' => 'test year',
      'num_teams' => 8,
      'wildcard_placement' => 0,
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
        [
          'round_index' => 0,
          'match_index' => 1,
          'team1' => [
            'name' => 'Team 3',
          ],
          'team2' => [
            'name' => 'Team 4',
          ],
        ],
      ],
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 100002,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => 100006,
        ],
        [
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => 100006,
        ],
      ],
    ];

    $serializer = new BracketSerializer();
    $bracket = $serializer->deserialize($data);
    $this->assertInstanceOf(Bracket::class, $bracket);
    $matches = $bracket->matches;
    $this->assertCount(2, $matches);
    $this->assertInstanceOf(BracketMatch::class, $matches[0]);
    $this->assertInstanceOf(BracketMatch::class, $matches[1]);
    $team1 = $matches[0]->team1;
    $team2 = $matches[0]->team2;
    $this->assertInstanceOf(Team::class, $team1);
    $this->assertInstanceOf(Team::class, $team2);
    $team3 = $matches[1]->team1;
    $team4 = $matches[1]->team2;
    $this->assertInstanceOf(Team::class, $team3);
    $this->assertInstanceOf(Team::class, $team4);
    $results = $bracket->results;
    $this->assertCount(3, $results);
    $this->assertInstanceOf(MatchPick::class, $results[0]);
    $this->assertInstanceOf(MatchPick::class, $results[1]);
    $this->assertInstanceOf(MatchPick::class, $results[2]);
    $this->assertMatchesSnapshot((array) $bracket);
  }
}
