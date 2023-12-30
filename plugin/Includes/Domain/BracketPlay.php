<?php
namespace WStrategies\BMB\Includes\Domain;

use Exception;

class BracketPlay extends PostBase implements PostBracketInterface {
  /**
   * @var int
   */
  public $bracket_id;

  /**
   * @var Bracket| null
   */
  public mixed $bracket;

  /**
   * @var MatchPick[]
   */
  public $picks;

  /**
   * @var int
   */
  public $total_score;

  /**
   * @var float
   */
  public $accuracy_score;

  /**
   * @var int
   */
  public $busted_id;

  /**
   * @var bool
   */
  public $is_printed;

  /**
   * @var BracketPlay
   */
  public $busted_play;

  /**
   * @var bool
   */
  public $is_bustable;

  /**
   * @var bool
   */
  public $is_winner;

  /**
   * @var bool
   */
  public $bmb_official;

  /**
   * @var bool
   */
  public $is_tournament_entry;

  public function __construct(array $data = []) {
    parent::__construct($data);
    $this->bracket_id = isset($data['bracket_id'])
      ? (int) $data['bracket_id']
      : null;
    $this->bracket = $data['bracket'] ?? null;
    $this->picks = $data['picks'] ?? [];
    $this->total_score = $data['total_score'] ?? null;
    $this->accuracy_score = $data['accuracy_score'] ?? null;
    $this->busted_id = isset($data['busted_id'])
      ? (int) $data['busted_id']
      : null;
    $this->is_printed = isset($data['is_printed'])
      ? (bool) $data['is_printed']
      : false;
    $this->busted_play = $data['busted_play'] ?? null;
    $this->is_bustable = isset($data['is_bustable'])
      ? (bool) $data['is_bustable']
      : false;
    $this->is_winner = isset($data['is_winner'])
      ? (bool) $data['is_winner']
      : false;
    $this->bmb_official = isset($data['bmb_official'])
      ? (bool) $data['bmb_official']
      : false;
    $this->is_tournament_entry = isset($data['is_tournament_entry'])
      ? (bool) $data['is_tournament_entry']
      : false;
  }

  public static function get_post_type(): string {
    return 'bracket_play';
  }

  public function get_winning_team(): ?Team {
    if (count($this->picks) === 0) {
      return null;
    }
    return $this->picks[count($this->picks) - 1]->winning_team;
  }

  public function get_post_meta(): array {
    return [
      'bracket_id' => $this->bracket_id,
    ];
  }

  public function get_update_post_meta(): array {
    return [];
  }

  /**
   * @throws ValidationException
   */
  public static function from_array($data): BracketPlay {
    RequiredFieldValidation::validateRequiredFields($data, [
      'bracket_id',
      'author',
    ]);
    $data['picks'] = self::get_picks_from_array($data);

    return new BracketPlay($data);
  }

  private static function get_picks_from_array($data): array {
    $picks = [];
    if (!isset($data['picks'])) {
      return $picks;
    }
    foreach ($data['picks'] as $pick) {
      $picks[] = MatchPick::from_array($pick);
    }
    return $picks;
  }

  public function to_array(): array {
    $play = parent::to_array();
    $play['bracket_id'] = $this->bracket_id;
    $play['bracket'] = $this->bracket->to_array();
    $play['total_score'] = $this->total_score;
    $play['accuracy_score'] = $this->accuracy_score;
    $play['busted_id'] = $this->busted_id;
    $play['is_bustable'] = $this->is_bustable;
    $play['is_winner'] = $this->is_winner;
    $play['bmb_official'] = $this->bmb_official;
    $play['is_tournament_entry'] = $this->is_tournament_entry;
    if (!empty($this->busted_play)) {
      $play['busted_play'] = $this->busted_play->to_array();
    }
    $play['is_printed'] = $this->is_printed;
    if ($this->picks) {
      $play['picks'] = [];
      foreach ($this->picks as $pick) {
        $play['picks'][] = $pick->to_array();
      }
    }
    return $play;
  }

  public function get_post_id(): int {
    return $this->id;
  }

  public function get_matches(): array {
    return $this->bracket->get_matches();
  }

  public function get_picks(): array {
    return $this->picks;
  }

  public function get_title(): string {
    return $this->bracket->title;
  }

  public function get_date(): string {
    return $this->bracket->get_date();
  }

  public function get_num_teams(): int {
    return $this->bracket->get_num_teams();
  }
}
