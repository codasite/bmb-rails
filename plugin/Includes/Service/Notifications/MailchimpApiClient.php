<?php

namespace WStrategies\BMB\Includes\Service\Notifications;

use MailchimpTransactional\ApiClient;

class MailchimpApiClient extends ApiClient {
  public $messages;
  public $users;
}
