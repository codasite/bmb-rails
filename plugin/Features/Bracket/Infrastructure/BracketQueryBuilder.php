<?php

namespace WStrategies\BMB\Features\Bracket\Infrastructure;

use WStrategies\BMB\Features\Bracket\Domain\BracketQueryTypes;
use WStrategies\BMB\Features\MobileApp\MobileAppMetaQuery;
use WStrategies\BMB\Features\MobileApp\RequestService;
use WStrategies\BMB\Includes\Domain\Bracket;

/**
 * Builds WP_Query arguments for bracket queries
 */
class BracketQueryBuilder {
  private RequestService $request_service;

  public function __construct(RequestService $request_service = null) {
    $this->request_service = $request_service ?? new RequestService();
  }

  /**
   * Build query arguments for public brackets
   *
   * @param array $opts {
   *     Optional. Array of query parameters.
   *     @type array  $tags           Array of tag slugs to filter by
   *     @type int    $author         Author ID to filter by
   *     @type int    $posts_per_page Number of posts per page (default 10)
   *     @type int    $paged          Current page number
   *     @type string $status         Status filter (live, upcoming, scored, all)
   * }
   * @return array WP_Query arguments
   */
  public function buildPublicBracketsQuery(array $opts = []): array {
    $tags = $opts['tags'] ?? [];
    $author_id = $opts['author'] ?? null;
    $posts_per_page = $opts['posts_per_page'] ?? 10;
    $paged = $opts['paged'] ?? 1;
    $status_filter = $opts['status'] ?? BracketQueryTypes::FILTER_LIVE;

    $query_args = [
      'post_type' => Bracket::get_post_type(),
      'tag_slug__and' => $tags,
      'posts_per_page' => $posts_per_page,
      'paged' => $paged,
      'post_status' => BracketQueryTypes::getStatusQuery($status_filter),
      'order' => 'DESC',
    ];

    if ($author_id) {
      $query_args['author'] = $author_id;
    }

    if ($this->request_service->is_mobile_app_request()) {
      $query_args['meta_query'] = MobileAppMetaQuery::get_mobile_meta_query();
    }

    return $query_args;
  }
}
