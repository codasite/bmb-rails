<?php
namespace WStrategies\BMB\Features\VotingBracket\Notifications;

use WStrategies\BMB\Features\Notifications\NotificationListeners\RoundCompleteNotificationListenerInterface;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;

class RoundCompletePushListener implements
  RoundCompleteNotificationListenerInterface {
  public function notify(User $user, Bracket $bracket, Play $play): void {
    // TODO: Implement notify() method.
  }
}
