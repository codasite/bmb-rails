<?php
namespace WStrategies\BMB\Features\Notifications\Push;

use Exception;
use Kreait\Firebase\Factory;
use WStrategies\BMB\Includes\Utils;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenRepo;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenManager;
use WStrategies\BMB\Features\Notifications\Push\Fakes\MessagingFake;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingService;

class PushMessagingServiceFactory {
  public function create(array $args = []): PushMessagingService {
    try {
      $factory = (new Factory())->withProjectId('bmb-mobile'); // TODO: add project id etc
      $messaging = $args['messaging'] ?? $factory->createMessaging();
    } catch (Exception $e) {
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
