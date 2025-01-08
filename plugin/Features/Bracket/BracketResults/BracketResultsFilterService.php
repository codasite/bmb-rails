<?php

namespace WStrategies\BMB\Features\Bracket\BracketResults;

use DateTimeImmutable;
use InvalidArgumentException;
use WpOrg\Requests\Exception\InvalidArgument;
use WStrategies\BMB\Includes\Domain\Pick;

class BracketResultsFilterService {
  /**
   * @param array<Pick> $results
   */
  public function filter_results_updated_at_time(
    $results,
    DateTimeImmutable $results_sent_at
  ) {
    return array_filter($results, function (Pick $result) use (
      $results_sent_at
    ) {
      if (!$result->get_updated_at()) {
        throw new InvalidArgumentException(
          'result updated_at must be a DateTimeImmutable, got: ' .
            gettype($result->get_updated_at())
        );
      }
      return $result->get_updated_at() > $results_sent_at;
    });
  }
}
