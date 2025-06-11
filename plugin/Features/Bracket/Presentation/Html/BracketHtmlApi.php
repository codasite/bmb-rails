<?php
namespace WStrategies\BMB\Features\Bracket\Presentation\Html;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WStrategies\BMB\Includes\Controllers\HtmlFragmentApiBase;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Public\Partials\shared\BracketsCommon;
use WStrategies\BMB\Public\Partials\shared\BracketListItem;
use WStrategies\BMB\Public\Partials\shared\PartialsContants;
use WStrategies\BMB\Features\MobileApp\RequestService;
use WStrategies\BMB\Features\MobileApp\MobileAppMetaQuery;

/**
 * HTML Fragment endpoint for bracket list pagination.
 * Returns rendered HTML fragments for infinite scroll implementation.
 */
class BracketHtmlApi extends HtmlFragmentApiBase {
  private BracketRepo $bracket_repo;
  private RequestService $request_service;

  protected string $rest_base = 'bracket-list-html';

  public function __construct(array $args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->request_service = $args['request_service'] ?? new RequestService();
  }

  /**
   * Get bracket list HTML fragments.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request): WP_Error|WP_REST_Response {
    $page = $request->get_param('page') ?: 1;
    $per_page = $request->get_param('per_page') ?: 10;
    $status_filter =
      $request->get_param('status') ?: PartialsContants::LIVE_STATUS;
    $tags = $request->get_param('tags') ?: [];

    // Build query args similar to BracketsCommon::get_public_brackets
    $all_statuses = [
      'publish',
      'score',
      'complete',
      PartialsContants::UPCOMING_STATUS,
    ];
    $active_status = ['publish'];
    $scored_status = ['score', 'complete'];

    if ($status_filter === PartialsContants::LIVE_STATUS) {
      $status_query = $active_status;
    } elseif ($status_filter === PartialsContants::UPCOMING_STATUS) {
      $status_query = [PartialsContants::UPCOMING_STATUS];
    } elseif ($status_filter === 'scored') {
      $status_query = $scored_status;
    } else {
      $status_query = $all_statuses;
    }

    $query_args = [
      'post_type' => 'bracket',
      'tag_slug__and' => $tags,
      'posts_per_page' => $per_page,
      'paged' => $page,
      'post_status' => $status_query,
      'order' => 'DESC',
    ];

    // Add mobile app meta query if needed
    if ($this->request_service->is_mobile_app_request()) {
      $query_args['meta_query'] = MobileAppMetaQuery::get_mobile_meta_query();
    }

    $the_query = new \WP_Query($query_args);
    $brackets = $this->bracket_repo->get_all($the_query);

    // Render HTML for brackets
    $html = $this->render_bracket_list($brackets);

    // Prepare pagination data
    $pagination = [
      'current_page' => $page,
      'total_pages' => $the_query->max_num_pages,
      'total_items' => $the_query->found_posts,
      'per_page' => $per_page,
      'has_more' => $page < $the_query->max_num_pages,
    ];

    return $this->format_html_response($html, $pagination);
  }

  /**
   * Render the bracket list HTML.
   */
  private function render_bracket_list(array $brackets): string {
    if (empty($brackets)) {
      return '<div class="tw-text-center tw-py-30 tw-text-white/50">No brackets found.</div>';
    }

    ob_start();
    foreach ($brackets as $bracket) {
      echo BracketListItem::bracket_list_item($bracket);
    }
    return ob_get_clean();
  }

  /**
   * Override collection params to add bracket-specific filters.
   */
  public function get_collection_params(): array {
    $params = parent::get_collection_params();

    $params['tags'] = [
      'description' => 'Filter by bracket tags.',
      'type' => 'array',
      'items' => [
        'type' => 'string',
      ],
      'default' => [],
    ];

    return $params;
  }
}
