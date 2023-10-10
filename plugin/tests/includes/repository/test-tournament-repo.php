<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wp-bracket-builder-bracket-play.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wp-bracket-builder-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wp-bracket-builder-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wp-bracket-builder-bracket-play-repo.php';

class TournamentRepoTest extends WPBB_UnitTestCase {

	private $tournament_repo;
	private $template_repo;

	public function set_up() {
		parent::set_up();

		$this->tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
		$this->template_repo = new Wp_Bracket_Builder_Bracket_Template_Repository();
	}

	public function test_add_tournament() {
		$template = self::factory()->template->create_and_get();
		$tournament = new Wp_Bracket_Builder_Bracket_Tournament([
			'title' => 'Test Tournament',
			'status' => 'publish',
			'author' => 1,
			'bracket_template_id' => $template->id,
		]);

		$tournament = $this->tournament_repo->add($tournament);

		$this->assertNotNull($tournament->id);
		$this->assertEquals('Test Tournament', $tournament->title);
		$this->assertEquals('publish', $tournament->status);
		$this->assertEquals(1, $tournament->author);
	}
}
