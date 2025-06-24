<?php

namespace WStrategies\BMB\Includes\Service\TournamentFilter\Public;

use WP_Query;
use WStrategies\BMB\Features\Bracket\Domain\BracketQueryTypes;
use WStrategies\BMB\Features\Bracket\Infrastructure\BracketQueryBuilder;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;

class PublicBracketsQuery {
  private BracketRepo $bracket_repo;
  private BracketQueryBuilder $query_builder;
  private array $bracket_counts = [];

  public function __construct($args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->query_builder = $args['query_builder'] ?? new BracketQueryBuilder();
  }

  public function status_is_valid(string $status): bool {
    $allowed_filters = [
      BracketQueryTypes::FILTER_LIVE,
      BracketQueryTypes::FILTER_UPCOMING,
      BracketQueryTypes::FILTER_COMPLETED,
    ];

    return in_array($status, $allowed_filters);
  }

  public function get_brackets(
    int $paged,
    int $per_page,
    string $status,
    array $exclude_tags = []
  ): array {
    if (!$this->status_is_valid($status)) {
      return [];
    }

    $query_args = $this->query_builder->buildPublicBracketsQuery([
      'paged' => $paged,
      'posts_per_page' => $per_page,
      'paged_status' => $status,
      'exclude_tags' => $exclude_tags,
    ]);

    $the_query = new WP_Query($query_args);
    return $this->bracket_repo->get_all($the_query);
  }

  public function get_brackets_count(
    string $status,
    array $exclude_tags = []
  ): int {
    if (!$this->status_is_valid($status)) {
      return 0;
    }

    if (isset($this->bracket_counts[$status])) {
      return $this->bracket_counts[$status];
    }

    $query_args = $this->query_builder->buildPublicBracketsQuery([
      'paged' => 1,
      'posts_per_page' => 1,
      'paged_status' => $status,
      'exclude_tags' => $exclude_tags,
    ]);

    $the_query = new WP_Query($query_args);
    $count = $the_query->found_posts;
    $this->bracket_counts[$status] = $count;

    return $count;
  }

  public function get_max_num_pages(
    int $per_page,
    string $status,
    array $exclude_tags = []
  ): int {
    $count = $this->get_brackets_count($status, $exclude_tags);
    return ceil($count / $per_page);
  }

  public function has_brackets(string $status, array $exclude_tags = []): bool {
    $count = $this->get_brackets_count($status, $exclude_tags);
    return $count > 0;
  }
}
