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
    $message = UpcomingBracketMessageFormatter::get_message($bracket);
    $link = UpcomingBracketMessageFormatter::get_link($bracket);
    $title = UpcomingBracketMessageFormatter::get_title();

    $this->notification_manager->create_notification(
      $user->id,
      $title,
      $message,
      NotificationType::BRACKET_UPCOMING,
      $link
    );
  }
}
