<?php

namespace WStrategies\BMB\Features\Notifications\Email\Fakes;

use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;

class EmailServiceInterfaceFake implements EmailServiceInterface {
  public function send($to_email, $to_name, $subject, $message, $html) {
  }
}
