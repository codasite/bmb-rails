<?php

/**
 * PHPUnit bootstrap file for both unit and integration tests.
 *
 * @package Wp_Bracket_Builder
 */

// Add path mapping for test output at the start
$mappedPathTo = getenv('REPO_ROOT');
define('DOING_TESTS', true);

if ($mappedPathTo) {
  class PathMappingPrinter extends
    Sempro\PHPUnitPrettyPrinter\PrettyPrinterForPhpUnit9 {
    public function write(string $buffer): void {
      $buffer = str_replace(
        '/var/www/html/wp-content/plugins/wp-bracket-builder',
        getenv('REPO_ROOT') . '/plugin',
        $buffer
      );

      parent::write($buffer);
    }
  }
} else {
  class PathMappingPrinter extends
    Sempro\PHPUnitPrettyPrinter\PrettyPrinterForPhpUnit9 {
  }
}

// Get test type from environment variable (unit or integration)
$test_type = getenv('TEST_TYPE') ?: 'integration';

// Always load composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Enable bypass finals for all test types
DG\BypassFinals::enable();

// Set up unit tests
if ($test_type === 'unit') {
  define('WPBB_PLUGIN_DIR', dirname(__DIR__) . '/');
  WP_Mock::bootstrap();
  return;
}

// Set up integration tests
if ($test_type === 'integration') {
  $_tests_dir = getenv('WP_TESTS_DIR');

  if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
  }

  // Forward custom PHPUnit Polyfills configuration
  $_phpunit_polyfills_path = getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH');
  if (false !== $_phpunit_polyfills_path) {
    define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path);
  }

  if (!file_exists("{$_tests_dir}/includes/functions.php")) {
    echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" .
      PHP_EOL;
    exit(1);
  }

  // Give access to tests_add_filter() function.
  require_once "{$_tests_dir}/includes/functions.php";

  /**
   * Manually load the plugin being tested.
   */
  function _manually_load_plugin(): void {
    require dirname(__DIR__) . '/wp-bracket-builder.php';
  }

  tests_add_filter('muplugins_loaded', '_manually_load_plugin');

  // Start up the WP testing environment.
  require "{$_tests_dir}/includes/bootstrap.php";
  require_once dirname(__DIR__) .
    '/vendor/wpackagist-plugin/woocommerce/woocommerce.php';
  return;
}

// If we get here, invalid test type
echo "Invalid TEST_TYPE environment variable. Must be 'unit' or 'integration'." .
  PHP_EOL;
exit(1);
