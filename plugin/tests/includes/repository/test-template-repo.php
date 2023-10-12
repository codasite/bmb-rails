<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR .
  'includes/domain/class-wpbb-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-play-repo.php';

class TemplateRepoTest extends WPBB_UnitTestCase {
  private $template_repo;

  public function set_up() {
    parent::set_up();

    $this->template_repo = new Wpbb_BracketTemplateRepo();
  }

  public function test_add_matches() {
    $template = self::factory()->template->create_and_get();
    $matches = [
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
    ];

    $template_data = $this->template_repo->get_template_data($template->id);

    $this->template_repo->insert_matches($template_data['id'], $matches);

    $matches = $this->template_repo->get_matches($template_data['id']);

    $this->assertEquals(2, count($matches));
    $this->assertEquals(0, $matches[0]->round_index);
    $this->assertEquals(0, $matches[0]->match_index);
    $this->assertEquals('Team 1', $matches[0]->team1->name);
    $this->assertEquals('Team 2', $matches[0]->team2->name);
    $this->assertEquals(0, $matches[1]->round_index);

    $this->assertEquals(1, $matches[1]->match_index);
    $this->assertEquals('Team 3', $matches[1]->team1->name);
    $this->assertEquals('Team 4', $matches[1]->team2->name);
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
