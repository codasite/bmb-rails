<?php
namespace WStrategies\BMB\Includes;

use DateTimeImmutable;
use DateTimeZone;
use Sentry\Severity;

class Utils {
  static function now(): DateTimeImmutable {
    return new DateTimeImmutable('now', new DateTimeZone('UTC'));
  }

  public function set_cookie(
    $key,
    $value,
    array $expires = ['days' => 1],
    array $options = []
  ): void {
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

  public function set_session_value($key, $value): void {
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

  public function log_sentry_error(\Throwable $error): void {
    if (function_exists('wp_sentry_safe')) {
      wp_sentry_safe(function (\Sentry\State\HubInterface $client) use (
        $error
      ) {
        $client->captureException($error);
      });
    }
  }

  public function log_sentry_message(
    string $msg,
    Severity $level = null
  ): void {
    if (function_exists('wp_sentry_safe')) {
      wp_sentry_safe(function (\Sentry\State\HubInterface $client) use (
        $msg,
        $level
      ) {
        if ($level === null) {
          $level = \Sentry\Severity::info();
        }
        $client->captureMessage($msg, $level);
      });
    }
  }

  /**
   * @param string $msg
   * @param 'debug'|'warning'|'error'|'fatal'|'info' $level
   *
   * @return void
   */
  public function log(string $msg, string $level = 'debug'): void {
    if (defined('DOING_TESTS')) {
      return;
    }
    error_log($msg);
    $severity = match ($level) {
      'debug' => \Sentry\Severity::debug(),
      'warning' => \Sentry\Severity::warning(),
      'error' => \Sentry\Severity::error(),
      'fatal' => \Sentry\Severity::fatal(),
      default => \Sentry\Severity::info(),
    };

    if (function_exists('wp_sentry_safe')) {
      wp_sentry_safe(function (\Sentry\State\HubInterface $client) use (
        $msg,
        $severity
      ) {
        $client->captureMessage($msg, $severity);
      });
    }
  }

  public function log_error(string $msg): void {
    $this->log($msg, 'error');
  }

  public function warn(string $msg): void {
    $this->log($msg, 'warning');
  }
}
