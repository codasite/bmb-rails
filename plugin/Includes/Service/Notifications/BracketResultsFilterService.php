<?php

namespace WStrategies\BMB\Includes\Service\Notifications;

class BracketResultsFilterService {
  public function filter_results_updated_at_time($results, $results_sent_at) {
    return array_filter($results, function ($result) use ($results_sent_at) {
      return $result->get_updated_at() > $results_sent_at;
    });
  }
}
