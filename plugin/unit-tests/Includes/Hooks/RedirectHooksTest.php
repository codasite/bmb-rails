<?php

// use PHPUnit\Framework\TestCase;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Hooks\RedirectHooks;

class RedirectHooksTest extends TestCase {
  public function test_dashboard_redirect_not_logged_in(): void {
    $login_url = 'http://example.com/login';
    // Set expectations for the WordPress functions
    WP_Mock::userFunction('is_page', [
      'args' => 'dashboard',
      'return' => true,
    ]);

    WP_Mock::userFunction('is_user_logged_in', [
      'return' => false,
    ]);

    WP_Mock::userFunction('wp_redirect', [
      'times' => 1,
      'args' => [WP_Mock\Functions::type('string')],
      'return' => function ($url) {
        throw new Exception('Redirected to ' . $url);
      },
    ]);

    WP_Mock::userFunction('wp_login_url', [
      'args' => ['dashboard'],
      'return' => $login_url,
    ]);

    // Simulate global $wp object
    global $wp;
    $wp = (object) ['request' => 'dashboard'];

    // Call the function and assert redirection
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Redirected to ' . $login_url);

    $hooks = new RedirectHooks();
    $hooks->dashboard_redirect();
    $this->assertConditionsMet();
  }

  public function test_dashboard_redirect_logged_in(): void {
    // Set expectations for the WordPress functions
    WP_Mock::userFunction('is_page', [
      'args' => 'dashboard',
      'return' => true,
    ]);

    WP_Mock::userFunction('is_user_logged_in', [
      'return' => true,
    ]);

    WP_Mock::userFunction('wp_redirect', [
      'times' => 0,
    ]);

    WP_Mock::userFunction('wp_login_url', [
      'times' => 0,
    ]);

    // Simulate global $wp object
    global $wp;
    $wp = (object) ['request' => 'dashboard'];

    // Call the function and assert redirection
    $hooks = new RedirectHooks();
    $hooks->dashboard_redirect();
    $this->assertConditionsMet();
  }

  public function test_dashboard_redirect_not_dashboard(): void {
    // Set expectations for the WordPress functions
    WP_Mock::userFunction('is_page', [
      'args' => 'dashboard',
      'return' => false,
    ]);

    WP_Mock::userFunction('is_user_logged_in', [
      'return' => false,
    ]);

    WP_Mock::userFunction('wp_redirect', [
      'times' => 0,
    ]);

    WP_Mock::userFunction('wp_login_url', [
      'times' => 0,
    ]);

    // Simulate global $wp object
    global $wp;
    $wp = (object) ['request' => 'not-dashboard'];

    // Call the function and assert redirection
    $hooks = new RedirectHooks();
    $hooks->dashboard_redirect();
    $this->assertConditionsMet();
  }
}
