<?php
namespace WStrategies\BMB\Features\Notifications\Email;

interface EmailServiceInterface {
  public function send($to_email, $to_name, $subject, $message, $html);
}
