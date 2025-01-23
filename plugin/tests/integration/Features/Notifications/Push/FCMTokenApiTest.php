<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Push;

use WP_REST_Request;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class FCMTokenApiTest extends WPBB_UnitTestCase {
  const FCM_API_ENDPOINT = '/bmb/v1/fcm/token';
  private $user;
  private $valid_data;

  public function set_up(): void {
    parent::set_up();
    $this->user = $this->create_user();
    wp_set_current_user($this->user->ID);
    global $wpdb;
    $wpdb->suppress_errors(true);

    $this->valid_data = [
      'token' => 'fcm-token-123',
      'device_id' => 'device-123',
      'platform' => 'ios',
      'device_name' => 'iPhone 12',
      'app_version' => '1.0.0',
    ];
  }

  public function tear_down(): void {
    global $wpdb;
    $wpdb->suppress_errors(false);
    parent::tear_down();
  }

  public function test_sync_new_token(): void {
    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/sync');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($this->valid_data));

    $response = rest_do_request($request);
    $data = $response->get_data();

    $this->assertSame(201, $response->get_status());
    $this->assertIsInt($data['id']);
    $this->assertEquals($this->user->ID, $data['user_id']);
    $this->assertEquals('device-123', $data['device_id']);
    $this->assertEquals('fcm-token-123', $data['token']);
    $this->assertEquals('ios', $data['device_type']);
    $this->assertEquals('iPhone 12', $data['device_name']);
    $this->assertEquals('1.0.0', $data['app_version']);
  }

  public function test_sync_update_existing_token(): void {
    // First sync a token
    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/sync');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($this->valid_data));
    rest_do_request($request);

    // Then update it
    $update_data = array_merge($this->valid_data, [
      'token' => 'new-fcm-token',
      'app_version' => '2.0.0',
    ]);

    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/sync');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($update_data));

    $response = rest_do_request($request);
    $data = $response->get_data();

    $this->assertSame(200, $response->get_status());
    $this->assertEquals('new-fcm-token', $data['token']);
    $this->assertEquals('2.0.0', $data['app_version']);
  }

  public function test_sync_missing_required_fields(): void {
    $invalid_data = array_diff_key($this->valid_data, ['token' => '']);

    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/sync');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($invalid_data));

    $response = rest_do_request($request);
    $this->assertSame(400, $response->get_status());
  }

  public function test_delete_token(): void {
    // First sync a token
    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/sync');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($this->valid_data));
    rest_do_request($request);

    // Then delete it
    $request = new WP_REST_Request('DELETE', self::FCM_API_ENDPOINT);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode(['device_id' => $this->valid_data['device_id']])
    );

    $response = rest_do_request($request);
    $this->assertSame(200, $response->get_status());
  }

  public function test_unauthorized_access(): void {
    wp_set_current_user(0); // Set as logged out user

    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/sync');
    $request->set_header('Content-Type', 'application/json');
    $request->set_body(wp_json_encode($this->valid_data));

    $response = rest_do_request($request);
    $this->assertSame(401, $response->get_status());
  }

  public function test_invalid_platform(): void {
    $invalid_data = array_merge($this->valid_data, ['platform' => 'windows']);

    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/sync');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($invalid_data));

    $response = rest_do_request($request);
    $this->assertSame(400, $response->get_status());
  }

  public function test_delete_nonexistent_token(): void {
    $request = new WP_REST_Request('DELETE', self::FCM_API_ENDPOINT);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode(['device_id' => 'nonexistent-device']));

    $response = rest_do_request($request);
    $this->assertSame(404, $response->get_status());
  }

  public function test_cannot_access_other_users_token(): void {
    // First user syncs a token
    $first_user = $this->create_user();
    wp_set_current_user($first_user->ID);

    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/sync');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($this->valid_data));
    rest_do_request($request);

    // Switch to second user
    $second_user = $this->create_user();
    wp_set_current_user($second_user->ID);

    // Try to sync with first user's device ID
    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/sync');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($this->valid_data));
    $response = rest_do_request($request);
    $this->assertSame(500, $response->get_status()); // Duplicate device_id error

    // Try to delete first user's token
    $request = new WP_REST_Request('DELETE', self::FCM_API_ENDPOINT);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode(['device_id' => $this->valid_data['device_id']])
    );
    $response = rest_do_request($request);
    $this->assertSame(404, $response->get_status());

    // Verify first user can still access their token
    wp_set_current_user($first_user->ID);
    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/sync');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($this->valid_data));
    $response = rest_do_request($request);
    $this->assertSame(200, $response->get_status());
  }
}
