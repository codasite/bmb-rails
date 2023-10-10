<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wp-bracket-builder-bracket-play.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wp-bracket-builder-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wp-bracket-builder-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wp-bracket-builder-bracket-play-repo.php';

class PlayRepoTest extends WPBB_UnitTestCase {

	private $tournament_repo;
	private $play_repo;

	public function set_up() {
		parent::set_up();

		$this->tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
		$this->play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();
	}

	public function test_add() {
		$this->assertTrue(true);
	}


	// public function test_add() {
	// 	$play = new Wp_Bracket_Builder_Bracket_Play([
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
	// 	$play = new Wp_Bracket_Builder_Bracket_Play([
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
