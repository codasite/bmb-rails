<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wp-bracket-builder-bracket-template.php';

class BracketTemplateTest extends WPBB_UnitTestCase {

	public function test_get_post_type() {
		$this->assertEquals('bracket_template', Wp_Bracket_Builder_Bracket_Template::get_post_type());
	}

	public function test_constructor() {
		$args = [
			'title' => 'Test Template',
			'status' => 'publish',
			'author' => 1,
		];
		$template = new Wp_Bracket_Builder_Bracket_Template($args);
		$this->assertInstanceOf(Wp_Bracket_Builder_Bracket_Template::class, $template);
	}
}
