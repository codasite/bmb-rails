<?php
namespace WStrategies\BMB\Includes\Domain;

use DateTimeImmutable;
use WStrategies\BMB\Features\Bracket\BracketMetaConstants;

class Bracket extends PostBase implements PostBracketInterface {
  public ?string $month;
  public ?string $year;
  public ?int $num_teams;
  public ?int $wildcard_placement;
  public ?DateTimeImmutable $results_first_updated_at;
  public ?int $num_plays;
  public ?float $fee;
  public bool $should_notify_results_updated;
  public bool $is_voting;
  public int $live_round_index;
  /**
   * @var BracketMatch[]
   */
  public array $matches;
  /**
   * @var Pick[]
   */
  public array $results;
  /**
   * @var Pick[]
   */
  public array $most_popular_picks;

  public function __construct(array $data = []) {
    parent::__construct($data);
    $this->month = $data['month'] ?? null;
    $this->year = $data['year'] ?? null;
    $this->num_teams = (int) ($data['num_teams'] ?? null);
    $this->wildcard_placement = (int) ($data['wildcard_placement'] ?? null);
    $this->matches = $data['matches'] ?? [];
    $this->results = $data['results'] ?? [];
    $this->most_popular_picks = $data['most_popular_picks'] ?? [];
    $this->results_first_updated_at = $data['results_first_updated_at'] ?? null;
    $this->num_plays = (int) ($data['num_plays'] ?? null);
    $this->fee = (float) ($data['fee'] ?? null);
    $this->should_notify_results_updated =
      $data[BracketMetaConstants::SHOULD_NOTIFY_RESULTS_UPDATED] ?? false;
    $this->is_voting = $data['is_voting'] ?? false;
    $this->live_round_index = (int) ($data['live_round_index'] ?? 0);
  }

  public function get_winning_team(): ?Team {
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

  public function live_round_index_is_final(): bool {
    return $this->live_round_index === $this->get_num_rounds() - 1;
  }

  public function highest_possible_score(): int {
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
      'bracket_fee' => $this->fee,
      BracketMetaConstants::SHOULD_NOTIFY_RESULTS_UPDATED => $this->should_notify_results_updated
        ? 1
        : 0,
    ];
  }

  public function get_update_post_meta(): array {
    return [
      'month' => $this->month,
      'year' => $this->year,
      'bracket_fee' => $this->fee,
      BracketMetaConstants::SHOULD_NOTIFY_RESULTS_UPDATED => $this->should_notify_results_updated
        ? 1
        : 0,
    ];
  }

  /**
   * @throws ValidationException
   */
  public static function from_array(array $data): Bracket {
    $requiredFields = ['num_teams', 'wildcard_placement', 'author', 'title'];
    RequiredFieldValidation::validateRequiredFields($data, $requiredFields);
    if (isset($data['matches'])) {
      $matches = [];
      foreach ($data['matches'] as $match) {
        $matches[] = BracketMatch::from_array($match);
      }
      $data['matches'] = $matches;
    }

    if (isset($data['results'])) {
      $results = [];
      foreach ($data['results'] as $result) {
        $results[] = Pick::from_array($result);
      }
      $data['results'] = $results;
    }

    if (isset($data['results_first_updated_at'])) {
      $results_updated = $data['results_first_updated_at'];
      if ($results_updated instanceof DateTimeImmutable) {
        $data['results_first_updated_at'] = $results_updated;
      } else {
        $data['results_first_updated_at'] = new DateTimeImmutable(
          $results_updated
        );
      }
    }
    return new Bracket($data);
  }

  public function get_last_result(): ?Pick {
    if (!$this->results) {
      return null;
    }
    return $this->results[count($this->results) - 1];
  }

  public function to_array(): array {
    $bracket = parent::to_array();
    $bracket['num_teams'] = $this->num_teams;
    $bracket['wildcard_placement'] = $this->wildcard_placement;
    $bracket['month'] = $this->month;
    $bracket['year'] = $this->year;
    $bracket['fee'] = $this->fee;
    $bracket[BracketMetaConstants::SHOULD_NOTIFY_RESULTS_UPDATED] =
      $this->should_notify_results_updated;
    $bracket['results_first_updated_at'] = $this->results_first_updated_at
      ? $this->results_first_updated_at->format('Y-m-d H:i:s')
      : null;
    $bracket['is_voting'] = $this->is_voting;
    $bracket['live_round_index'] = $this->live_round_index;
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

  public function is_open(): bool {
    return $this->status === 'publish';
  }

  public function is_printable(): bool {
    return $this->status !== 'upcoming' && !$this->is_voting;
  }

  public function is_chat_enabled(): bool {
    $enabled_status = ['publish', 'score', 'complete'];
    return in_array($this->status, $enabled_status);
  }

  public function has_fee(): bool {
    return $this->fee > 0;
  }
}
