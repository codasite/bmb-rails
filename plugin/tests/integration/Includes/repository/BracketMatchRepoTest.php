<?php
namespace WStrategies\BMB\tests\integration\Includes\repository;

use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\BracketMatchRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\TeamRepo;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class BracketMatchRepoTest extends WPBB_UnitTestCase {
  private $match_repo;
  private $team_repo;
  private $bracket_repo;

  public function set_up(): void {
    parent::set_up();

    $this->team_repo = new TeamRepo();
    $this->match_repo = new BracketMatchRepo($this->team_repo);
    $this->bracket_repo = new BracketRepo();
  }

  public function test_delete_matches(): void {
    // Create a bracket first
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 1']),
          'team2' => new Team(['name' => 'Team 2']),
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team(['name' => 'Team 3']),
          'team2' => new Team(['name' => 'Team 4']),
        ]),
      ],
    ]);

    // Get the custom table bracket ID
    $bracket_id = $this->bracket_repo->get_bracket_id($bracket->id);
    $this->assertNotNull(
      $bracket_id,
      'Should have a valid bracket ID in custom table'
    );

    // Verify initial matches using get_bracket
    $initial_bracket = $this->get_bracket($bracket->id);
    $this->assertEquals(
      2,
      count($initial_bracket->matches),
      'Should have 2 initial matches'
    );

    // Delete the matches
    $this->match_repo->delete_matches($bracket_id);

    // Verify matches were deleted using get_bracket
    $updated_bracket = $this->get_bracket($bracket->id);
    $this->assertEquals(
      0,
      count($updated_bracket->matches),
      'Should have no matches after delete'
    );
  }

  public function test_update_matches(): void {
    // Create initial bracket with one match
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 1']),
          'team2' => new Team(['name' => 'Team 2']),
        ]),
      ],
    ]);

    // Get the custom table bracket ID
    $bracket_id = $this->bracket_repo->get_bracket_id($bracket->id);
    $this->assertNotNull(
      $bracket_id,
      'Should have a valid bracket ID in custom table'
    );

    // Verify initial state using get_bracket
    $initial_bracket = $this->get_bracket($bracket->id);
    $this->assertEquals(
      1,
      count($initial_bracket->matches),
      'Should have 1 initial match'
    );

    // Create new matches to update with
    $new_matches = [
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team(['name' => 'Team 3']),
        'team2' => new Team(['name' => 'Team 4']),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 1,
        'team1' => new Team(['name' => 'Team 5']),
        'team2' => new Team(['name' => 'Team 6']),
      ]),
    ];

    // Update the matches
    $this->match_repo->update($bracket_id, $new_matches);

    // Get updated bracket and verify the updates
    $updated_bracket = $this->get_bracket($bracket->id);
    $this->assertEquals(
      2,
      count($updated_bracket->matches),
      'Should have 2 matches after update'
    );
    $this->assertEquals('Team 3', $updated_bracket->matches[0]->team1->name);
    $this->assertEquals('Team 4', $updated_bracket->matches[0]->team2->name);
    $this->assertEquals('Team 5', $updated_bracket->matches[1]->team1->name);
    $this->assertEquals('Team 6', $updated_bracket->matches[1]->team2->name);
  }

  public function test_update_with_empty_matches(): void {
    // Create initial bracket with one match
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 1']),
          'team2' => new Team(['name' => 'Team 2']),
        ]),
      ],
    ]);

    // Get the custom table bracket ID
    $bracket_id = $this->bracket_repo->get_bracket_id($bracket->id);
    $this->assertNotNull(
      $bracket_id,
      'Should have a valid bracket ID in custom table'
    );

    // Verify initial state using get_bracket
    $initial_bracket = $this->get_bracket($bracket->id);
    $this->assertEquals(
      1,
      count($initial_bracket->matches),
      'Should have 1 initial match'
    );

    // Update with empty array
    $this->match_repo->update($bracket_id, []);

    // Verify all matches were removed using get_bracket
    $updated_bracket = $this->get_bracket($bracket->id);
    $this->assertEquals(
      0,
      count($updated_bracket->matches),
      'Should have no matches after update'
    );
  }

  public function test_insert_matches_with_null_teams(): void {
    $bracket = $this->create_bracket(['status' => 'publish', 'matches' => []]);
    $bracket_id = $this->bracket_repo->get_bracket_id($bracket->id);

    $matches = [
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => null,
        'team2' => null,
      ]),
    ];

    $this->match_repo->insert_matches($bracket_id, $matches);

    $updated_bracket = $this->get_bracket($bracket->id);
    $this->assertEquals(1, count($updated_bracket->matches));
    $this->assertNull($updated_bracket->matches[0]->team1);
    $this->assertNull($updated_bracket->matches[0]->team2);
  }

  public function test_insert_matches_with_partial_teams(): void {
    $bracket = $this->create_bracket(['status' => 'publish', 'matches' => []]);
    $bracket_id = $this->bracket_repo->get_bracket_id($bracket->id);

    $matches = [
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team(['name' => 'Team 1']),
        'team2' => null,
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 1,
        'team1' => null,
        'team2' => new Team(['name' => 'Team 2']),
      ]),
    ];

    $this->match_repo->insert_matches($bracket_id, $matches);

    $updated_bracket = $this->get_bracket($bracket->id);
    $this->assertEquals(2, count($updated_bracket->matches));
    $this->assertEquals('Team 1', $updated_bracket->matches[0]->team1->name);
    $this->assertNull($updated_bracket->matches[0]->team2);
    $this->assertNull($updated_bracket->matches[1]->team1);
    $this->assertEquals('Team 2', $updated_bracket->matches[1]->team2->name);
  }

  public function test_get_matches_ordering(): void {
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'matches' => [
        new BracketMatch([
          'round_index' => 1,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 1']),
          'team2' => new Team(['name' => 'Team 2']),
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team(['name' => 'Team 3']),
          'team2' => new Team(['name' => 'Team 4']),
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 5']),
          'team2' => new Team(['name' => 'Team 6']),
        ]),
      ],
    ]);

    $bracket_id = $this->bracket_repo->get_bracket_id($bracket->id);
    $matches = $this->match_repo->get_matches($bracket_id);

    $this->assertEquals(3, count($matches));
    // Should be ordered by round_index, then match_index
    $this->assertEquals(0, $matches[0]->round_index);
    $this->assertEquals(0, $matches[0]->match_index);
    $this->assertEquals('Team 5', $matches[0]->team1->name);

    $this->assertEquals(0, $matches[1]->round_index);
    $this->assertEquals(1, $matches[1]->match_index);
    $this->assertEquals('Team 3', $matches[1]->team1->name);

    $this->assertEquals(1, $matches[2]->round_index);
    $this->assertEquals(0, $matches[2]->match_index);
    $this->assertEquals('Team 1', $matches[2]->team1->name);
  }

  public function test_get_matches_with_nonexistent_bracket(): void {
    $matches = $this->match_repo->get_matches(999999);
    $this->assertEmpty($matches);
  }

  public function test_match_ids_after_insert(): void {
    $bracket = $this->create_bracket(['status' => 'publish', 'matches' => []]);
    $bracket_id = $this->bracket_repo->get_bracket_id($bracket->id);

    $matches = [
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team(['name' => 'Team 1']),
        'team2' => new Team(['name' => 'Team 2']),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 1,
        'team1' => new Team(['name' => 'Team 3']),
        'team2' => new Team(['name' => 'Team 4']),
      ]),
    ];

    $this->match_repo->insert_matches($bracket_id, $matches);

    // Verify each match got an ID
    $this->assertNotNull($matches[0]->id);
    $this->assertNotNull($matches[1]->id);
    $this->assertNotEquals($matches[0]->id, $matches[1]->id);

    // Verify IDs persist in database
    $updated_bracket = $this->get_bracket($bracket->id);
    $this->assertEquals($matches[0]->id, $updated_bracket->matches[0]->id);
    $this->assertEquals($matches[1]->id, $updated_bracket->matches[1]->id);
  }

  public function test_update_preserves_match_order(): void {
    // Create initial bracket with matches in specific order
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 1']),
          'team2' => new Team(['name' => 'Team 2']),
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team(['name' => 'Team 3']),
          'team2' => new Team(['name' => 'Team 4']),
        ]),
      ],
    ]);

    $bracket_id = $this->bracket_repo->get_bracket_id($bracket->id);

    // Update with new matches in different order
    $new_matches = [
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 1,
        'team1' => new Team(['name' => 'Team 5']),
        'team2' => new Team(['name' => 'Team 6']),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team(['name' => 'Team 7']),
        'team2' => new Team(['name' => 'Team 8']),
      ]),
    ];

    $this->match_repo->update($bracket_id, $new_matches);

    // Verify matches are ordered correctly
    $updated_bracket = $this->get_bracket($bracket->id);
    $this->assertEquals(2, count($updated_bracket->matches));
    $this->assertEquals(0, $updated_bracket->matches[0]->match_index);
    $this->assertEquals('Team 7', $updated_bracket->matches[0]->team1->name);
    $this->assertEquals(1, $updated_bracket->matches[1]->match_index);
    $this->assertEquals('Team 5', $updated_bracket->matches[1]->team1->name);
  }
}
