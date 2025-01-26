<?php
namespace WStrategies\BMB\Features\VotingBracket\Notifications;

use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingService;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingServiceFactory;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;

class RoundCompletePushListener implements
  RoundCompleteNotificationListenerInterface {
  private readonly PushMessagingService $messaging_service;

  public function __construct($args = []) {
    $this->messaging_service =
      $args['messaging_service'] ??
      (new PushMessagingServiceFactory())->create($args);
  }

  public function notify(User $user, Bracket $bracket, Play $play): void {
    $heading = RoundCompleteMessageFormatter::get_heading($bracket);
    $message = RoundCompleteMessageFormatter::get_message($bracket);

    $this->messaging_service->send_notification(
      NotificationType::ROUND_COMPLETE,
      $user->id,
      $heading,
      $message
    );
  }
}
