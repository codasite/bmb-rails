<?php

function my_pagination_component($num_pages, $justify = 'start') {
    // Don't print empty markup if there's only one page.
    if ($num_pages <= 1) {
        return;
    }

    // Get the current page number
    if (isset($_GET['page'])) {
        // If the page query param is set, use this
        $page = intval($_GET['paged']);
    } else {
        // But the page query param isn't set for me,
        // either because I'm using Post Name Permalinks
        // or because of the rewrite rules, so this
        // workaround gets the page number from the uri.

        // Note: this will break if the page number is not
        // the final uri path segment.
        $uri = $_SERVER['REQUEST_URI'];
        if (substr($uri, -1) == '/') {
            $uri = rtrim($uri, '/');
        }
        $parts = explode('/', $uri);
        $page = max(1, intval(end($parts)));
    }

?>
    <div class="tw-flex tw-items-center tw-justify-<?php echo $justify; ?> tw-gap-10 tw-py-14 tw-px-12">
        <?php
        $big = 999999999; // an unlikely page number
        $links =  paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=#%#',
            'type' => 'array',
            'current' => $page,
            'total' => $num_pages,
            'prev_text' => '<div class="tw-flex tw-items-center">' . file_get_contents(plugins_url('../../assets/icons/chevron_left.svg', __FILE__)) . '</div>',
            'next_text' => '<div class="tw-flex tw-items-center">' . file_get_contents(plugins_url('../../assets/icons/chevron_right.svg', __FILE__)) . '</div>',
        ));

        foreach ($links as $link) {
            $current = false;
            if (strpos($link, 'current') !== false) {
                $current = true;
            }
        ?>
            <div class="tw-flex tw-justify-center tw-px-4 tw-text-white tw-font-500 tw-min-w-24 tw-rounded-4<?php echo $current ? ' tw-bg-blue' : '' ?>">
                <?php echo $link; ?>
            </div>
        <?php
        }

        ?>
    </div>
<?php
}
