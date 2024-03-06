<?php

/**
 * PHPUnit bootstrap file.
 *
 * @package Wp_Bracket_Builder
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
define('WPBB_PLUGIN_DIR', dirname(__DIR__, 2) . '/');
require_once WPBB_PLUGIN_DIR . 'tests/integration/mock/StripeMock.php';

WP_Mock::bootstrap();
