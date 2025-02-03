<?php

namespace WStrategies\BMB\tests\integration;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Base test case class for REST API tests.
 * Provides helper methods for making REST API requests.
 */
abstract class RestApiTestCase extends WPBB_UnitTestCase {
  /**
   * Make a REST API request with JSON data.
   *
   * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
   * @param string $endpoint REST API endpoint
   * @param array $data Request data to be JSON encoded
   * @param array $query_params Optional query parameters
   * @return WP_REST_Response The response
   */
  protected function make_json_request(
    string $method,
    string $endpoint,
    array $data = [],
    array $query_params = []
  ): WP_REST_Response {
    $request = new WP_REST_Request($method, $endpoint);
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    if (!empty($data)) {
      $request->set_body(wp_json_encode($data));
    }

    if (!empty($query_params)) {
      $request->set_query_params($query_params);
    }

    return rest_do_request($request);
  }

  /**
   * Make a GET request.
   */
  protected function get(
    string $endpoint,
    array $query_params = []
  ): WP_REST_Response {
    return $this->make_json_request('GET', $endpoint, [], $query_params);
  }

  /**
   * Make a POST request.
   */
  protected function post(
    string $endpoint,
    array $data = []
  ): WP_REST_Response {
    return $this->make_json_request('POST', $endpoint, $data);
  }

  /**
   * Make a PUT request.
   */
  protected function put(string $endpoint, array $data = []): WP_REST_Response {
    return $this->make_json_request('PUT', $endpoint, $data);
  }

  /**
   * Make a DELETE request.
   */
  protected function delete(
    string $endpoint,
    array $data = []
  ): WP_REST_Response {
    return $this->make_json_request('DELETE', $endpoint, $data);
  }

  /**
   * Assert that the response has a specific status code.
   */
  protected function assertResponseStatus(
    int $expected_status,
    WP_REST_Response $response,
    string $message = ''
  ): void {
    $this->assertSame(
      $expected_status,
      $response->get_status(),
      $message ?:
      "Expected response status {$expected_status} but got {$response->get_status()}"
    );
  }

  /**
   * Assert that the response is successful (2xx status code).
   */
  protected function assertResponseIsSuccessful(
    WP_REST_Response $response,
    string $message = ''
  ): void {
    $status = $response->get_status();
    $this->assertGreaterThanOrEqual(
      200,
      $status,
      $message ?: "Response status {$status} is not successful"
    );
    $this->assertLessThan(
      300,
      $status,
      $message ?: "Response status {$status} is not successful"
    );
  }

  /**
   * Assert that the response has validation errors.
   */
  protected function assertResponseHasValidationErrors(
    WP_REST_Response $response,
    array $fields = [],
    string $message = ''
  ): void {
    $this->assertResponseStatus(400, $response, $message);
    $data = $response->get_data();

    $this->assertArrayHasKey('code', $data, 'Response should have error code');
    $this->assertArrayHasKey(
      'message',
      $data,
      'Response should have error message'
    );

    if (!empty($fields)) {
      $this->assertArrayHasKey(
        'data',
        $data,
        'Response should have error data'
      );
      $this->assertArrayHasKey(
        'params',
        $data['data'],
        'Response should have invalid parameters'
      );

      foreach ($fields as $field) {
        $this->assertArrayHasKey(
          $field,
          $data['data']['params'],
          "Response should have validation error for field '{$field}'"
        );
      }
    }
  }
}
