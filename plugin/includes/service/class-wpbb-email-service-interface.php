<?php

interface Wpbb_EmailServiceInterface {
  public function send($to_email, $to_name, $subject, $message, $html);
}
