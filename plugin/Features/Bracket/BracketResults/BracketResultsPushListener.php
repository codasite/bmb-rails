<?php

namespace WStrategies\BMB\Features\Bracket\BracketResults;

use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingService;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingServiceFactory;
use WStrategies\BMB\Features\Bracket\BracketResults\BracketResultsMessageFormatter;

class BracketResultsPushListener implements
  BracketResultsNotificationListenerInterface {
  private readonly PushMessagingService $messaging_service;

  public function __construct($args = []) {
    $this->messaging_service =
      $args['messaging_service'] ??
      (new PushMessagingServiceFactory())->create($args);
  }

  public function notify(User $user, Play $play, PickResult $result): void {
    $title = BracketResultsMessageFormatter::get_title();
    $message = BracketResultsMessageFormatter::get_message($result);
    $link = BracketResultsMessageFormatter::get_link($play);
    $this->messaging_service->send_notification([
      'type' => NotificationType::BRACKET_RESULTS,
      'user_id' => $user->id,
      'title' => $title,
      'message' => $message,
      'link' => $link,
    ]);
  }
}
