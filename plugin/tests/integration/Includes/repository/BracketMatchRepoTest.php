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
}
