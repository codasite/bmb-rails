<?php

namespace WStrategies\BMB\Features\Bracket\Presentation;

use WP_CLI;
use WP_CLI\Utils;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketMatchRepo;
use WStrategies\BMB\Includes\Repository\TeamRepo;
use WStrategies\BMB\Includes\Service\Serializer\BracketSerializer;
use WStrategies\BMB\Includes\Service\Serializer\BracketMatchSerializer;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Domain\WildcardPlacement;

/**
 * Manages brackets through WP-CLI commands.
 */
class BracketCommand {
  private BracketRepo $bracket_repo;
  private BracketMatchRepo $match_repo;
  private TeamRepo $team_repo;
  private BracketSerializer $serializer;
  private BracketMatchSerializer $match_serializer;

  public function __construct() {
    $this->team_repo = new TeamRepo();
    $this->bracket_repo = new BracketRepo();
    $this->match_repo = new BracketMatchRepo($this->team_repo);
    $this->serializer = new BracketSerializer();
    $this->match_serializer = new BracketMatchSerializer();
  }

  /**
   * Creates a new bracket from JSON input via STDIN.
   *
   * ## OPTIONS
   *
   * [--author=<author_id>]
   * : The WordPress user ID to set as the bracket author
   * ---
   * default: 1
   * ---
   *
   * [--status=<status>]
   * : The status to set for the bracket (publish|private|draft)
   * ---
   * default: private
   * options:
   *   - publish
   *   - private
   *   - draft
   * ---
   *
   * ## EXAMPLES
   *
   *     # Create a bracket from JSON via STDIN
   *     $ echo '{"title":"My Bracket","matches":[...]}' | wp wpbb bracket create
   *
   *     # Create a bracket with a specific author and status
   *     $ cat bracket.json | wp wpbb bracket create --author=123 --status=publish
   *
   * @param array $args
   * @param array $assoc_args
   */
  public function create($args, $assoc_args) {
    try {
      // Read JSON from STDIN
      $json_content = stream_get_contents(STDIN);
      if (empty($json_content)) {
        WP_CLI::error('No JSON data provided via STDIN.');
        return;
      }

      $data = json_decode($json_content, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        WP_CLI::error(sprintf('Invalid JSON: %s', json_last_error_msg()));
        return;
      }

      try {
        $bracket = $this->serializer->deserialize($data);
        $bracket->author = (int) ($assoc_args['author'] ?? 1);
        $bracket->status = $assoc_args['status'] ?? 'private';
      } catch (ValidationException $e) {
        WP_CLI::error(sprintf('Validation error: %s', $e->getMessage()));
        return;
      }

      $saved = $this->bracket_repo->add($bracket);
      // $saved = null;

      if ($saved) {
        WP_CLI::success(
          sprintf(
            'Created bracket with ID: %d, Title: %s, Author: %d, Status: %s, URL: %s',
            $saved->id,
            $saved->title,
            $saved->author,
            $saved->status,
            $saved->get_play_url()
          )
        );
      } else {
        WP_CLI::error('Failed to create bracket');
      }
    } catch (\Exception $e) {
      WP_CLI::error($e->getMessage());
    }
  }

  /**
   * Lists brackets with optional filtering.
   *
   * ## OPTIONS
   *
   * [--author=<author_id>]
   * : Filter brackets by author ID
   *
   * [--status=<status>]
   * : Filter by status (publish|private|draft)
   *
   * [--format=<format>]
   * : Render output in a particular format
   * ---
   * default: table
   * options:
   *   - table
   *   - json
   *   - csv
   *   - yaml
   *   - count
   * ---
   *
   * ## EXAMPLES
   *
   *     # List all brackets
   *     $ wp wpbb bracket list
   *
   *     # List published brackets by a specific author
   *     $ wp wpbb bracket list --author=123 --status=publish
   *
   * @param array $args
   * @param array $assoc_args
   */
  public function list($args, $assoc_args) {
    $query = [];

    if (isset($assoc_args['author'])) {
      $query['author'] = (int) $assoc_args['author'];
    }

    if (isset($assoc_args['status'])) {
      $query['post_status'] = $assoc_args['status'];
    }

    $brackets = $this->bracket_repo->get_all($query);

    if (empty($brackets)) {
      WP_CLI::warning('No brackets found.');
      return;
    }

    $items = array_map(function ($bracket) {
      return [
        'id' => $bracket->id,
        'title' => $bracket->title,
        'author' => $bracket->author,
        'status' => $bracket->status,
        'num_teams' => $bracket->num_teams,
        'is_voting' => $bracket->is_voting ? 'Yes' : 'No',
        'live_round_index' => $bracket->live_round_index,
      ];
    }, $brackets);

    Utils\format_items($assoc_args['format'], $items, [
      'id',
      'title',
      'author',
      'status',
      'num_teams',
      'is_voting',
      'live_round_index',
    ]);
  }

