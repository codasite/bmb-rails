<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-team.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-team-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-template-repo.php';

class TeamRepoTest extends WPBB_UnitTestCase {
  private $team_repo;
  private $template_repo;

  public function set_up() {
    parent::set_up();

    $this->team_repo = new Wpbb_BracketTeamRepo();
    $this->template_repo = new Wpbb_BracketTemplateRepo();
  }

  public function test_add() {
    $template = self::factory()->template->create_and_get();
    $team = new Wpbb_Team([
      'name' => 'Test Team',
    ]);
    $template_data = $this->template_repo->get_template_data($template->id);

    $team = $this->team_repo->add($template_data['id'], $team);

    $this->assertNotNull($team->id);
    $this->assertEquals('Test Team', $team->name);
  }

  public function test_get() {
    $template = self::factory()->template->create_and_get();
    $team = new Wpbb_Team([
      'name' => 'Test Team',
    ]);
    $template_data = $this->template_repo->get_template_data($template->id);

    $team = $this->team_repo->add($template_data['id'], $team);
    $team = $this->team_repo->get($team->id);

    $this->assertNotNull($team->id);
    $this->assertEquals('Test Team', $team->name);
  }
}
