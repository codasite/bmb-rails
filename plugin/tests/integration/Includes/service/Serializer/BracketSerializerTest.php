<?php
namespace WStrategies\BMB\tests\integration\Includes\service\Serializer;

use Spatie\Snapshots\MatchesSnapshots;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Service\Serializer\BracketSerializer;
use WStrategies\BMB\Includes\Service\Serializer\PostBaseSerializer;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

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
        new Pick([
          'id' => 100007,
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 100002,
        ]),
        new Pick([
          'id' => 100008,
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => 100006,
        ]),
        new Pick([
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
    $this->assertInstanceOf(Pick::class, $results[0]);
    $this->assertInstanceOf(Pick::class, $results[1]);
    $this->assertInstanceOf(Pick::class, $results[2]);
    $this->assertMatchesJsonSnapshot(json_encode($bracket));
  }

  public function test_deserialize_required_fields() {
    $data = [];

    $serializer = new BracketSerializer();
    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(
      'Missing required fields: title, num_teams, wildcard_placement, matches'
    );
    $bracket = $serializer->deserialize($data);
  }

  public function test_deserialize_readonly_fields() {
    $data = [
      'id' => 100000,
      'title' => 'Test Bracket',
      'author' => 100001,
      'status' => 'score',
      'published_date' => '2020-01-01 00:00:00',
      'slug' => 'test-bracket',
      'author_display_name' => 'Test Author',
      'thumbnail_url' => 'https://example.com/test.jpg',
      'url' => 'https://example.com/test-bracket',
      'month' => 'test month',
      'year' => 'test year',
      'num_teams' => 8,
      'wildcard_placement' => 0,
      'matches' => [
        [
          'id' => 100002,
          'round_index' => 0,
          'match_index' => 0,
          'team1' => [
            'id' => 100003,
            'name' => 'Team 1',
          ],
          'team2' => [
            'id' => 100004,
            'name' => 'Team 2',
          ],
        ],
        [
          'id' => 100005,
          'round_index' => 0,
          'match_index' => 1,
          'team1' => [
            'id' => 100006,
            'name' => 'Team 3',
          ],
          'team2' => [
            'id' => 100007,
            'name' => 'Team 4',
          ],
        ],
      ],
    ];

    $serializer = new BracketSerializer();
    $bracket = $serializer->deserialize($data);
    $this->assertNull($bracket->id);
    $this->assertNull($bracket->author);
    $this->assertEquals('publish', $bracket->status);
    $this->assertFalse($bracket->published_date);
    $this->assertEquals('', $bracket->slug);
    $this->assertEquals('', $bracket->author_display_name);
    $this->assertFalse($bracket->thumbnail_url);
    $this->assertFalse($bracket->url);
    $this->assertEquals('test month', $bracket->month);
    $this->assertEquals('test year', $bracket->year);
    $this->assertEquals(8, $bracket->num_teams);
    $this->assertEquals(0, $bracket->wildcard_placement);
    $this->assertCount(2, $bracket->matches);

    $this->assertMatchesJsonSnapshot(json_encode($bracket));
  }

  public function test_published_bracket_is_open() {
    $bracket = $this->create_bracket([
      'status' => 'publish',
    ]);
    $serializer = new BracketSerializer();
    $serialized = $serializer->serialize($bracket);
    $this->assertTrue($serialized['is_open']);
  }
}
