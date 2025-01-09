<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;

// Create new coverage object
$filter = new Filter();
$coverage = new CodeCoverage(
  (new Selector())->forLineCoverage($filter),
  $filter
);

// Load unit test coverage
if (file_exists('/var/www/html/coverage-data/unit.cov')) {
  $unit = include '/var/www/html/coverage-data/unit.cov';
  $coverage->merge($unit);
}

// Load integration test coverage
if (file_exists('/var/www/html/coverage-data/integration.cov')) {
  $integration = include '/var/www/html/coverage-data/integration.cov';
  $coverage->merge($integration);
}

// Generate HTML report
$writer = new HtmlReport();
$writer->process($coverage, '/var/www/html/coverage');
