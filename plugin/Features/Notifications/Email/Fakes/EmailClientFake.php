<?php

namespace WStrategies\BMB\Features\Notifications\Email\Fakes;

use WStrategies\BMB\Features\Notifications\Email\EmailClientInterface;

class EmailClientFake implements EmailClientInterface {
  public function send($to_email, $to_name, $subject, $message, $html) {
  }
}
