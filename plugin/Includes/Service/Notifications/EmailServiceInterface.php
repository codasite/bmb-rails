<?php
namespace WStrategies\BMB\Includes\Service\Notifications;

interface EmailServiceInterface {
  public function send($to_email, $to_name, $subject, $message, $html);
}
