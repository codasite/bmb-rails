<?php
namespace WStrategies\BMB\Features\Notifications\Email;

use Exception;
use WStrategies\BMB\Features\Notifications\Email\Fakes\EmailServiceInterfaceFake;
use WStrategies\BMB\Includes\Utils;

class MailchimpEmailServiceFactory {
  public function create(): ?EmailServiceInterface {
    try {
      return new MailchimpEmailService();
    } catch (Exception $e) {
      if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
        (new Utils())->log_error(
          'Caught error: ' . $e->getMessage() . 'Returning fake email service'
        );
      }
      return new EmailServiceInterfaceFake();
    }
  }
}
