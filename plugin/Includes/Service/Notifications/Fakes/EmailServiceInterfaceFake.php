<?php

namespace WStrategies\BMB\Includes\Service\Notifications\Fakes;

use WStrategies\BMB\Includes\Service\Notifications\EmailServiceInterface;

class EmailServiceInterfaceFake implements EmailServiceInterface {
  public function send($to_email, $to_name, $subject, $message, $html) {
  }
}
