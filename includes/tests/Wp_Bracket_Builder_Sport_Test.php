<?php

declare(strict_types=1);
require_once __DIR__ . '/../class-wp-bracket-builder-domain.php';

use PHPUnit\Framework\TestCase;

class Wp_Bracket_Builder_Sport_Test extends TestCase {

	// public function test_from_array() {
	// 	$sport = Wp_Bracket_Builder_Sport::from_array([
	// 		'id' => 1,
	// 		'name' => 'Basketball',
	// 		'teams' => [
	// 			[
	// 				'id' => 1,
	// 				'name' => 'Team 1',
	// 			],
	// 			[
	// 				'id' => 2,
	// 				'name' => 'Team 2',
	// 			],
	// 		],
	// 	]);

	// 	$this->assertEquals($this->sport, $sport);
	// }

	public function test_equals_true() {
		$sport1 = new Wp_Bracket_Builder_Sport('Basketball', 1, [
			new Wp_Bracket_Builder_Team('Team 1', 1),
			new Wp_Bracket_Builder_Team('Team 2', 2),
		]);
		$sport2 = new Wp_Bracket_Builder_Sport('Basketball', 1, [
			new Wp_Bracket_Builder_Team('Team 1', 1),
			new Wp_Bracket_Builder_Team('Team 2', 2),
		]);

		$this->assertTrue($sport1->equals($sport2));
	}

	public function test_equals_false_sport_id() {
		$sport1 = new Wp_Bracket_Builder_Sport('Basketball', 1);
		$sport2 = new Wp_Bracket_Builder_Sport('Basketball', 2);

		$this->assertFalse($sport1->equals($sport2));
	}

	public function test_equals_false_sport_name() {
		$sport1 = new Wp_Bracket_Builder_Sport('Basketball', 1);
		$sport2 = new Wp_Bracket_Builder_Sport('Football', 1);

		$this->assertFalse($sport1->equals($sport2));
	}

	public function test_equals_false_team_id() {
		$sport1 = new Wp_Bracket_Builder_Sport('Basketball', 1, [
			new Wp_Bracket_Builder_Team('Team 1', 1),
			new Wp_Bracket_Builder_Team('Team 2', 2),
		]);
		$sport2 = new Wp_Bracket_Builder_Sport('Basketball', 1, [
			new Wp_Bracket_Builder_Team('Team 1', 1),
			new Wp_Bracket_Builder_Team('Team 2', 3),
		]);

		$this->assertFalse($sport1->equals($sport2));
	}
	public function test_equals_false_team_count() {
		$sport1 = new Wp_Bracket_Builder_Sport('Basketball', 1, [
			new Wp_Bracket_Builder_Team('Team 1', 1),
			new Wp_Bracket_Builder_Team('Team 2', 2),
		]);
		$sport2 = new Wp_Bracket_Builder_Sport('Basketball', 1, [
			new Wp_Bracket_Builder_Team('Team 1', 1),
		]);

		$this->assertFalse($sport1->equals($sport2));
	}
	public function test_equals_false_team_name() {
		$sport1 = new Wp_Bracket_Builder_Sport('Basketball', 1, [
			new Wp_Bracket_Builder_Team('Team 1', 1),
			new Wp_Bracket_Builder_Team('Team 2', 2),
		]);
		$sport2 = new Wp_Bracket_Builder_Sport('Basketball', 1, [
			new Wp_Bracket_Builder_Team('Team 1', 1),
			new Wp_Bracket_Builder_Team('Team 3', 2),
		]);

		$this->assertFalse($sport1->equals($sport2));
	}
}
