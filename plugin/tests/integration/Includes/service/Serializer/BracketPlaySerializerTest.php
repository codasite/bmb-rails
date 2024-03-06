<?php
namespace WStrategies\BMB\tests\integration\Includes\service\Serializer;

use Spatie\Snapshots\MatchesSnapshots;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Service\Serializer\PlaySerializer;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class BracketPlaySerializerTest extends WPBB_UnitTestCase {
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
    ]);

    $play = $this->create_play([
      'id' => 100007,
      'slug' => 'test-play',
      'published_date' => '2020-01-01 00:00:00',
      'bracket_id' => 100000,
      'total_score' => 100,
      'accuracy_score' => 0.5,
      'is_printed' => false,
      'is_bustable' => false,
      'is_winner' => false,
      'bmb_official' => false,
      'bracket' => $bracket,
      'picks' => [
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

    $serializer = new PlaySerializer();
    $serialized = $serializer->serialize($play);
    $this->assertMatchesSnapshot($serialized);
  }

  public function test_serialize_with_buster_play() {
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
    ]);

    $busted_play = $this->create_play([
      'id' => 200008,
      'slug' => 'test-play-busted',
      'published_date' => '2020-01-01 00:00:00',
      'bracket_id' => 100000,
      'total_score' => 100,
      'accuracy_score' => 0.5,
      'is_printed' => true,
      'is_bustable' => true,
      'is_winner' => true,
      'bmb_official' => true,
      'bracket' => $bracket,
      'picks' => [
        new Pick([
          'id' => 200007,
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 100002,
        ]),
        new Pick([
          'id' => 200008,
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => 100006,
        ]),
        new Pick([
          'id' => 200009,
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => 100006,
        ]),
      ],
    ]);

    $play = $this->create_play([
      'id' => 100007,
      'slug' => 'test-play',
      'published_date' => '2020-01-01 00:00:00',
      'bracket_id' => 100000,
      'total_score' => 100,
      'accuracy_score' => 0.5,
      'is_printed' => false,
      'is_bustable' => false,
      'is_winner' => false,
      'bmb_official' => false,
      'bracket' => $bracket,
      'busted_id' => 200008,
      'busted_play' => $busted_play,
      'picks' => [
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

    $serializer = new PlaySerializer();
    $serialized = $serializer->serialize($play);
    $this->assertMatchesSnapshot($serialized);
  }

  public function test_deserialize() {
    $data = [
      'bracket_id' => 1,
      'picks' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 1,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => 2,
        ],
        [
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => 3,
        ],
      ],
    ];
    $serializer = new PlaySerializer();
    $play = $serializer->deserialize($data);
    $this->assertInstanceOf(Play::class, $play);
    $picks = $play->picks;
    $this->assertCount(3, $picks);
    $this->assertInstanceOf(Pick::class, $picks[0]);
    $this->assertInstanceOf(Pick::class, $picks[1]);
    $this->assertInstanceOf(Pick::class, $picks[2]);
    $this->assertMatchesJsonSnapshot(json_encode($play));
  }

  public function test_deserialize_required_fields() {
    $data = [];
    $serializer = new PlaySerializer();
    $this->expectException(ValidationException::class);
    // assert exception message
    $this->expectExceptionMessage('Missing required fields: bracket_id, picks');
    $play = $serializer->deserialize($data);
  }

  public function test_deserialize_readonly_fields() {
    $data = [
      'id' => 1,
      'slug' => 'test-play',
      'published_date' => '2020-01-01 00:00:00',
      'bracket_id' => 1,
      'total_score' => 100,
      'accuracy_score' => 0.5,
      'is_printed' => true,
      'is_bustable' => true,
      'is_winner' => true,
      'bmb_official' => true,
      'picks' => [
        [
          'id' => 1,
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 1,
        ],
        [
          'id' => 2,
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => 2,
        ],
        [
          'id' => 3,
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => 3,
        ],
      ],
    ];
    $serializer = new PlaySerializer();
    $play = $serializer->deserialize($data);

    $this->assertNull($play->id);
    $this->assertNull($play->author);
    $this->assertEquals('publish', $play->status);
    $this->assertFalse($play->published_date);
    $this->assertEquals('', $play->slug);
    $this->assertEquals('', $play->author_display_name);
    $this->assertFalse($play->thumbnail_url);
    $this->assertFalse($play->url);
    $this->assertEquals(1, $play->bracket_id);
    $this->assertNull($play->bracket);
    $this->assertCount(3, $play->picks);
    $this->assertInstanceOf(Pick::class, $play->picks[0]);
    $this->assertInstanceOf(Pick::class, $play->picks[1]);
    $this->assertInstanceOf(Pick::class, $play->picks[2]);
    $this->assertNull($play->total_score);
    $this->assertNull($play->accuracy_score);
    $this->assertNull($play->busted_id);
    $this->assertFalse($play->is_printed);
    $this->assertNull($play->busted_play);
    $this->assertFalse($play->is_bustable);
    $this->assertFalse($play->is_winner);
    $this->assertFalse($play->bmb_official);
    $this->assertFalse($play->is_tournament_entry);

    $this->assertMatchesJsonSnapshot(json_encode($play));
  }
}
