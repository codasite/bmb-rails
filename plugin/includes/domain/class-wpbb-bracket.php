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
  public $date;
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
    $this->date = $data['date'] ?? null;
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

  public function get_num_rounds(): int {
    if (!$this->num_teams) {
      return 0;
    }
    return ceil(log($this->num_teams, 2));
  }

  public static function get_post_type(): string {
    return 'bracket';
  }

  public function get_post_meta(): array {
    return [
      'num_teams' => $this->num_teams,
      'wildcard_placement' => $this->wildcard_placement,
      'date' => $this->date,
    ];
  }

  public function get_update_post_meta(): array {
    return [
      'date' => $this->date,
    ];
  }

  /**
   * @throws Wpbb_ValidationException
   */
  public static function from_array(array $data): Wpbb_Bracket {
    $requiredFields = [
      'num_teams',
      'wildcard_placement',
      // 'date',
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
    return new Wpbb_Bracket($data);
  }

  public function to_array(): array {
    $bracket = parent::to_array();
    $bracket['num_teams'] = $this->num_teams;
    $bracket['wildcard_placement'] = $this->wildcard_placement;
    $bracket['date'] = $this->date;
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
    return $this->date;
  }

  public function get_num_teams(): int {
    return $this->num_teams;
  }

  public function get_post_id(): int {
    return $this->id;
  }
}
