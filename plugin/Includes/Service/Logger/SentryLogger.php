<?php
namespace WStrategies\BMB\Includes\Service\Logger;

class SentryLogger {
  public static function log($msg, $level = 'debug'): void {
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
      wp_sentry_safe(function (\Sentry\State\HubInterface $client) use (
        $msg,
        $severity
      ) {
        $client->captureMessage($msg, $severity);
      });
    }
  }

  public static function log_error($msg): void {
    self::log($msg, 'error');
  }

  public static function warn($msg): void {
    self::log($msg, 'warning');
  }
}
