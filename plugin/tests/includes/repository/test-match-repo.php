<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-team.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-match.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-team-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-match-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-template-repo.php';

class MatchRepoTest extends WPBB_UnitTestCase {
  private $match_repo;
  private $template_repo;

  public function set_up() {
    parent::set_up();

    $this->match_repo = new Wpbb_BracketMatchRepo();
    $this->template_repo = new Wpbb_BracketTemplateRepo();
  }

  public function test_add() {
    $template = self::factory()->template->create_and_get();
    $team1 = new Wpbb_Team([
      'name' => 'Test Team 1',
    ]);
    $team2 = new Wpbb_Team([
      'name' => 'Test Team 2',
    ]);

    $match = new Wpbb_Match([
      'round_index' => 0,
      'match_index' => 0,
      'team1' => $team1,
      'team2' => $team2,
    ]);

    $template_data = $this->template_repo->get_template_data($template->id);
    print_r($template_data);

    $this->match_repo->insert_match($template_data['id'], $match);

    $this->assertNotNull($match->id);
    $this->assertEquals(0, $match->round_index);
    $this->assertEquals(0, $match->match_index);
    $this->assertEquals('Test Team 1', $match->team1->name);
    $this->assertEquals('Test Team 2', $match->team2->name);
  }

  public function test_get() {
    // $template = self::factory()->template->create_and_get();
    // $match = self::factory()->match->create_and_get([
    //   'template_id' => $template->id,
    // ]);
    // $template_data = $this->template_repo->get_template_data($template->id);

    // $team = $this->team_repo->add($template_data['id'], $team);
    // $team = $this->team_repo->get($team->id);

    // $this->assertNotNull($team->id);
    // $this->assertEquals('Test Team', $team->name);
  }
}