  /**
   * Updates a bracket.
   *
   * ## OPTIONS
   *
   * <id>
   * : The ID of the bracket to update
   *
   * [--matches]
   * : Update matches from JSON input via STDIN
   *
   * [--round-names=<names>]
   * : Update round names using a pipe-separated string. When using Docker, wrap the entire command in single quotes.
   *
   * ## EXAMPLES
   *
   *     # Update bracket matches from JSON via STDIN
   *     $ echo '[{"round_index":0,"match_index":0,...}]' | wp wpbb bracket update 123 --matches
   *
   *     # Update bracket round names (direct WP-CLI)
   *     $ wp wpbb bracket update 123 --round-names="Round 1|Round 2|Finals"
   *
   *     # Update bracket round names (with Docker)
   *     $ docker exec -i wp-dev wp-cli 'wpbb bracket update 123 --round-names="Round 1|Round 2|Finals"'
   *
   * @param array $args
   * @param array $assoc_args
   */
  public function update($args, $assoc_args) {
    if (empty($args[0])) {
      WP_CLI::error('Bracket ID is required.');
      return;
    }

    $post_id = (int) $args[0];
    $bracket = $this->bracket_repo->get($post_id);

    if (!$bracket) {
      WP_CLI::error(sprintf('Bracket with ID %d not found.', $post_id));
      return;
    }

    // Get the custom table bracket ID
    $bracket_id = $this->bracket_repo->get_bracket_id($post_id);
    if (!$bracket_id) {
      WP_CLI::error(
        sprintf('Custom table bracket ID not found for post ID %d.', $post_id)
      );
      return;
    }

    if (!isset($assoc_args['matches']) && !isset($assoc_args['round-names'])) {
      WP_CLI::error(
        'No update options specified. Use --matches to update bracket matches or --round-names to update round names.'
      );
      return;
    }

    try {
      if (isset($assoc_args['matches'])) {
        // Read JSON from STDIN for matches
        $json_content = stream_get_contents(STDIN);
        if (empty($json_content)) {
          WP_CLI::error('No JSON data provided via STDIN.');
          return;
        }

        $data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
          WP_CLI::error(sprintf('Invalid JSON: %s', json_last_error_msg()));
          return;
        }

        if (!is_array($data)) {
          WP_CLI::error('Invalid input: Expected an array of matches.');
          return;
        }

        try {
          $matches = array_map(
            fn($match_data) => $this->match_serializer->deserialize(
              $match_data
            ),
            $data
          );
        } catch (ValidationException $e) {
          WP_CLI::error(
            sprintf('Match validation error: %s', $e->getMessage())
          );
          return;
        }

        // Update matches using match repo
        $this->match_repo->update($bracket_id, $matches);
        WP_CLI::success(
          sprintf(
            'Updated bracket %d with %d matches.',
            $post_id,
            count($matches)
          )
        );
      }

      if (isset($assoc_args['round-names'])) {
        $input = trim($assoc_args['round-names']);
        if ($input === '') {
          WP_CLI::error('Round names cannot be empty.');
          return;
        }

        // Split and trim each name
        $round_names = array_map('trim', explode('|', $input));

        // Check for empty names
        foreach ($round_names as $index => $name) {
          if ($name === '') {
            WP_CLI::error(
              sprintf('Round name at position %d cannot be empty.', $index + 1)
            );
            return;
          }
        }

        $bracket->round_names = $round_names;
        $updated = $this->bracket_repo->update($bracket);

        if ($updated) {
          WP_CLI::success(
            sprintf(
              'Updated bracket %d with %d round names.',
              $post_id,
              count($round_names)
            )
          );
        } else {
          WP_CLI::error('Failed to update round names.');
        }
      }
    } catch (\Exception $e) {
      WP_CLI::error($e->getMessage());
    }
  }

  /**
   * Generates a test bracket with generic team names.
   *
   * ## OPTIONS
   *
   * [--teams=<number>]
   * : Number of teams in the bracket
   * ---
   * default: 8
   * ---
   *
   * [--wildcard-placement=<placement>]
   * : Where to place wildcard/play-in games when team count is not a power of 2
   * ---
   * default: split
   * options:
   *   - top
   *   - bottom
   *   - center
   *   - split
   * ---
   *
   * [--title=<title>]
   * : The title for the bracket
   * ---
   * default: Test Bracket
   * ---
   *
   * [--month=<month>]
   * : The month for the bracket
   * ---
   * default: current month
   * ---
   *
   * [--year=<year>]
   * : The year for the bracket
   * ---
   * default: current year
   * ---
   *
   * [--author=<author_id>]
   * : The WordPress user ID to set as the bracket author
   * ---
   * default: 1
   * ---
   *
   * [--status=<status>]
   * : The status to set for the bracket
   * ---
   * default: private
   * options:
   *   - publish
   *   - private
   *   - draft
   * ---
   *
   * [--is-voting]
   * : Create a voting bracket
   *
   * [--fee=<amount>]
   * : Entry fee for the bracket
   * ---
   * default: 0
   * ---
   *
   * ## EXAMPLES
   *
   *     # Generate simple 8-team test bracket
   *     $ wp wpbb bracket generate
   *
   *     # Generate 16-team bracket with custom title
   *     $ wp wpbb bracket generate --teams=16 --title="March Madness Test"
   *
   *     # Generate bracket with play-in games at top
   *     $ wp wpbb bracket generate --teams=12 --wildcard-placement=top
   *
   *     # Generate voting bracket with entry fee
   *     $ wp wpbb bracket generate --teams=8 --is-voting --fee=10.00 --status=publish
   *
   * @param array $args
   * @param array $assoc_args
   */
  public function generate($args, $assoc_args) {
    // $args is intentionally unused - WP-CLI requires this parameter
    try {
      // Parse and validate arguments
      $num_teams = (int) ($assoc_args['teams'] ?? 8);
      $wildcard_placement_str = strtolower(
        $assoc_args['wildcard-placement'] ?? 'split'
      );
      $title = $assoc_args['title'] ?? 'Test Bracket';
      $month = $assoc_args['month'] ?? strtoupper(date('F'));
      $year = $assoc_args['year'] ?? date('Y');
      $author = (int) ($assoc_args['author'] ?? 1);
      $status = $assoc_args['status'] ?? 'private';
      $is_voting = isset($assoc_args['is-voting']);
      $fee = (float) ($assoc_args['fee'] ?? 0);

      // Validate team count
      if ($num_teams < 2) {
        WP_CLI::error('Number of teams must be at least 2.');
        return;
      }

      // Validate wildcard placement
      if (
        !array_key_exists($wildcard_placement_str, WildcardPlacement::OPTIONS)
      ) {
        $valid_options = implode(', ', array_keys(WildcardPlacement::OPTIONS));
        WP_CLI::error(
          "Invalid wildcard placement '{$wildcard_placement_str}'. Valid options: {$valid_options}"
        );
        return;
      }

      $wildcard_placement = WildcardPlacement::OPTIONS[$wildcard_placement_str];

      // Generate matches
      $matches = $this->generateMatches($num_teams);

      // Create bracket data
      $bracket_data = [
        'title' => $title,
        'num_teams' => $num_teams,
        'wildcard_placement' => $wildcard_placement,
        'month' => $month,
        'year' => $year,
        'author' => $author,
        'status' => $status,
        'matches' => $matches,
        'is_voting' => $is_voting,
        'fee' => $fee,
        'results' => [],
        'most_popular_picks' => [],
        'live_round_index' => 0,
      ];

      try {
        $bracket = new Bracket($bracket_data);
      } catch (ValidationException $e) {
        WP_CLI::error(sprintf('Validation error: %s', $e->getMessage()));
        return;
      }

      $saved = $this->bracket_repo->add($bracket);

      if ($saved) {
        WP_CLI::success(
          sprintf(
            'Generated bracket with ID: %d, Title: %s, Teams: %d, Wildcard: %s, Author: %d, Status: %s, URL: %s',
            $saved->id,
            $saved->title,
            $saved->num_teams,
            $wildcard_placement_str,
            $saved->author,
            $saved->status,
            $saved->get_play_url()
          )
        );
      } else {
        WP_CLI::error('Failed to generate bracket');
      }
    } catch (\Exception $e) {
      WP_CLI::error($e->getMessage());
    }
  }

  /**
   * Generate matches for a given number of teams.
   * Based on BracketTestFactory::generateMatches()
   *
   * @param int $numberOfTeams
   * @return array
   */
  private function generateMatches(int $numberOfTeams): array {
    $matches = [];
    $num_matches = $numberOfTeams / 2;

    for ($i = 0; $i < $num_matches; $i++) {
      $matches[] = new BracketMatch([
        'round_index' => 0,
        'match_index' => $i,
        'team1' => new Team([
          'name' => 'Team ' . ($i * 2 + 1),
        ]),
        'team2' => new Team([
          'name' => 'Team ' . ($i * 2 + 2),
        ]),
      ]);
    }

    return $matches;
  }
}
