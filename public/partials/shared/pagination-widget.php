<?php

function my_pagination_component($num_pages) {    

    // Get the current page number
    if (isset($_GET['page'])) {
        // If the page query param is set, use this
        $page = intval($_GET['paged']);
    } else {
        // But the page query param isn't set for me,
        // either because I'm using Post Name Permalinks
        // or because of the rewrite rules, so this
        // workaround gets the page number from the uri.
        $uri = $_SERVER['REQUEST_URI'];
        if (substr($uri,-1) == '/') {
            $uri = rtrim($uri, '/');
        }
        $parts = explode('/', $uri);
        $page = max(1,intval(end($parts)));
    }

    $big = 999999999; // an unlikely page number
    echo paginate_links(array(
        'base' => str_replace($big, '%#%', esc_url( get_pagenum_link($big) )),
        'format' => '?paged=#%#',
        'current' => $page,
        'total' => $num_pages,
    ));
}