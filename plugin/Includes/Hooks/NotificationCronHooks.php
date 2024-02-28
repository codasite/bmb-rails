<?php

namespace WStrategies\BMB\Includes\Hooks;

class NotificationCronHooks implements HooksInterface {
  public function load(Loader $loader): void {
    $loader->add_action('wpbb_notification_cron_hook', [
      $this,
      'wpbb_notification_cron_exec',
    ]);
    if (!wp_next_scheduled('wpbb_notification_cron_hook')) {
      wp_schedule_event(time(), 'hourly', 'wpbb_notification_cron_hook');
    }
  }

  public function wpbb_notification_cron_exec() {
  }
}
