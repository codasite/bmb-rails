<?php

namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketResultsRepo;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationService;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationServiceFactory;

class NotificationCronHooks implements HooksInterface {
  private ?BracketResultsNotificationService $notification_service;
  public function __construct(array $args = []) {
    $this->notification_service =
      $args['notification_service'] ??
      (new BracketResultsNotificationServiceFactory())->create();
  }

  public function load(Loader $loader): void {
    $loader->add_action('init', [$this, 'schedule_cron']);
    $loader->add_action('wpbb_notification_cron_hook', [
      $this,
      'wpbb_notification_cron_exec',
    ]);
  }

  public function schedule_cron(): void {
    if (!wp_next_scheduled('wpbb_notification_cron_hook')) {
      wp_schedule_event(time(), 'every_minute', 'wpbb_notification_cron_hook');
      // wp_schedule_event(time(), 'hourly', 'wpbb_notification_cron_hook');
    }
  }

  public function wpbb_notification_cron_exec(): void {
    if (!$this->notification_service) {
      return;
    }
    $this->notification_service->send_bracket_results_notifications();
  }
}
