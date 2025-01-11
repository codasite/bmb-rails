<?php
namespace WStrategies\BMB\Features\Notifications\Push;

use Kreait\Firebase\Factory;

class PushMessagingServiceFactory {
  public function create(array $args = []): PushMessagingService {
    $factory = new Factory(); // TODO: add project id etc
    $messaging = $args['messaging'] ?? $factory->createMessaging();
    $fcmDeviceManager =
      $args['fcm_device_manager'] ?? new FCMDeviceManager(new FCMTokenRepo());
    return new PushMessagingService($messaging, $fcmDeviceManager);
  }
}
