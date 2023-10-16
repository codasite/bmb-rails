<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR .
  'includes/domain/class-wpbb-bracket-tournament.php';

class BracketTournamentTest extends WPBB_UnitTestCase {
  public function test_get_post_type() {
    $this->assertEquals(
      'bracket_tournament',
      Wpbb_BracketTournament::get_post_type()
    );
  }

  public function test_constructor() {
    $args = [
      'title' => 'Test Tournament',
      'status' => 'publish',
      'author' => 1,
      'date' => 'test date',
    ];
    $tournament = new Wpbb_BracketTournament($args);
    $this->assertInstanceOf(Wpbb_BracketTournament::class, $tournament);
  }

  public function test_from_array() {
    $args = [
      'title' => 'Test Tournament',
      'status' => 'publish',
      'author' => 1,
      'bracket_template_id' => 684,
      'date' => 'test date',
    ];

    $tournament = Wpbb_BracketTournament::from_array($args);
    $this->assertInstanceOf(Wpbb_BracketTournament::class, $tournament);
    $this->assertEquals(684, $tournament->bracket_template_id);
    $this->assertEquals(1, $tournament->author);
    $this->assertEquals('publish', $tournament->status);
    $this->assertEquals('Test Tournament', $tournament->title);
  }

  public function test_from_array_no_template_id() {
    $this->expectException(Exception::class);
    $args = [
      'title' => 'Test Tournament',
      'status' => 'publish',
      'author' => 1,
    ];

    $tournament = Wpbb_BracketTournament::from_array($args);
  }

  public function test_from_array_no_author() {
    $this->expectException(Exception::class);
    $args = [
      'title' => 'Test Tournament',
      'status' => 'publish',
      'bracket_template_id' => 684,
    ];

    $tournament = Wpbb_BracketTournament::from_array($args);
  }
}
