<?php
namespace WStrategies\BMB\Features\Bracket\Presentation\Html;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WStrategies\BMB\Includes\Controllers\HtmlFragmentApiBase;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Features\Bracket\Infrastructure\BracketQueryBuilder;
use WStrategies\BMB\Features\Bracket\Domain\BracketQueryTypes;
use WStrategies\BMB\Features\Bracket\Presentation\BracketListRenderer;
use WStrategies\BMB\Features\MobileApp\RequestService;

/**
 * HTML Fragment endpoint for bracket list pagination.
 * Returns rendered HTML fragments for infinite scroll implementation.
 */
class BracketHtmlApi extends HtmlFragmentApiBase {
  private BracketRepo $bracket_repo;
  private BracketQueryBuilder $query_builder;
  private BracketListRenderer $list_renderer;

  protected $rest_base = 'bracket-list-html';

  public function __construct(array $args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->query_builder = $args['query_builder'] ?? new BracketQueryBuilder();
    $this->list_renderer = $args['list_renderer'] ?? new BracketListRenderer();
  }

  /**
   * Get bracket list HTML fragments.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request): WP_Error|WP_REST_Response {
    // Build query args from request parameters
    $query_args = $this->query_builder->buildPublicBracketsQuery([
      'paged' => $request->get_param('page') ?: 1,
      'posts_per_page' => $request->get_param('per_page') ?: 10,
      'status' =>
        $request->get_param('status') ?: BracketQueryTypes::FILTER_LIVE,
      'tags' => $request->get_param('tags') ?: [],
      'author' => $request->get_param('author'),
    ]);

    // Get brackets using repository
    $the_query = new \WP_Query($query_args);
    $brackets = $this->bracket_repo->get_all($the_query);

    // Render HTML and format response
    $html = $this->list_renderer->renderBracketList($brackets);
    $pagination = [
      'current_page' => $query_args['paged'],
      'total_pages' => $the_query->max_num_pages,
      'total_items' => $the_query->found_posts,
      'per_page' => $query_args['posts_per_page'],
      'has_more' => $query_args['paged'] < $the_query->max_num_pages,
    ];

    return $this->format_html_response($html, $pagination);
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

    $params['author'] = [
      'description' => 'Filter by bracket author ID.',
      'type' => 'integer',
    ];

    $params['status'] = [
      'description' =>
        'Filter by bracket status (live, upcoming, scored, all).',
      'type' => 'string',
      'default' => 'live',
    ];

    return $params;
  }
}
