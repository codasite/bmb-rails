<?php
namespace WStrategies\BMB\Includes\Service\Notifications;

use Exception;
use WStrategies\BMB\Includes\Utils;

class BracketResultsNotificationServiceFactory {
  public function create(): ?BracketResultsNotificationService {
    try {
        return new BracketResultsNotificationService();
    } catch (Exception $e) {
      if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
        (new Utils())->log_error(
          'Caught error: ' .
          $e->getMessage() .
          'Returning null notification service'
        );
      }
      return null;
    }
  }
}
