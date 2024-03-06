<?php
namespace WStrategies\BMB\tests\integration\Includes\domain;


use WStrategies\BMB\Includes\Domain\Play;

class BracketPlayTest extends WPBB_UnitTestCase {
  public function test_get_post_type() {
    $this->assertEquals('bracket_play', Play::get_post_type());
  }

  public function test_constructor() {
    $args = [
      'bracket_id' => 2,
    ];
    $play = new Play($args);
    $this->assertInstanceOf(Play::class, $play);
  }

  public function test_from_array() {
    $args = [
      'bracket_id' => 716,
      'status' => 'publish',
      'author' => 1,
      'busted_id' => 722,
      'picks' => [
        ['round_index' => 0, 'match_index' => 0, 'winning_team_id' => 1],
        ['round_index' => 0, 'match_index' => 1, 'winning_team_id' => 2],
        ['round_index' => 0, 'match_index' => 2, 'winning_team_id' => 3],
        ['round_index' => 0, 'match_index' => 3, 'winning_team_id' => 4],
      ],
    ];

    $play = Play::from_array($args);
    $this->assertInstanceOf(Play::class, $play);
    $this->assertEquals(716, $play->bracket_id);
    $this->assertEquals(1, $play->author);
    $this->assertEquals(722, $play->busted_id);
    $this->assertCount(4, $play->picks);
  }

  public function test_from_array_bracket_id_is_required() {
    $this->expectException(Exception::class);
    $args = [
      'author' => 1,
      'picks' => [
        ['round_index' => 0, 'match_index' => 0, 'winning_team_id' => 1],
        ['round_index' => 0, 'match_index' => 1, 'winning_team_id' => 2],
      ],
    ];

    $play = Play::from_array($args);
  }

  public function test_from_array_author_is_required() {
    $this->expectException(Exception::class);
    $args = [
      'bracket_id' => 716,
      'picks' => [
        ['round_index' => 0, 'match_index' => 0, 'winning_team_id' => 1],
        ['round_index' => 0, 'match_index' => 1, 'winning_team_id' => 2],
      ],
    ];

    $play = Play::from_array($args);
  }
}
