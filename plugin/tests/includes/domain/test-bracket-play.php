<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';

class BracketPlayTest extends WPBB_UnitTestCase {

	public function test_get_post_type() {
		$this->assertEquals('bracket_play', Wpbb_BracketPlay::get_post_type());
	}

	public function test_constructor() {
		$args = [
			'tournament_id' => 2,
		];
		$play = new Wpbb_BracketPlay($args);
		$this->assertInstanceOf(Wpbb_BracketPlay::class, $play);
	}

	public function test_tournament_id_is_required() {
		$this->expectException(Exception::class);
		$play = new Wpbb_BracketPlay([]);
	}

	public function test_from_array() {
		$args = [
			"tournament_id" => 716,
			"title" => "Barry's Picks",
			"status" => "publish",
			"author" => 1,
			"busted_id" => 722,
			"picks" => [
				["round_index" => 0, "match_index" => 0, "winning_team_id" => 1],
				["round_index" => 0, "match_index" => 1, "winning_team_id" => 2],
				["round_index" => 0, "match_index" => 2, "winning_team_id" => 3],
				["round_index" => 0, "match_index" => 3, "winning_team_id" => 4],
			]
		];

		$play = Wpbb_BracketPlay::from_array($args);
		$this->assertInstanceOf(Wpbb_BracketPlay::class, $play);
		$this->assertEquals(716, $play->tournament_id);
		$this->assertEquals(1, $play->author);
		$this->assertEquals(722, $play->busted_id);
		$this->assertCount(4, $play->picks);
	}

	public function test_from_array_tournament_id_is_required() {
		$this->expectException(Exception::class);
		$args = [
			"author" => 1,
			"picks" => [
				["round_index" => 0, "match_index" => 0, "winning_team_id" => 1],
				["round_index" => 0, "match_index" => 1, "winning_team_id" => 2],
			]
		];

		$play = Wpbb_BracketPlay::from_array($args);
	}

	public function test_from_array_author_is_required() {
		$this->expectException(Exception::class);
		$args = [
			"tournament_id" => 716,
			"picks" => [
				["round_index" => 0, "match_index" => 0, "winning_team_id" => 1],
				["round_index" => 0, "match_index" => 1, "winning_team_id" => 2],
			]
		];

		$play = Wpbb_BracketPlay::from_array($args);
	}

	public function test_from_array_picks_is_required() {
		$this->expectException(Exception::class);
		$args = [
			"tournament_id" => 716,
			"author" => 1,
		];

		$play = Wpbb_BracketPlay::from_array($args);
	}
}
