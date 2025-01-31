<?php

namespace WStrategies\BMB\Features\VotingBracket\Notifications;

use WStrategies\BMB\Features\Notifications\Application\NotificationManager;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;
use WStrategies\BMB\Includes\Service\WordpressFunctions\PermalinkService;

class RoundCompleteStorageListener implements
  RoundCompleteNotificationListenerInterface {
  private readonly NotificationManager $notification_manager;
  private readonly PermalinkService $permalink_service;

  public function __construct($args = []) {
    $this->notification_manager =
      $args['notification_manager'] ?? new NotificationManager();
    $this->permalink_service =
      $args['permalink_service'] ?? new PermalinkService();
  }

  public function notify(User $user, Bracket $bracket, Play $play): void {
    $heading = RoundCompleteMessageFormatter::get_heading($bracket);
    $message = RoundCompleteMessageFormatter::get_message($bracket);
    $link =
      $this->permalink_service->get_permalink($bracket->id) .
      RoundCompleteMessageFormatter::get_button_url_suffix($bracket);

    $this->notification_manager->create_notification(
      $user->id,
      $heading,
      $message,
      NotificationType::ROUND_COMPLETE,
      $link
    );
  }
}
