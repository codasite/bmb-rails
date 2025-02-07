<?php

namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\Domain\NotificationSubscription;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingService;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingServiceFactory;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\User;

class UpcomingBracketPushListener implements
  UpcomingNotificationListenerInterface {
  private readonly PushMessagingService $messaging_service;

  public function __construct($args = []) {
    $this->messaging_service =
      $args['messaging_service'] ??
      (new PushMessagingServiceFactory())->create($args);
  }

  public function notify(
    User $user,
    Bracket $bracket,
    NotificationSubscription $notification
  ): void {
    $title = UpcomingBracketMessageFormatter::get_title();
    $message = UpcomingBracketMessageFormatter::get_message($bracket);
    $link = UpcomingBracketMessageFormatter::get_link($bracket);
    $this->messaging_service->send_notification([
      'type' => NotificationType::BRACKET_UPCOMING,
      'user_id' => $user->id,
      'title' => $title,
      'message' => $message,
      'link' => $link,
    ]);
  }
}
