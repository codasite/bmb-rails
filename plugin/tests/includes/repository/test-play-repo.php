<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR .
  'includes/domain/class-wpbb-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-play-repo.php';

class PlayRepoTest extends WPBB_UnitTestCase {
  private $tournament_repo;
  private $play_repo;

  public function set_up() {
    parent::set_up();

    $this->tournament_repo = new Wpbb_BracketTournamentRepo();
    $this->play_repo = new Wpbb_BracketPlayRepo();
  }

  public function test_add() {
    $template = self::factory()->template->create_and_get();
    $tournament = self::factory()->tournament->create_and_get([
      'bracket_template_id' => $template->id,
    ]);

    $play = new Wpbb_BracketPlay([
      'tournament_id' => $tournament->id,
      'author' => 1,
      'picks' => [
        ['round_index' => 0, 'match_index' => 0, 'winning_team_id' => 1],
        ['round_index' => 0, 'match_index' => 1, 'winning_team_id' => 2],
      ],
    ]);
  }

  // public function test_add() {
  // 	$play = new Wpbb_BracketPlay([
  // 		'tournament_id' => 1,
  // 		'author' => 1,
  // 		'picks' => [
  // 			['round_index' => 0, 'match_index' => 0, 'winning_team_id' => 1],
  // 			['round_index' => 0, 'match_index' => 1, 'winning_team_id' => 2],
  // 		]
  // 	]);

  // 	$play = $this->play_repo->add($play);

  // 	$this->assertNotNull($play->id);
  // 	$this->assertEquals(1, $play->tournament_id);
  // 	$this->assertEquals(1, $play->author);
  // 	$this->assertCount(2, $play->picks);
  // }

  // public function test_add_invalid_tournament_id() {
  // 	$play = new Wpbb_BracketPlay([
  // 		'tournament_id' => 1,
  // 		'author' => 1,
  // 		'picks' => [
  // 			['round_index' => 0, 'match_index' => 0, 'winning_team_id' => 1],
  // 			['round_index' => 0, 'match_index' => 1, 'winning_team_id' => 2],
  // 		]
  // 	]);

  // 	$play = $this->play_repo->add($play);

  // 	$this->assertNotNull($play->id);
  // 	$this->assertEquals(1, $play->tournament_id);
  // 	$this->assertEquals(1, $play->author);
  // 	$this->assertCount(2, $play->picks);
  // }
}
