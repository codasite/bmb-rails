<?php
namespace WStrategies\BMB\Features\Bracket\BracketResults;

use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;

interface BracketResultsNotificationListenerInterface {
  public function notify(Play $play, PickResult $result): void;
}
