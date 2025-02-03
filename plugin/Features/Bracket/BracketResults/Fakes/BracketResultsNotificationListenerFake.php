<?php

namespace WStrategies\BMB\Features\Bracket\BracketResults\Fakes;

use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;
use WStrategies\BMB\Features\Bracket\BracketResults\BracketResultsNotificationListenerInterface;

class BracketResultsNotificationListenerFake implements
  BracketResultsNotificationListenerInterface {
  public bool $notify_was_called = false;
  public ?User $last_notified_user = null;
  public ?Play $last_notified_play = null;
  public ?PickResult $last_notified_result = null;

  public function notify(User $user, Play $play, PickResult $result): void {
    $this->notify_was_called = true;
    $this->last_notified_user = $user;
    $this->last_notified_play = $play;
    $this->last_notified_result = $result;
  }
}
