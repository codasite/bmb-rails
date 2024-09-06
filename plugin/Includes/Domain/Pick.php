<?php
namespace WStrategies\BMB\Includes\Domain;

use WStrategies\BMB\Includes\Service\BracketMatchService;

/**
 * A Pick is chosen winning team for a match in a bracket
 */
class Pick implements BracketMatchNodeInterface {
  /**
   * @var int
   */
  public $id;

  /**
   * @var int
   */
  public $round_index;

  /**
   * @var int
   */
  public $match_index;

  /**
   * @var Team|null
   */
  public $winning_team;

  /**
   * Percentage of players who picked this team to win for the round_index and
   * match_index.
   */
  public ?float $popularity;

  /**
   * @var int
   */
  public $winning_team_id;
  // This is private so that it doesn't get serialized
  private ?\DateTimeImmutable $updated_at;

  public function __construct($data = []) {
    $this->round_index = (int) $data['round_index'];
    $this->match_index = (int) $data['match_index'];
    $this->winning_team_id = (int) $data['winning_team_id'];
    $this->winning_team = $data['winning_team'] ?? null;
    $this->popularity = isset($data['popularity'])
      ? (float) $data['popularity']
      : null;
    $this->id = isset($data['id']) ? (int) $data['id'] : null;
    $this->updated_at = $data['updated_at'] ?? null;
  }

  public static function from_array($data): Pick {
    if (isset($data['winning_team'])) {
      $data['winning_team'] = Team::from_array($data['winning_team']);
    }

    return new Pick($data);
  }

  public function to_array(): array {
    return [
      'id' => $this->id,
      'round_index' => $this->round_index,
      'match_index' => $this->match_index,
      'winning_team_id' => $this->winning_team_id,
      'winning_team' => $this->winning_team
        ? $this->winning_team->to_array()
        : null,
      'updated_at' => $this->updated_at,
      'popularity' => $this->popularity,
    ];
  }

  public function get_round_index(): int {
    return $this->round_index;
  }

  public function get_match_index(): int {
    return $this->match_index;
  }

  public function get_winning_team(): ?Team {
    return $this->winning_team;
  }

  public function get_updated_at(): ?\DateTimeImmutable {
    return $this->updated_at;
  }

  /**
   * Returns an array of teams picked by this play ranked according to the highest round and match index the team was picked in.
   * For example the final winning team is the first element in the array, the second place team is the second element and so on.
   *
   * @param Pick[] $picks
   * @return Team[]
   */
  public static function get_ranked_teams(array $picks): array {
    /**
     * Ensure that the picks are sorted by round and match index, and then reverse the order
     * so that the first pick corresponds to the highest round and match index.
     *
     * @var Pick[] $reversed
     */
    $reversed = array_reverse(BracketMatchService::sort_match_node($picks));

    /**
     * An array of all the teams picked by this play in the order same order as above.
     * This array will contain duplicate teams
     *
     * @var Team[] $all_teams
     */
    $all_teams = array_map(function ($pick) {
      return $pick->winning_team;
    }, $reversed);

    /**
     * Extract only the team ids from the above array
     *
     * @var int[] $team_ids
     */
    $team_ids = array_map(function ($team) {
      return $team->id;
    }, $all_teams);

    /**
     * Remove duplicate team ids, keeping only the first occurrence.
     * This will give us a unique set of team ids ranked from highest round and match index to lowest.
     *
     * @var int[] $unique_ids
     */
    $unique_ids = array_unique($team_ids);

    /**
     * Create a map of team ids to teams. This is used to look up teams by their ids
     *
     * @var array<int, Team> $team_id_map
     */
    $team_id_map = Team::get_team_id_map($all_teams);

    /**
     * Finally, create an array of teams from the unique team ids
     *
     * @var Team[] $ranked_teams
     */
    $ranked_teams = [];
    foreach ($unique_ids as $id) {
      $ranked_teams[] = $team_id_map[$id];
    }
    return $ranked_teams;
  }
}
