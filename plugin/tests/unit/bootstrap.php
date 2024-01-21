<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Wp_Bracket_Builder
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
define('WPBB_PLUGIN_DIR', dirname(__DIR__, 2) . '/');

WP_Mock::bootstrap();
