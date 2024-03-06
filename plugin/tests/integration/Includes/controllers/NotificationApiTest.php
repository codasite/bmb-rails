<?php
namespace WStrategies\BMB\tests\integration\Includes\controllers;

use WP_REST_Request;
use WStrategies\BMB\Includes\Domain\NotificationType;
use WStrategies\BMB\tests\integration\Traits\SetupAdminUser;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class NotificationApiTest extends WPBB_UnitTestCase {
  use SetupAdminUser;
  const NOTIFICATION_API_ENDPOINT = '/wp-bracket-builder/v1/notifications';
  public function test_get_notification() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post();
    $notification = self::factory()->notification->create_object([
      'user_id' => $user->ID,
      'post_id' => $post->ID,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $request = new WP_REST_Request(
      'GET',
      self::NOTIFICATION_API_ENDPOINT . '/' . $notification->id
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $data = $response->get_data();
    $this->assertSame(200, $response->get_status());
    $this->assertSame($notification->id, $data->id);
    $this->assertSame($user->ID, $data->user_id);
    $this->assertSame($post->ID, $data->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $data->notification_type
    );
  }

  public function test_get_notification_not_found() {
    $request = new WP_REST_Request(
      'GET',
      self::NOTIFICATION_API_ENDPOINT . '/1'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $response = rest_do_request($request);
    $this->assertSame(404, $response->get_status());
  }

  public function test_create_notification() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post();
    $request = new WP_REST_Request('POST', self::NOTIFICATION_API_ENDPOINT);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'user_id' => $user->ID,
        'post_id' => $post->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $response = rest_do_request($request);
    $data = $response->get_data();
    $this->assertSame(201, $response->get_status());
    $this->assertIsInt($data->id);
    $this->assertSame($user->ID, $data->user_id);
    $this->assertSame($post->ID, $data->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $data->notification_type
    );
  }

  public function test_create_notification_invalid_type() {
    $request = new WP_REST_Request('POST', self::NOTIFICATION_API_ENDPOINT);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'user_id' => 1,
        'post_id' => 1,
        'notification_type' => 'invalid',
      ])
    );
    $response = rest_do_request($request);
    $this->assertSame(400, $response->get_status());
  }

  public function test_create_notification_nonexistent_user() {
    $post = $this->create_post();
    $request = new WP_REST_Request('POST', self::NOTIFICATION_API_ENDPOINT);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'user_id' => 99999999,
        'post_id' => $post->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $response = rest_do_request($request);
    $this->assertSame(400, $response->get_status());
  }

  public function test_create_notification_nonexistant_post() {
    $user = self::factory()->user->create_and_get();
    $request = new WP_REST_Request('POST', self::NOTIFICATION_API_ENDPOINT);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'user_id' => $user->ID,
        'post_id' => 99999999,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $response = rest_do_request($request);
    $this->assertSame(400, $response->get_status());
  }

  public function test_create_notification_no_user() {
    $post = $this->create_post();
    $request = new WP_REST_Request('POST', self::NOTIFICATION_API_ENDPOINT);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'post_id' => $post->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $response = rest_do_request($request);
    $data = $response->get_data();
    $this->assertSame(201, $response->get_status());
    $this->assertIsInt($data->id);
    $this->assertSame(get_current_user_id(), $data->user_id);
    $this->assertSame($post->ID, $data->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $data->notification_type
    );
  }

  public function test_owner_can_delete_notification() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post();
    $notification = self::factory()->notification->create_object([
      'user_id' => $user->ID,
      'post_id' => $post->ID,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $request = new WP_REST_Request(
      'DELETE',
      self::NOTIFICATION_API_ENDPOINT . '/' . $notification->id
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    wp_set_current_user($user->ID);
    $response = rest_do_request($request);
    $this->assertSame(200, $response->get_status());
  }

  public function test_non_owner_cannot_delete_notification() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post();
    $notification = self::factory()->notification->create_object([
      'user_id' => $user->ID,
      'post_id' => $post->ID,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $request = new WP_REST_Request(
      'DELETE',
      self::NOTIFICATION_API_ENDPOINT . '/' . $notification->id
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    wp_set_current_user(self::factory()->user->create());
    $response = rest_do_request($request);
    $this->assertSame(403, $response->get_status());
  }
}
