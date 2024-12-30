<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Push;

use WP_REST_Request;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class FCMTokenApiTest extends WPBB_UnitTestCase {
  const FCM_API_ENDPOINT = '/bmb/v1/fcm';
  private $user;
  private $valid_data;

  public function set_up(): void {
    parent::set_up();
    $this->user = $this->create_user();
    wp_set_current_user($this->user->ID);

    $this->valid_data = [
      'token' => 'fcm-token-123',
      'device_id' => 'device-123',
      'platform' => 'ios',
      'device_name' => 'iPhone 12',
      'app_version' => '1.0.0',
    ];
  }

  public function test_register_token(): void {
    $request = new WP_REST_Request(
      'POST',
      self::FCM_API_ENDPOINT . '/register'
    );
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

  public function test_register_token_missing_required_fields(): void {
    $invalid_data = array_diff_key($this->valid_data, ['token' => '']);

    $request = new WP_REST_Request(
      'POST',
      self::FCM_API_ENDPOINT . '/register'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($invalid_data));

    $response = rest_do_request($request);
    $this->assertSame(400, $response->get_status());
  }

  public function test_update_token(): void {
    // First register a token
    $register_request = new WP_REST_Request(
      'POST',
      self::FCM_API_ENDPOINT . '/register'
    );
    $register_request->set_header('Content-Type', 'application/json');
    $register_request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $register_request->set_body(wp_json_encode($this->valid_data));
    $register_response = rest_do_request($register_request);

    // Then update it
    $update_data = [
      'old_token' => 'fcm-token-123',
      'new_token' => 'new-fcm-token',
      'device_id' => 'device-123',
    ];

    $request = new WP_REST_Request('PUT', self::FCM_API_ENDPOINT . '/update');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($update_data));

    $response = rest_do_request($request);
    $data = $response->get_data();

    $this->assertSame(200, $response->get_status());
    $this->assertEquals('new-fcm-token', $data['token']);
  }

  public function test_deregister_token(): void {
    // First register a token
    $register_request = new WP_REST_Request(
      'POST',
      self::FCM_API_ENDPOINT . '/register'
    );
    $register_request->set_header('Content-Type', 'application/json');
    $register_request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $register_request->set_body(wp_json_encode($this->valid_data));
    rest_do_request($register_request);

    // Then deregister it
    $request = new WP_REST_Request(
      'DELETE',
      self::FCM_API_ENDPOINT . '/deregister'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode(['device_id' => $this->valid_data['device_id']])
    );

    $response = rest_do_request($request);
    $this->assertSame(200, $response->get_status());
  }

  public function test_update_status(): void {
    // First register a token
    $register_request = new WP_REST_Request(
      'POST',
      self::FCM_API_ENDPOINT . '/register'
    );
    $register_request->set_header('Content-Type', 'application/json');
    $register_request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $register_request->set_body(wp_json_encode($this->valid_data));
    rest_do_request($register_request);

    // Then update its status
    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/status');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'device_id' => $this->valid_data['device_id'],
        'status' => 'active',
      ])
    );

    $response = rest_do_request($request);
    $this->assertSame(200, $response->get_status());
  }

  public function test_unauthorized_access(): void {
    wp_set_current_user(0); // Set as logged out user

    $request = new WP_REST_Request(
      'POST',
      self::FCM_API_ENDPOINT . '/register'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_body(wp_json_encode($this->valid_data));

    $response = rest_do_request($request);
    $this->assertSame(401, $response->get_status());
  }

  public function test_invalid_platform(): void {
    $invalid_data = array_merge($this->valid_data, ['platform' => 'windows']);

    $request = new WP_REST_Request(
      'POST',
      self::FCM_API_ENDPOINT . '/register'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($invalid_data));

    $response = rest_do_request($request);
    $this->assertSame(400, $response->get_status());
  }

  public function test_update_nonexistent_token(): void {
    $update_data = [
      'old_token' => 'nonexistent-token',
      'new_token' => 'new-token',
      'device_id' => 'device-123',
    ];

    $request = new WP_REST_Request('PUT', self::FCM_API_ENDPOINT . '/update');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($update_data));

    $response = rest_do_request($request);
    $this->assertSame(404, $response->get_status());
  }

  public function test_deregister_nonexistent_device(): void {
    $request = new WP_REST_Request(
      'DELETE',
      self::FCM_API_ENDPOINT . '/deregister'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode(['device_id' => 'nonexistent-device']));

    $response = rest_do_request($request);
    $this->assertSame(404, $response->get_status());
  }

  public function test_update_status_nonexistent_device(): void {
    $request = new WP_REST_Request('POST', self::FCM_API_ENDPOINT . '/status');
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'device_id' => 'nonexistent-device',
        'status' => 'active',
      ])
    );

    $response = rest_do_request($request);
    $this->assertSame(404, $response->get_status());
  }

  public function test_cannot_access_other_users_token(): void {
    // First user registers a token
    $first_user = $this->create_user();
    wp_set_current_user($first_user->ID);

    $register_request = new WP_REST_Request(
      'POST',
      self::FCM_API_ENDPOINT . '/register'
    );
    $register_request->set_header('Content-Type', 'application/json');
    $register_request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $register_request->set_body(wp_json_encode($this->valid_data));
    rest_do_request($register_request);

    // Switch to second user
    $second_user = $this->create_user();
    wp_set_current_user($second_user->ID);

    // Try to update first user's token
    $update_request = new WP_REST_Request(
      'PUT',
      self::FCM_API_ENDPOINT . '/update'
    );
    $update_request->set_header('Content-Type', 'application/json');
    $update_request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $update_request->set_body(
      wp_json_encode([
        'old_token' => $this->valid_data['token'],
        'new_token' => 'new-token',
        'device_id' => $this->valid_data['device_id'],
      ])
    );
    $update_response = rest_do_request($update_request);
    $this->assertSame(404, $update_response->get_status());

    // Try to deregister first user's device
    $deregister_request = new WP_REST_Request(
      'DELETE',
      self::FCM_API_ENDPOINT . '/deregister'
    );
    $deregister_request->set_header('Content-Type', 'application/json');
    $deregister_request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $deregister_request->set_body(
      wp_json_encode(['device_id' => $this->valid_data['device_id']])
    );
    $deregister_response = rest_do_request($deregister_request);
    $this->assertSame(404, $deregister_response->get_status());

    // Try to update status of first user's device
    $status_request = new WP_REST_Request(
      'POST',
      self::FCM_API_ENDPOINT . '/status'
    );
    $status_request->set_header('Content-Type', 'application/json');
    $status_request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $status_request->set_body(
      wp_json_encode([
        'device_id' => $this->valid_data['device_id'],
        'status' => 'active',
      ])
    );
    $status_response = rest_do_request($status_request);
    $this->assertSame(404, $status_response->get_status());

    // Verify first user can still access their token
    wp_set_current_user($first_user->ID);
    $status_request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $status_response = rest_do_request($status_request);
    $this->assertSame(200, $status_response->get_status());
  }
}
