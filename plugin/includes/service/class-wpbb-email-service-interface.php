<?php

interface Wpbb_Email_Service_Interface
{
  public function send($to_email, $to_name, $subject, $message, $html);
}
