<?php
$view = get_query_var('view');
switch ($view) {
    case 'play':
        echo '<div id="wpbb-play-template"></div>';
        break;
    case 'copy':
        echo '<div id="wpbb-template-builder"></div>';
        break;
    default:
        echo '<div id="wpbb-play-template"></div>';
        break;
}
