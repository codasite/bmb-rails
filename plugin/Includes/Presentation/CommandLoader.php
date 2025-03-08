<?php

namespace WStrategies\BMB\Includes\Presentation;

use WStrategies\BMB\Features\Bracket\Presentation\BracketCommand;
use WStrategies\BMB\Features\Notifications\Presentation\NotificationCommand;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;
use WP_CLI;

/**
 * Registers all WP-CLI commands for the plugin.
 */
class CommandLoader implements HooksInterface {
  public function load(Loader $loader): void {
    if (defined('WP_CLI')) {
      // Register notification commands
      WP_CLI::add_command('wpbb notification', new NotificationCommand());

      // Register bracket commands
      WP_CLI::add_command('wpbb bracket', new BracketCommand());
    }
  }
}
