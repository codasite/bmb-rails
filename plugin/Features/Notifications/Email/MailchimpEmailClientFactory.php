<?php
namespace WStrategies\BMB\Features\Notifications\Email;

use Exception;
use WStrategies\BMB\Features\Notifications\Email\Fakes\EmailClientFake;
use WStrategies\BMB\Includes\Utils;

class MailchimpEmailClientFactory {
  public function create(): EmailClientInterface {
    try {
      return new MailchimpEmailClient();
    } catch (Exception $e) {
      if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
        (new Utils())->log_error(
          'Caught error: ' . $e->getMessage() . 'Returning fake email service'
        );
      }
      return new EmailClientFake();
    }
  }
}
