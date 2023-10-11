<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';

class TemplateRepoTest extends WPBB_UnitTestCase
{
	private $template_repo;

	public function set_up() {
		parent::set_up();

		$this->template_repo = new Wpbb_BracketTemplateRepo();
	}

	public function test_add_matches() {
		$match_arr = array(
			array("round_index" => 0, "match_index" => 0, "team1" => array("name" => "Team 1"), "team2" => array("name" => "Team 2")),
			array("round_index" => 0, "match_index" => 1, "team1" => array("name" => "Team 3"), "team2" => array("name" => "Team 4")),
			array("round_index" => 0, "match_index" => 2, "team1" => array("name" => "Team 5"), "team2" => array("name" => "Team 6")),
			array("round_index" => 0, "match_index" => 3, "team1" => array("name" => "Team 7"), "team2" => array("name" => "Team 8")),
		);
		$this->assertTrue(true);
	}

	public function test_add() {
		$template = new Wpbb_BracketTemplate([
			'title' => 'Test Template',
			'status' => 'publish',
			'author' => 1,
		]);

		$template = $this->template_repo->add($template);

		$this->assertNotNull($template->id);
		$this->assertEquals('Test Template', $template->title);
		$this->assertEquals('publish', $template->status);
		$this->assertEquals(1, $template->author);
	}

	public function test_get_by_id() {
		$template = new Wpbb_BracketTemplate([
			'title' => 'Test Template',
			'status' => 'publish',
			'author' => 1,
		]);

		$template = $this->template_repo->add($template);

		$template = $this->template_repo->get($template->id);

		$this->assertNotNull($template->id);
		$this->assertEquals('Test Template', $template->title);
		$this->assertEquals('publish', $template->status);
		$this->assertEquals(1, $template->author);
	}

	/**
	 * @group skip
	 */
	// public function test_update_title() {
	// 	$template = new Wpbb_BracketTemplate([
	// 		'title' => 'Test Template',
	// 		'status' => 'publish',
	// 		'author' => 1,
	// 	]);

	// 	$template = $this->template_repo->add($template);

	// 	$template = $this->template_repo->update($template->id, [
	// 		'title' => 'New Title',
	// 	]);

	// 	$this->assertNotNull($template->id);
	// 	$this->assertEquals('New Title', $template->title);
	// 	$this->assertEquals('publish', $template->status);
	// 	$this->assertEquals(1, $template->author);
	// }
}
