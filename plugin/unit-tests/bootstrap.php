<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Wp_Bracket_Builder
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
define('WPBB_PLUGIN_DIR', dirname(__DIR__) . '/');

WP_Mock::bootstrap();
