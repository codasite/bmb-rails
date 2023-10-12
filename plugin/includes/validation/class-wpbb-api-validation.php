<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  '/domain/class-wpbb-bracket.php';

class Wpbb_ApiValidation {
  private $total_rounds;

  private $wildcards;

  private $bracket;
  private $wpdb;

  public function validate_bracket_api($bracket) {
    $this->bracket = $bracket;
    $this->total_rounds = $this->bracket->num_rounds;
    $this->wildcards = $this->bracket->num_wildcards;

    $teams = get_option('bracket_builder_max_teams');
    $total_no_of_teams = $this->get_team_count(
      $this->total_rounds,
      $this->wildcards
    );
    if ($total_no_of_teams > $teams) {
      return $this->createError(
        'Given Number of teams is more than maximum allowed teams to participate'
      );
    }

    if (!$this->is_valid_wildcard()) {
      return $this->createError('Invalid wildcard');
    }

    $validate = $this->check_team_names();
    return $validate;
  }

  //Method to check team names at initial level
  private function check_team_names() {
    $total_no_of_teams = $this->get_team_count(
      $this->total_rounds,
      $this->wildcards
    );
    $teams_found = 0;
    $round = end($this->bracket->rounds);
    $second_round = $this->bracket->rounds[$this->total_rounds - 2];

    foreach ($round->matches as $index => $match) {
      if (is_null($match) && $this->wildcards != 0) {
        $team = $second_round->matches[floor($index / 2)]->team1;
        if ($index % 2 !== 0) {
          $team = $second_round->matches[floor($index / 2)]->team2;
        }
        if (is_null($team)) {
          return $this->createError(
            'Wild Card Team Name not found in second round'
          );
        }
        $teams_found++;
      } elseif (
        is_null($match) ||
        is_null($match->team1) ||
        is_null($match->team2)
      ) {
        return $this->createError('Team Name not found in first round');
      } else {
        $teams_found += 2;
      }
    }

    if ($total_no_of_teams != $teams_found) {
      return $this->createError('Number of teams mismatch');
    }
    return $this->check_inner_round_teams();
  }

  //Method to check if any team names are given in inner most rounds will throw error
  //Accepts team names only in initial level of rounds (in case of wild cards acccepts till round2)
  public function check_inner_round_teams() {
    $second_round = $this->bracket->rounds[$this->total_rounds - 2];

    if ($this->wildcards != 0) {
      $round = end($this->bracket->rounds);
      foreach ($round->matches as $index => $match) {
        if (!is_null($match)) {
          $team_name =
            $second_round->matches[floor($index / 2)]
              ->{'team' . ($index % 2 === 0 ? 1 : 2)};
          if (!is_null($team_name)) {
            return $this->createError('Invalid placement of team name');
          }
        }
      }
    }

    $no_of_rounds_to_check =
      $this->total_rounds - ($this->wildcards != 0 ? 2 : 1);

    foreach ($this->bracket->rounds as $round) {
      if ($round->depth < $no_of_rounds_to_check) {
        foreach ($round->matches as $match) {
          if (!is_null($match->team1) || !is_null($match->team2)) {
            return $this->createError(
              'Team Name should not accept in ' . $round->name
            );
          }
        }
      }
    }
  }

  //Checks whether the given number of wildcards matches with the number of teams
  public function is_valid_wildcard() {
    $total_teams = 2 ** $this->total_rounds;
    return $this->wildcards >= 0 &&
      $this->wildcards <= $total_teams / 2 &&
      $this->wildcards % 2 === 0;
  }

  private function createError($message) {
    return new WP_Error('cant-create', __($message, 'text-domain'), [
      'status' => 400,
    ]);
  }

  // To get the total of teams count in bracket
  public function get_team_count($rounds, $wildcards) {
    $total_no_of_teams = 2 ** $rounds;

    if ($wildcards != 0) {
      $teams_in_first_round = $wildcards * 2;
      $teams_not = $total_no_of_teams - $teams_in_first_round;
      $total_no_of_teams =
        $teams_in_first_round +
        ($total_no_of_teams - $teams_in_first_round) / 2;
    }

    return $total_no_of_teams;
  }
}
