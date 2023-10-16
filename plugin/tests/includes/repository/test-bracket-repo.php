<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-repo.php';

class BracketRepoTest extends WPBB_UnitTestCase {
  private $bracket_repo;

  public function set_up() {
    parent::set_up();

    $this->bracket_repo = new Wpbb_BracketRepo();
  }

  public function test_add() {
    $bracket = new Wpbb_Bracket([
      'title' => 'Test Bracket',
      'status' => 'publish',
      'author' => 1,
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
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Wpbb_Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Wpbb_Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);

    $bracket = $this->bracket_repo->add($bracket);

    $this->assertNotNull($bracket->id);
    $this->assertEquals('Test Bracket', $bracket->title);
    $this->assertEquals('publish', $bracket->status);
    $this->assertEquals(1, $bracket->author);

    $new_matches = $bracket->matches;

    $this->assertEquals(2, count($new_matches));
    $this->assertEquals(0, $new_matches[0]->round_index);
    $this->assertEquals(0, $new_matches[0]->match_index);
    $this->assertEquals('Team 1', $new_matches[0]->team1->name);
    $this->assertEquals('Team 2', $new_matches[0]->team2->name);
    $this->assertEquals(0, $new_matches[1]->round_index);
    $this->assertEquals(1, $new_matches[1]->match_index);
    $this->assertEquals('Team 3', $new_matches[1]->team1->name);
    $this->assertEquals('Team 4', $new_matches[1]->team2->name);
  }

  public function test_get_by_id() {
    $bracket = new Wpbb_Bracket([
      'title' => 'Test Bracket',
      'status' => 'publish',
      'author' => 1,
    ]);

    $bracket = $this->bracket_repo->add($bracket);

    $bracket = $this->bracket_repo->get($bracket->id);

    $this->assertNotNull($bracket->id);
    $this->assertEquals('Test Bracket', $bracket->title);
    $this->assertEquals('publish', $bracket->status);
    $this->assertEquals(1, $bracket->author);
  }

  public function test_update() {
    $bracket = new Wpbb_Bracket([
      'title' => 'Test Bracket',
      'date' => '2019-01-01 00:00:00',
      'status' => 'publish',
      'author' => 1,
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
    ]);

    $bracket = $this->bracket_repo->add($bracket);

    $bracket = $this->bracket_repo->update($bracket->id, [
      'title' => 'New Title',
      'date' => '2019-01-01 00:00:00',
      'status' => 'archive',
    ]);

    $this->assertNotNull($bracket->id);
    $this->assertEquals('New Title', $bracket->title);
    $this->assertEquals('2019-01-01 00:00:00', $bracket->date);
    $this->assertEquals('archive', $bracket->status);
    $this->assertEquals(1, $bracket->author);
  }

  public function test_update_results() {
    $bracket = new Wpbb_Bracket([
      'title' => 'Test Bracket',
      'status' => 'publish',
      'author' => 1,
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
    ]);

    $bracket = $this->bracket_repo->add($bracket);

    $winning_team_id = $bracket->matches[0]->team1->id;

    $bracket = $this->bracket_repo->update($bracket->id, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $winning_team_id,
        ],
      ],
    ]);

    $this->assertNotNull($bracket->id);
    $this->assertEquals('Test Bracket', $bracket->title);
    $this->assertEquals('publish', $bracket->status);
    $this->assertEquals(1, $bracket->author);

    $new_results = $bracket->results;

    $this->assertEquals(1, count($new_results));
    $this->assertEquals(0, $new_results[0]->round_index);
    $this->assertEquals(0, $new_results[0]->match_index);
    $this->assertEquals($winning_team_id, $new_results[0]->winning_team_id);
  }

  /**
   * @group skip
   */
  // public function test_update_title() {
  // 	$bracket = new Wpbb_Bracket([
  // 		'title' => 'Test Bracket',
  // 		'status' => 'publish',
  // 		'author' => 1,
  // 	]);

  // 	$bracket = $this->bracket_repo->add($bracket);

  // 	$bracket = $this->bracket_repo->update($bracket->id, [
  // 		'title' => 'New Title',
  // 	]);

  // 	$this->assertNotNull($bracket->id);
  // 	$this->assertEquals('New Title', $bracket->title);
  // 	$this->assertEquals('publish', $bracket->status);
  // 	$this->assertEquals(1, $bracket->author);
  // }
}
