<?php
class Wpbb_Utils {
  static function now() {
    return new DateTimeImmutable('now', new DateTimeZone('UTC'));
  }

  public function set_cookie(
    $key,
    $value,
    array $expires = ['days' => 1],
    array $options = []
  ) {
    $expiration = time();
    if (isset($expires['years'])) {
      $expiration += 60 * 60 * 24 * 365 * $expires['years'];
    }
    if (isset($expires['months'])) {
      $expiration += 60 * 60 * 24 * 30 * $expires['months'];
    }
    if (isset($expires['days'])) {
      $expiration += 60 * 60 * 24 * $expires['days'];
    }
    if (isset($expires['hours'])) {
      $expiration += 60 * 60 * $expires['hours'];
    }
    if (isset($expires['minutes'])) {
      $expiration += 60 * $expires['minutes'];
    }
    if (isset($expires['seconds'])) {
      $expiration += $expires['seconds'];
    }

    $default_options = [
      'path' => '/',
      'domain' => COOKIE_DOMAIN,
      'secure' => is_ssl(),
      'httponly' => false,
    ];
    $options = array_merge($default_options, $options);

    setcookie(
      $key,
      $value,
      $expiration,
      $options['path'],
      $options['domain'],
      $options['secure'],
      $options['httponly']
    );
  }

  public function get_cookie($key) {
    if (isset($_COOKIE[$key])) {
      return $_COOKIE[$key];
    }
    return null;
  }

  public function pop_cookie($key) {
    $value = $this->get_cookie($key);
    if ($value) {
      $this->set_cookie($key, '', ['days' => -1]);
    }
    return $value;
  }

  public function set_session_value($key, $value) {
    if (!session_id()) {
      session_start();
    }
    $_SESSION[$key] = $value;
  }

  // Get value from user session
  public function get_session_value($key) {
    if (!session_id()) {
      session_start();
    }
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }
    return null;
  }

  public function log_sentry_error($error) {
    if (function_exists('wp_sentry_safe')) {
      wp_sentry_safe(function (\Sentry\State\HubInterface $client) use (
        $error
      ) {
        $client->captureException($error);
      });
    }
  }

  public function log_sentry_message($msg, $level = null) {
    if (function_exists('wp_sentry_safe')) {
      return wp_sentry_safe(function (\Sentry\State\HubInterface $client) use (
        $msg,
        $level
      ) {
        if ($level === null) {
          $level = \Sentry\Severity::info();
        }
        return $client->captureMessage($msg, $level);
      });
    }
  }

  public function log($msg, $level = 'debug') {
    error_log($msg);
    switch ($level) {
      case 'debug':
        $severity = \Sentry\Severity::debug();
        break;
      case 'info':
        $severity = \Sentry\Severity::info();
        break;
      case 'warning':
        $severity = \Sentry\Severity::warning();
        break;
      case 'error':
        $severity = \Sentry\Severity::error();
        break;
      case 'fatal':
        $severity = \Sentry\Severity::fatal();
        break;
      default:
        $severity = \Sentry\Severity::info();
        break;
    }

    if (function_exists('wp_sentry_safe')) {
      return wp_sentry_safe(function (\Sentry\State\HubInterface $client) use (
        $msg,
        $severity
      ) {
        return $client->captureMessage($msg, $severity);
      });
    }
  }

  public function log_error($msg) {
    return $this->log($msg, 'error');
  }

  public function warn($msg) {
    return $this->log($msg, 'warning');
  }
}
