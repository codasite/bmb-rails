<?php
namespace WStrategies\BMB\Includes\Controllers;

use WP_Error;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Service\PaidTournamentService\PaidTournamentServiceInterface;
use WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato\GelatoProductIntegration;
use WStrategies\BMB\Includes\Service\ProductIntegrations\ProductIntegrationInterface;
use WStrategies\BMB\Includes\Service\Serializer\BracketPlaySerializer;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripePaidTournamentService;
use WStrategies\BMB\Includes\Service\TournamentEntryService;
use WStrategies\BMB\Includes\Utils;

class BracketPlayApi extends WP_REST_Controller implements HooksInterface {
  /**
   * @var BracketPlayRepo
   */
  private $play_repo;

  /**
   * @var Utils
   */
  private $utils;

  // /**
  //  * @var Bracket_Pick_Service
  //  */
  // private $bracket_pick_service;

  /**
   * @var string
   */
  protected $namespace;

  /**
   * @var string
   */
  protected $rest_base;

  /**
   * @var ProductIntegrationInterface
   */
  private $product_integration;

  /**
   * @var TournamentEntryService
   */
  private TournamentEntryService $tournament_entry_service;

  private BracketPlaySerializer $serializer;

  private PaidTournamentServiceInterface $paid_tournament_service;

