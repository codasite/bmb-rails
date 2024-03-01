<?php

namespace WStrategies\BMB\Includes\Service\Notifications;

use MailchimpTransactional\Api\MessagesApi;
use MailchimpTransactional\Api\UsersApi;
use MailchimpTransactional\ApiClient;

class MailchimpApiClient extends ApiClient {
  public MessagesApi $messages;
  public UsersApi $users;
}
