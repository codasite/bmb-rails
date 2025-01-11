<?php
namespace WStrategies\BMB\Features\VotingBracket\Notifications;

use WStrategies\BMB\Features\VotingBracket\Notifications\RoundCompleteNotificationListenerInterface;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;
use WStrategies\BMB\Features\VotingBracket\Notifications\RoundCompleteMessageFormatter;

class RoundCompletePushListener implements
  RoundCompleteNotificationListenerInterface {
  public function notify(User $user, Bracket $bracket, Play $play): void {
    $heading = RoundCompleteMessageFormatter::get_heading($bracket);
    $message = RoundCompleteMessageFormatter::get_message($bracket);
    // TODO: Implement push notification logic using $heading and $message
  }
}
