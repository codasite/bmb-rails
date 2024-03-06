<?php

namespace WStrategies\BMB\Public\Partials\shared;

class PaginationWidget {
  public static function pagination($page, $num_pages): void {
    // Don't print empty markup if there's only one page.
    if ($num_pages <= 1) {
      return;
    }
    $links = paginate_links([
      'type' => 'array',
      'current' => $page,
      'total' => $num_pages,
      'prev_text' =>
        '<div class="tw-flex tw-items-center">' .
        file_get_contents(
          WPBB_PLUGIN_DIR . 'Public/assets/icons/chevron_left.svg'
        ) .
        '</div>',
      'next_text' =>
        '<div class="tw-flex tw-items-center">' .
        file_get_contents(
          WPBB_PLUGIN_DIR . 'Public/assets/icons/chevron_right.svg'
        ) .
        '</div>',
    ]);
    ?>
    <div class="tw-flex tw-items-center tw-justify-start tw-gap-10 tw-py-14 tw-px-12">
      <?php foreach ($links as $link):

        $current = false;
        if (strpos($link, 'current') !== false) {
          $current = true;
        }
        ?>
        <div
          class="tw-flex tw-justify-center tw-items-center tw-px-4 [&_a]:tw-text-white tw-font-500 tw-text-16 tw-min-w-[24px] tw-min-h-[24px] tw-rounded-4<?php echo $current
            ? ' tw-bg-blue'
            : ''; ?>">
          <?php echo $link; ?>
        </div>
      <?php
      endforeach; ?>
    </div>
    <?php
  }
}
