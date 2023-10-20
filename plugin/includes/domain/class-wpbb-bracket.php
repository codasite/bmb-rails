<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-custom-post-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-match.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-validation-exception.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-bracket-interface.php';

class Wpbb_Bracket extends Wpbb_PostBase implements Wpbb_PostBracketInterface {
  /**
   * @var string
   */
  public $month;
  public $year;
  /**
   * @var int
   */
  public $num_teams;

  /**
   * @var int
   */
  public $wildcard_placement;

  /**
   * @var Wpbb_Match[] Array of Wpbb_Match objects
   */
  public $matches;

  /**
   * @var Wpbb_MatchPick[]
   */
  public $results;

  public function __construct(array $data = []) {
    parent::__construct($data);
    $this->month = $data['month'] ?? null;  
    $this->year = $data['year'] ?? null;
    $this->num_teams = (int) ($data['num_teams'] ?? null);
    $this->wildcard_placement = (int) ($data['wildcard_placement'] ?? null);
    $this->matches = $data['matches'] ?? [];
    $this->results = $data['results'] ?? [];
  }

  public function get_winning_team(): ?Wpbb_Team {
    if (!$this->results) {
      return null;
    }

    $winning_pick = $this->results[count($this->results) - 1];

    return $winning_pick->winning_team;
  }

  public function has_results(): bool {
    return count($this->results) > 0;
  }

  public function get_num_teams(): int {
    if ($this->num_teams !== null) {
      return $this->num_teams;
    }
    if ($this->matches) {
      $team_count = 0;
      foreach ($this->matches as $match) {
        if ($match->team1) {
          $team_count++;
        }
        if ($match->team2) {
          $team_count++;
        }
      }
      return $team_count;
    }
    return 0;
  }

  public function get_num_rounds(): int {
    $num_teams = $this->get_num_teams();
    $num_rounds = 0;
    if ($num_teams !== 0) {
      $num_rounds = ceil(log($num_teams, 2));
    }
    return $num_rounds;
  }

  public function highest_possible_score() {
    $point_values = [1, 2, 4, 8, 16, 32];

    $score = 0;

    foreach ($this->results as $result) {
      $score += $point_values[$result->round_index];
    }

    return $score;
  }

  public static function get_post_type(): string {
    return 'bracket';
  }

  public function get_post_meta(): array {
    return [
      'num_teams' => $this->num_teams,
      'wildcard_placement' => $this->wildcard_placement,
      'month' => $this->month,
      'year' => $this->year,
    ];
  }

  public function get_update_post_meta(): array {
    return [
      'month' => $this->month,
      'year' => $this->year,
    ];
  }

  /**
   * @throws Wpbb_ValidationException
   */
  public static function from_array(array $data): Wpbb_Bracket {
    $requiredFields = [
      'num_teams',
      'wildcard_placement',
      'month',
      'year',
      'author',
      'title',
      'matches',
    ];
    validateRequiredFields($data, $requiredFields);
    $matches = [];
    foreach ($data['matches'] as $match) {
      $matches[] = Wpbb_Match::from_array($match);
    }
    $data['matches'] = $matches;

    if (isset($data['results'])) {
      $results = [];
      foreach ($data['results'] as $result) {
        $results[] = Wpbb_MatchPick::from_array($result);
      }
      $data['results'] = $results;
    }
    $bracket = new Wpbb_Bracket($data);
    return new Wpbb_Bracket($data);
  }

  public function to_array(): array {
    $bracket = parent::to_array();
    $bracket['num_teams'] = $this->num_teams;
    $bracket['wildcard_placement'] = $this->wildcard_placement;
    $bracket['month'] = $this->month;
    $bracket['year'] = $this->year;
    if ($this->matches) {
      $matches = [];
      foreach ($this->matches as $match) {
        $matches[] = $match->to_array();
      }
      $bracket['matches'] = $matches;
    }

    if ($this->results) {
      $results = [];
      foreach ($this->results as $result) {
        $results[] = $result->to_array();
      }
      $bracket['results'] = $results;
    }
    return $bracket;
  }

  public function get_matches(): array {
    return $this->matches;
  }

  public function get_picks(): array {
    return $this->results;
  }

  public function get_title(): string {
    return $this->title;
  }

  public function get_date(): string {
    return $this->month . ' ' . $this->year;
  }

  public function get_post_id(): int {
    return $this->id;
  }
}