  public function __construct($args = []) {
    $this->utils = $args['utils'] ?? new Utils();
    $this->play_repo = $args['play_repo'] ?? new BracketPlayRepo();
    $this->product_integration =
      $args['product_integration'] ?? new GelatoProductIntegration();
    $this->tournament_entry_service =
      $args['tournament_entry_service'] ?? new TournamentEntryService();
    $this->serializer = $args['serializer'] ?? new BracketPlaySerializer();
    try {
      $this->paid_tournament_service =
        $args['paid_tournament_service'] ?? new StripePaidTournamentService();
    } catch (\Exception $e) {
      error_log('Caught error: ' . $e->getMessage());
    }
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'plays';
  }

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  /**
   * Register the routes for bracket objects.
   * Adapted from: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
   */
  public function register_routes(): void {
    $namespace = $this->namespace;
    $base = $this->rest_base;
    register_rest_route($namespace, '/' . $base, [
      [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$this, 'get_items'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => [
          'bracket_id' => [
            'description' => 'The ID of the bracket.',
            'type' => 'integer',
            'required' => false, // Set to true if the parameter is required
            'sanitize_callback' => 'absint', // Sanitize the input as an absolute integer value
            'validate_callback' => function ($param, $request, $key) {
              return is_numeric($param); // Validate that the input is a numeric value
            },
          ],
        ],
      ],
      [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [$this, 'create_item'],
        'permission_callback' => [$this, 'create_play_permission_check'],
        'args' => $this->get_endpoint_args_for_item_schema(
          WP_REST_Server::CREATABLE
        ),
      ],
      'schema' => [$this, 'get_public_item_schema'],
    ]);
    register_rest_route($namespace, '/' . $base . '/(?P<item_id>[\d]+)', [
      'args' => [
        'item_id' => [
          'description' => __('Unique identifier for the object.'),
          'type' => 'integer',
        ],
      ],
      [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$this, 'get_item'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => [
          'context' => $this->get_context_param(['default' => 'view']),
        ],
      ],
      [
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => [$this, 'update_item'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => $this->get_endpoint_args_for_item_schema(
          WP_REST_Server::EDITABLE
        ),
      ],
      [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => [$this, 'delete_item'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => [
          'force' => [
            'default' => false,
            'description' => __(
              'Required to be true, as resource does not support trashing.'
            ),
            'type' => 'boolean',
          ],
        ],
      ],
    ]);
    register_rest_route(
      $this->namespace,
      '/' . $base . '/(?P<item_id>[\d]+)/generate-images',
      [
        [
          'methods' => WP_REST_Server::CREATABLE,
          'callback' => [$this, 'generate_images'],
          'permission_callback' => [$this, 'customer_permission_check'],
          'args' => array_merge(
            [
              'item_id' => [
                'description' => __('Unique identifier for the object.'),
                'type' => 'integer',
                'required' => true,
              ],
            ],
            $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE)
          ),
        ],
      ]
    );
  }

  /**
   * Retrieves a collection of brackets.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request): WP_Error|WP_REST_Response {
    // $bracket_id = $request->get_param('bracket_id');
    $the_query = new WP_Query([
      'post_type' => BracketPlay::get_post_type(),
      'post_status' => 'any',
    ]);
    $plays = $this->play_repo->get_all($the_query);
    $serialized = [];
    foreach ($plays as $play) {
      $serialized[] = $this->serializer->serialize($play);
    }

    return new WP_REST_Response($serialized, 200);
  }

  /**
   * Retrieves a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request): WP_Error|WP_REST_Response {
    // get id from request
    $id = $request->get_param('item_id');
    $play = $this->play_repo->get($id);
    $serialized = $this->serializer->serialize($play);
    return new WP_REST_Response($serialized, 200);
  }

  /**
   * Creates a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request): WP_Error|WP_REST_Response {
    $params = $request->get_params();
    $bracket_id = $params['bracket_id'];
    try {
      $play = $this->serializer->deserialize($params);
    } catch (ValidationException $e) {
      return new WP_Error('validation-error', $e->getMessage(), [
        'status' => 400,
      ]);
    }
    $play->author = get_current_user_id();
    $play->bmb_official = has_tag('bmb_official', $bracket_id);
    $saved = $this->play_repo->add($play);
    $this->paid_tournament_service->on_play_created($saved);
    $this->tournament_entry_service->try_mark_play_as_tournament_entry($saved);
    // Generate the bracket images
    if (
      isset($params['generate_images']) &&
      $params['generate_images'] === true
    ) {
      if (!$this->product_integration->has_all_configs()) {
        $this->product_integration->generate_images($saved);
      }
      // set the play id in the session
      $this->utils->set_cookie('play_id', $saved->id, ['days' => 30]);
    }

    // check if user logged in
    if (!is_user_logged_in()) {
      // if (get_current_user_id() === 0)
      $this->utils->set_cookie('play_id', $saved->id, ['days' => 30]);

      // nonce
      $bytes = random_bytes(32);
      $nonce = base64_encode($bytes);
      $this->utils->set_cookie('wpbb_anonymous_play_key', $nonce);

      update_post_meta($saved->id, 'wpbb_anonymous_play_key', $nonce);
    }
    $serialized = $this->serializer->serialize($saved);
    $serialized = $this->paid_tournament_service->filter_play_created_response_data(
      $serialized
    );
    return new WP_REST_Response($serialized, 201);
  }

  public function generate_images($request): WP_Error|WP_REST_Response {
    $params = $request->get_params();
    $play_id = $params['item_id'];
    $play = $this->play_repo->get($play_id);
    if (!$play) {
      return new WP_Error(
        'not-found',
        'The requested play could not be found.',
        ['status' => 404]
      );
    }
    if (!current_user_can('wpbb_play_bracket', $play->bracket_id)) {
      return new WP_Error(
        'unauthorized',
        'You are not authorized to play this bracket.',
        ['status' => 403]
      );
    }
    if (!$this->product_integration->has_all_configs()) {
      $this->product_integration->generate_images($play);
    }
    $serialized = $this->serializer->serialize($play);
    return new WP_REST_Response($serialized, 201);
  }

  /**
   * Check if a given request has customer access to this plugin. Anyone can view the data.
   *
   * @param WP_REST_Request $request Full details about the request.
   *
   * @return WP_Error|bool
   */
  public function customer_permission_check(
    WP_REST_Request $request
  ): WP_Error|bool {
    return current_user_can('read');
  }

  public function create_play_permission_check(
    WP_REST_Request $request
  ): WP_Error|bool {
    $params = $request->get_params();
    $buster_play = isset($params['busted_id']) && $params['busted_id'] !== null;
    $bracket_id = $params['bracket_id'];
    if (!current_user_can('wpbb_play_bracket', $bracket_id)) {
      return new WP_Error(
        'unauthorized',
        'You are not authorized to play this bracket.',
        ['status' => 403]
      );
    }
    if ($buster_play) {
      $busted_play = $this->play_repo->get($params['busted_id']);
      if (!$busted_play->is_bustable) {
        return new WP_Error('unauthorized', 'This bracket cannot be busted.', [
          'status' => 403,
        ]);
      }
    }
    return true;
  }
}
