<?php
namespace WStrategies\BMB\Includes\Service;

interface EmailServiceInterface {
  public function send($to_email, $to_name, $subject, $message, $html);
}
