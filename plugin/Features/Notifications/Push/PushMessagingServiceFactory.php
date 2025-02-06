<?php
namespace WStrategies\BMB\Features\Notifications\Push;

use Exception;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use WStrategies\BMB\Includes\Utils;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenRepo;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenManager;
use WStrategies\BMB\Features\Notifications\Push\Fakes\MessagingFake;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingService;

class PushMessagingServiceFactory {
  public function create(array $args = []): PushMessagingService {
    try {
      error_log('Creating push messaging service factory');

      $factory = (new Factory())->withServiceAccount(FIREBASE_CREDENTIALS_PATH);
      $info = $factory->getDebugInfo();
      $messaging = $args['messaging'] ?? $factory->createMessaging();
      if (!$messaging instanceof Messaging) {
        throw new Exception('Messaging is not an instance of Messaging');
      }
    } catch (Exception $e) {
      error_log('error creating service');
      (new Utils())->log_error(
        'Caught error: ' . $e->getMessage() . 'Returning fake Messaging'
      );
      $messaging = new MessagingFake();
    }

    $token_manager =
      $args['fcm_device_manager'] ??
      new FCMTokenManager(['token_repo' => new FCMTokenRepo()]);
    return new PushMessagingService($messaging, $token_manager);
  }
}
