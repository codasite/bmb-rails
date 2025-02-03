<?php

namespace WStrategies\BMB\Features\Notifications\Presentation;

use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;
use WP_CLI;
/**
 * Registers the notification WP-CLI command.
 */
class NotificationCommandLoader implements HooksInterface {
  public function load(Loader $loader): void {
    if (defined('WP_CLI')) {
      WP_CLI::add_command('wpbb notification', new NotificationCommand());
    }
  }
}
