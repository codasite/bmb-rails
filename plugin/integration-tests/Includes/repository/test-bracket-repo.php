<?php

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\BracketRepo;

class BracketRepoTest extends WPBB_UnitTestCase {
  private $bracket_repo;

  public function set_up(): void {
    parent::set_up();

    $this->bracket_repo = new BracketRepo();
  }

  public function test_add() {
    $bracket = new Bracket([
      'title' => 'Test Bracket',
      'status' => 'publish',
      'author' => 1,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);

    $bracket = $this->bracket_repo->add($bracket);

    $this->assertNotNull($bracket->id);
    $this->assertEquals('Test Bracket', $bracket->title);
    $this->assertEquals('publish', $bracket->status);
    $this->assertEquals(1, $bracket->author);

    $new_matches = $bracket->matches;

    $this->assertEquals(2, count($new_matches));
    $this->assertEquals(0, $new_matches[0]->round_index);
    $this->assertEquals(0, $new_matches[0]->match_index);
    $this->assertEquals('Team 1', $new_matches[0]->team1->name);
    $this->assertEquals('Team 2', $new_matches[0]->team2->name);
    $this->assertEquals(0, $new_matches[1]->round_index);
    $this->assertEquals(1, $new_matches[1]->match_index);
    $this->assertEquals('Team 3', $new_matches[1]->team1->name);
    $this->assertEquals('Team 4', $new_matches[1]->team2->name);
  }

  public function test_get_by_id() {
    $bracket = new Bracket([
      'title' => 'Test Bracket',
      'status' => 'publish',
      'author' => 1,
    ]);

    $bracket = $this->bracket_repo->add($bracket);

    $bracket = $this->bracket_repo->get($bracket->id);

    $this->assertNotNull($bracket->id);
    $this->assertEquals('Test Bracket', $bracket->title);
    $this->assertEquals('publish', $bracket->status);
    $this->assertEquals(1, $bracket->author);
  }

  public function test_update() {
    $bracket = new Bracket([
      'title' => 'Test Bracket',
      'month' => 'January',
      'year' => '2019',
      'status' => 'publish',
      'author' => 1,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $bracket = $this->bracket_repo->add($bracket);

    $bracket = $this->bracket_repo->update($bracket->id, [
      'title' => 'New Title',
      'month' => 'February',
      'year' => '2019',
      'status' => 'archive',
    ]);

    $this->assertNotNull($bracket->id);
    $this->assertEquals('New Title', $bracket->title);
    $this->assertEquals('February', $bracket->month);
    $this->assertEquals('2019', $bracket->year);
    $this->assertEquals('archive', $bracket->status);
    $this->assertEquals(1, $bracket->author);
  }

  public function test_update_results() {
    $bracket = new Bracket([
      'title' => 'Test Bracket',
      'status' => 'publish',
      'author' => 1,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $bracket = $this->bracket_repo->add($bracket);

    $winning_team_id = $bracket->matches[0]->team1->id;

    $bracket = $this->bracket_repo->update($bracket->id, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $winning_team_id,
        ],
      ],
    ]);

    $this->assertNotNull($bracket->id);
    $this->assertEquals('Test Bracket', $bracket->title);
    $this->assertEquals('publish', $bracket->status);
    $this->assertEquals(1, $bracket->author);

    $new_results = $bracket->results;

    $this->assertEquals(1, count($new_results));
    $this->assertEquals(0, $new_results[0]->round_index);
    $this->assertEquals(0, $new_results[0]->match_index);
    $this->assertEquals($winning_team_id, $new_results[0]->winning_team_id);
  }

  public function test_update_result_deletes_old_results() {
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'num_teams' => 4,
    ]);

    $this->bracket_repo->update($bracket->id, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ],
      ],
    ]);

    $bracket = $this->bracket_repo->get($bracket->id);

    $this->assertEquals(2, count($bracket->results));

    $this->bracket_repo->update($bracket->id, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ]);

    $bracket = $this->bracket_repo->get($bracket->id);

    $this->assertEquals(2, count($bracket->results));
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $bracket->results[0]->winning_team_id
    );
  }

  public function test_update_empty_results_sets_results_first_updated_at() {
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'num_teams' => 4,
    ]);

    $winning_team_id = $bracket->matches[0]->team1->id;

    $bracket = $this->bracket_repo->update($bracket->id, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $winning_team_id,
        ],
      ],
    ]);

    $this->assertNotFalse($bracket->results_first_updated_at);
  }

  public function test_update_results_first_updated_at() {
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'num_teams' => 4,
    ]);

    $repo = new BracketRepo();

    $time = '2019-01-01 00:00:00';
    $datetime = new DateTimeImmutable($time);
    $repo->update($bracket->id, [
      'results_first_updated_at' => $time,
    ]);

    $updated = $this->get_bracket($bracket->id);

    $this->assertEquals($datetime, $updated->results_first_updated_at);
  }

  public function test_update_bracket_author() {
    $user1 = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'num_teams' => 4,
      'author' => $user1->ID,
    ]);

    $repo = new BracketRepo();

    $repo->update($bracket->id, [
      'author' => $user2->ID,
    ]);

    $updated = $this->get_bracket($bracket->id);

    $this->assertEquals($user2->ID, $updated->author);
  }

  public function test_update_bracket_results_first_updated_at_stays_null() {
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'num_teams' => 4,
    ]);
    $this->assertEquals(null, $bracket->results_first_updated_at);
    $repo = new BracketRepo();
    $repo->update($bracket->id, []);
    $updated = $this->get_bracket($bracket->id);
    $this->assertEquals(null, $updated->results_first_updated_at);
  }
}
