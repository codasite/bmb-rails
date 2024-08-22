<?php
namespace WStrategies\BMB\Features\VotingBracket;

use WP_Error;
use WP_REST_Response;
use WP_REST_Controller;
use WP_REST_Server;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;
use WStrategies\BMB\Includes\Repository\PickRepo;
use WStrategies\BMB\Includes\Repository\TeamRepo;
use WStrategies\BMB\Includes\Service\ScoreService;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Service\Serializer\BracketSerializer;
use WStrategies\BMB\Includes\Utils;

class VotingBracketApi extends WP_REST_Controller implements HooksInterface {
  private BracketRepo $bracket_repo;
  private VotingBracketService $voting_bracket_service;
  private Utils $utils;

  public function __construct($args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->voting_bracket_service =
      $args['voting_bracket_service'] ??
      new VotingBracketService(new PickRepo(new TeamRepo()));
    $this->utils = $args['utils'] ?? new Utils();
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'brackets';
  }

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  /**
   * Register the routes for handling the voting bracket API.
   */
  public function register_routes(): void {
    $base = 'brackets';

    register_rest_route(
      $this->namespace,
      '/' . $base . '/(?P<bracket_id>[\d]+)/complete-round',
      [
        [
          'methods' => WP_REST_Server::CREATABLE, // POST request
          'callback' => [$this, 'complete_round'], // Complete round callback
          'permission_callback' => [$this, 'can_complete_round'], // Permission check
          'args' => [
            'bracket_id' => [
              'description' => __('Unique identifier for the bracket.'),
              'type' => 'integer',
              'required' => true,
            ],
          ],
        ],
      ]
    );
  }

  /**
   * Handles the logic for completing a round.
   *
   * @param WP_REST_Request $request The current request.
   * @return WP_REST_Response|WP_Error The response or WP_Error object on failure.
   */
  public function complete_round($request) {
    $bracket_id = (int) $request['bracket_id'];
    $bracket = $this->bracket_repo->get($bracket_id);

    // Validate and complete the round for the given bracket ID.
    if ($bracket === null) {
      return new WP_Error('invalid_bracket', __('Invalid bracket ID.'), [
        'status' => 404,
      ]);
    }
    error_log('complete round');

    // If there are no plays for the round return 400.
    if (!$this->voting_bracket_service->has_plays_for_live_round($bracket)) {
      return new WP_Error(
        'no_plays_for_round',
        __('There are no plays for the current round.'),
        ['status' => 400]
      );
    }

    $updated = $this->complete_bracket_round($bracket_id);

    if ($updated) {
      return new WP_REST_Response(
        ['message' => 'Round completed successfully.'],
        200
      );
    } else {
      return new WP_Error(
        'could_not_complete_round',
        __('Could not complete the round.'),
        ['status' => 500]
      );
    }
  }

  /**
   * Checks whether the current user has permission to complete a round.
   *
   * @param WP_REST_Request $request The current request.
   * @return bool|WP_Error True if the user has permission, WP_Error otherwise.
   */
  public function can_complete_round($request) {
    if (!current_user_can('wpbb_edit_bracket', $request['bracket_id'])) {
      return new WP_Error(
        'rest_forbidden',
        'You do not have permission to complete this round.',
        ['status' => 403]
      );
    }

    return true;
  }

  /**
   * Complete the current round for the given bracket ID.
   *
   * @param int $bracket_id Bracket ID.
   * @return bool True if the round was completed, false otherwise.
   */
  private function complete_bracket_round($bracket_id) {
    $bracket = $this->bracket_repo->get($bracket_id);
    $bracket->live_round_index += 1;
    // If the current round is the last round, set the bracket status to 'complete'.
    if ($bracket->live_round_index === $bracket->get_num_rounds()) {
      $bracket->status = 'complete';
    }
    // Calculate the most popular picks for the current round.
    // Save them to the bracket.
    $bracket = $this->bracket_repo->update($bracket);
    return $bracket;
  }
}
