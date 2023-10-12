<?php
class Wpbb_Utils
{
  public function set_session_value($key, $value)
  {
    if (!session_id()) {
      session_start();
    }
    $_SESSION[$key] = $value;
  }

  // Get value from user session
  public function get_session_value($key)
  {
    if (!session_id()) {
      session_start();
    }
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }
    return null;
  }

  public function log_sentry_error($error)
  {
    if (function_exists('wp_sentry_safe')) {
      wp_sentry_safe(function (\Sentry\State\HubInterface $client) use (
        $error
      ) {
        $client->captureException($error);
      });
    }
  }

  public function log_sentry_message($msg, $level = null)
  {
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

  public function log($msg, $level = 'debug')
  {
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
}
