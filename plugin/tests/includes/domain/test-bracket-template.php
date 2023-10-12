<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR .
  'includes/domain/class-wpbb-bracket-template.php';

class BracketTemplateTest extends WPBB_UnitTestCase {
  public function test_get_post_type() {
    $this->assertEquals(
      'bracket_template',
      Wpbb_BracketTemplate::get_post_type()
    );
  }

  public function test_constructor() {
    $args = [
      'title' => 'Test Template',
      'status' => 'publish',
      'author' => 1,
    ];
    $template = new Wpbb_BracketTemplate($args);
    $this->assertInstanceOf(Wpbb_BracketTemplate::class, $template);
  }
}
