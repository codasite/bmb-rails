<?php

namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\Application\NotificationManager;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Domain\NotificationSubscription;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\User;
use WStrategies\BMB\Includes\Service\WordpressFunctions\PermalinkService;

class UpcomingBracketStorageListener implements
  UpcomingNotificationListenerInterface {
  private readonly NotificationManager $notification_manager;
  private readonly PermalinkService $permalink_service;

  public function __construct($args = []) {
    $this->notification_manager =
      $args['notification_manager'] ?? new NotificationManager();
    $this->permalink_service =
      $args['permalink_service'] ?? new PermalinkService();
  }

  public function notify(
    User $user,
    Bracket $bracket,
    NotificationSubscription $notification
  ): void {
    $heading = UpcomingBracketMessageFormatter::get_heading($bracket);
    $link = $this->permalink_service->get_permalink($bracket->id);

    $this->notification_manager->create_notification(
      $user->id,
      $heading,
      'A bracket you are following is about to start!',
      NotificationType::BRACKET_UPCOMING,
      $link
    );
  }
}
