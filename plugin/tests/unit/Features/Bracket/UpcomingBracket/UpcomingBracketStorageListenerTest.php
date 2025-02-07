<?php

namespace WStrategies\BMB\tests\unit\Features\Bracket\UpcomingBracket;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\Notifications\Application\NotificationManager;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Domain\NotificationSubscription;
use WStrategies\BMB\Features\Bracket\UpcomingBracket\UpcomingBracketStorageListener;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Fakes\UserFake;
use WStrategies\BMB\Includes\Service\WordpressFunctions\Fakes\PermalinkServiceFake;

class UpcomingBracketStorageListenerTest extends TestCase {
  public function test_notify_should_create_notification_with_correct_content() {
    $notification_manager = $this->createMock(NotificationManager::class);
    $notification_manager
      ->expects($this->once())
      ->method('create_notification')
      ->with(
        1, // user_id
        $this->isType('string'), // heading
        'TEST BRACKET is now live. Make your picks!',
        NotificationType::BRACKET_UPCOMING,
        $this->isType('string') // link
      );

    $storage_listener = new UpcomingBracketStorageListener([
      'notification_manager' => $notification_manager,
      'permalink_service' => new PermalinkServiceFake(),
    ]);

    $bracket = new Bracket(['id' => 1, 'title' => 'Test Bracket']);
    $notification = new NotificationSubscription([
      'user_id' => 1,
      'post_id' => 1,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $user = new UserFake();

    $storage_listener->notify($user, $bracket, $notification);
  }
}
