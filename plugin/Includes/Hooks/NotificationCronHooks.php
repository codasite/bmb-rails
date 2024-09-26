<?php

namespace WStrategies\BMB\Includes\Hooks;

use Error;
use WStrategies\BMB\Features\VotingBracket\Notifications\SendRoundCompleteNotificationsService;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketResultsRepo;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationService;

class NotificationCronHooks implements HooksInterface {
  private BracketResultsNotificationService $results_notification_service;
  private SendRoundCompleteNotificationsService $send_round_complete_notifications_service;
  public function __construct(array $args = []) {
    $this->results_notification_service =
      $args['notification_service'] ?? new BracketResultsNotificationService();
    $this->send_round_complete_notifications_service =
      $args['send_round_complete_notifications_service'] ??
      new SendRoundCompleteNotificationsService();
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
    }
  }

  public function wpbb_notification_cron_exec(): void {
    $this->results_notification_service->send_bracket_results_notifications();
    $this->send_round_complete_notifications_service->send_round_complete_notifications();
  }
}
