<?php

function wpbb_pagination($page, $num_pages, $justify = 'start') {
  // Don't print empty markup if there's only one page.
  if ($num_pages <= 1) {
    return;
  }
  $big = 999999999; // an unlikely page number
  $links =  paginate_links(array(
    'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
    // 'format' => '?paged=#%#',
    'format' => '?paged=%#%',
    'type' => 'array',
    'current' => $page,
    'total' => $num_pages,
    'prev_text' => '<div class="tw-flex tw-items-center">' . file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/chevron_left.svg') . '</div>',
    'next_text' => '<div class="tw-flex tw-items-center">' . file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/chevron_right.svg') . '</div>',
  ));
?>
  <div class="tw-flex tw-items-center tw-justify-<?php echo $justify; ?> tw-gap-10 tw-py-14 tw-px-12">
    <?php foreach ($links as $link) :
      $current = false;
      if (strpos($link, 'current') !== false) {
        $current = true;
      } ?>
      <div class="tw-flex tw-justify-center tw-items-center tw-px-4 tw-text-white tw-font-500 tw-text-16 tw-min-w-[24px] tw-min-h-[24px] tw-rounded-4<?php echo $current ? ' tw-bg-blue' : '' ?>">
        <?php echo $link; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php
}
