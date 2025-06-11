<?php

namespace WStrategies\BMB\Features\Bracket\Presentation;

use WP_Query;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Public\Partials\shared\BracketListItem;
use WStrategies\BMB\Public\Partials\shared\PaginationWidget;

/**
 * Handles rendering of bracket lists and related components
 */
class BracketListRenderer {
  /**
   * Render a list of brackets
   *
   * @param Bracket[] $brackets Array of bracket objects
   * @param array $pagination {
   *     Optional. Pagination data
   *     @type int $current_page Current page number
   *     @type int $num_pages    Total number of pages
   * }
   * @return string Rendered HTML
   */
  public function renderBracketList(
    array $brackets,
    array $pagination = []
  ): string {
    if (empty($brackets)) {
      return '<div class="tw-text-center tw-py-30 tw-text-white/50">No brackets found.</div>';
    }

    ob_start();

    // Render bracket list
    echo '<div class="tw-flex tw-flex-col tw-gap-15">';
    foreach ($brackets as $bracket) {
      echo BracketListItem::bracket_list_item($bracket);
    }
    echo '</div>';

    // Render pagination if provided
    if (!empty($pagination)) {
      PaginationWidget::pagination(
        $pagination['current_page'] ?? 1,
        $pagination['num_pages'] ?? 1
      );
    }

    return ob_get_clean();
  }
}
