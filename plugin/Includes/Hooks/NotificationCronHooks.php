<?php

namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketResultsRepo;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationService;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationServiceFactory;

class NotificationCronHooks implements HooksInterface {
  private BracketRepo $bracket_repo;
  private ?BracketResultsNotificationService $notification_service;
  public function __construct(array $args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->notification_service =
      $args['notification_service'] ??
      (new BracketResultsNotificationServiceFactory())->create();
  }

  public function load(Loader $loader): void {
    $loader->add_action('wpbb_notification_cron_hook', [
      $this,
      'wpbb_notification_cron_exec',
    ]);
    if (!wp_next_scheduled('wpbb_notification_cron_hook')) {
      wp_schedule_event(time(), 'hourly', 'wpbb_notification_cron_hook');
    }
  }

  public function wpbb_notification_cron_exec(): void {
    if (!$this->notification_service) {
      return;
    }
    $brackets = $this->bracket_repo->get_all([
      'meta_query' => [
        [
          'key' => BracketResultsRepo::SHOULD_SEND_NOTIFICATIONS_META_KEY,
          'value' => true,
        ],
      ],
    ]);

    foreach ($brackets as $bracket) {
      $this->notification_service->notify_bracket_results_updated($bracket);
    }
  }
}
