<?php

namespace WStrategies\BMB\Features\Bracket\BracketResults;

use WStrategies\BMB\Features\Notifications\Application\NotificationManager;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Service\WordpressFunctions\PermalinkService;

class BracketResultsStorageListener implements
  BracketResultsNotificationListenerInterface {
  private readonly NotificationManager $notification_manager;
  private readonly PermalinkService $permalink_service;

  public function __construct($args = []) {
    $this->notification_manager =
      $args['notification_manager'] ?? new NotificationManager();
    $this->permalink_service =
      $args['permalink_service'] ?? new PermalinkService();
  }

  public function notify(User $user, Play $play, PickResult $result): void {
    $heading = BracketResultsMessageFormatter::get_heading($result);
    $link = $this->permalink_service->get_permalink($play->id) . 'view';

    $this->notification_manager->create_notification(
      $user->id,
      'Bracket Results Updated',
      $heading,
      NotificationType::BRACKET_RESULTS,
      $link
    );
  }
}
