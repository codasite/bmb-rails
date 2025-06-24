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
    return in_array($status, [
      BracketQueryTypes::FILTER_LIVE,
      BracketQueryTypes::FILTER_UPCOMING,
      BracketQueryTypes::FILTER_SCORED,
      BracketQueryTypes::FILTER_IN_PROGRESS,
      BracketQueryTypes::FILTER_COMPLETED,
      BracketQueryTypes::FILTER_ALL,
    ]);
  }

  public function get_brackets(
    int $paged,
    int $per_page,
    string $status
  ): array {
    if (!$this->status_is_valid($status)) {
      return [];
    }

    $query_args = $this->query_builder->buildPublicBracketsQuery([
      'paged' => $paged,
      'posts_per_page' => $per_page,
      'status' => $status,
    ]);

    $the_query = new WP_Query($query_args);
    return $this->bracket_repo->get_all($the_query);
  }

  public function get_brackets_count(string $status): int {
    if (!$this->status_is_valid($status)) {
      return 0;
    }

    if (isset($this->bracket_counts[$status])) {
      return $this->bracket_counts[$status];
    }

    $query_args = $this->query_builder->buildPublicBracketsQuery([
      'paged' => 1,
      'posts_per_page' => 1,
      'status' => $status,
    ]);

    $the_query = new WP_Query($query_args);
    $count = $the_query->found_posts;
    $this->bracket_counts[$status] = $count;

    return $count;
  }

  public function get_max_num_pages(int $per_page, string $status): int {
    $count = $this->get_brackets_count($status);
    return ceil($count / $per_page);
  }

  public function has_brackets(string $status): bool {
    $count = $this->get_brackets_count($status);
    return $count > 0;
  }
}
