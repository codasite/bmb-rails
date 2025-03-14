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
   * ## EXAMPLES
   *
   *     # Update bracket matches from JSON via STDIN
   *     $ echo '[{"round_index":0,"match_index":0,...}]' | wp wpbb bracket update 123 --matches
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

    if (!isset($assoc_args['matches'])) {
      WP_CLI::error(
        'No update options specified. Use --matches to update bracket matches.'
      );
      return;
    }

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

      if (!is_array($data)) {
        WP_CLI::error('Invalid input: Expected an array of matches.');
        return;
      }

      try {
        $matches = array_map(
          fn($match_data) => $this->match_serializer->deserialize($match_data),
          $data
        );
      } catch (ValidationException $e) {
        WP_CLI::error(sprintf('Match validation error: %s', $e->getMessage()));
        return;
      }

      // Update matches using match repo
      $this->match_repo->update($bracket_id, $matches);

      // Get updated bracket to verify
      $updated_bracket = $this->bracket_repo->get($post_id);
      WP_CLI::success(
        sprintf(
          'Updated bracket %d with %d matches.',
          $post_id,
          count($updated_bracket->matches)
        )
      );
    } catch (\Exception $e) {
      WP_CLI::error($e->getMessage());
    }
  }
}
