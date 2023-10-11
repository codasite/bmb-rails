<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR .
  'includes/domain/class-wpbb-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-template-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-play-repo.php';

class TournamentRepoTest extends WPBB_UnitTestCase
{
  private $tournament_repo;
  private $template_repo;

  public function set_up()
  {
    parent::set_up();

    $this->tournament_repo = new Wpbb_BracketTournamentRepo();
    $this->template_repo = new Wpbb_BracketTemplateRepo();
  }

  public function test_add_tournament()
  {
    $template = self::factory()->template->create_and_get();
    $tournament = new Wpbb_BracketTournament([
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
