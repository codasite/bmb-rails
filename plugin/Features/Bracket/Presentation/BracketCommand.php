<?php

namespace WStrategies\BMB\Features\Bracket\Presentation;

use WP_CLI;
use WP_CLI\Utils;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Service\Serializer\BracketSerializer;

/**
 * Manages brackets through WP-CLI commands.
 */
class BracketCommand {
  private BracketRepo $bracket_repo;
  private BracketSerializer $serializer;

  public function __construct() {
    $this->bracket_repo = new BracketRepo();
    $this->serializer = new BracketSerializer();
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
        'is_template' => $bracket->is_template ? 'Yes' : 'No',
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
      'is_template',
      'live_round_index',
    ]);
  }
}
