<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Wp_Bracket_Builder
 */

$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
  $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
DG\BypassFinals::enable();

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH');
if (false !== $_phpunit_polyfills_path) {
  define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path);
}

if (!file_exists("{$_tests_dir}/includes/functions.php")) {
  echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" .
    PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  exit(1);
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin(): void {
  require dirname(__FILE__, 3) . '/wp-bracket-builder.php';
}

tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
require_once __DIR__ . '/unittest-base.php';
require_once dirname(__DIR__, 2) .
  '/vendor/wpackagist-plugin/woocommerce/woocommerce.php';
