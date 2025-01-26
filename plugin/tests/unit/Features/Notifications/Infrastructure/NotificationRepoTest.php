<?php namespace WStrategies\BMB\tests\unit\Features\Notifications\Infrastructure;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationRepo;
use WStrategies\BMB\Includes\Repository\TableSqlGenerator;

class NotificationRepoTest extends TestCase {
  private NotificationRepo $repo;
  private $wpdb;

  public function setUp(): void {
    parent::setUp();

    $this->wpdb = $this->getMockBuilder('wpdb')
      ->disableOriginalConstructor()
      ->getMock();
    $this->wpdb->users = 'wp_users';

    $this->repo = new NotificationRepo(['wpdb' => $this->wpdb]);

    if (!defined('WPBB_DB_PREFIX')) {
      define('WPBB_DB_PREFIX', 'bmb_');
    }
  }

  public function test_generated_table_sql_is_correct(): void {
    $fields = $this->repo->get_field_definitions();
    $sql = TableSqlGenerator::generate_table_sql($fields);

    $expected_sql =
      "id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,\n" .
      "  user_id bigint UNSIGNED NOT NULL,\n" .
      "  title varchar(255) NOT NULL,\n" .
      "  message text NOT NULL,\n" .
      "  timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" .
      "  is_read tinyint(1) NOT NULL DEFAULT 0,\n" .
      "  link varchar(255),\n" .
      "  notification_type varchar(50) NOT NULL,\n" .
      "  KEY user_id (user_id),\n" .
      "  KEY timestamp (timestamp),\n" .
      "  KEY notification_type (notification_type),\n" .
      '  FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE';

    $this->assertEquals($expected_sql, $sql);
  }

  public function tearDown(): void {
    parent::tearDown();
  }
}
