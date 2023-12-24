<?php

use Spatie\Snapshots\MatchesSnapshots;
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
}
