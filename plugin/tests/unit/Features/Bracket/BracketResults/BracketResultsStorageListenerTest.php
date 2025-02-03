<?php

namespace WStrategies\BMB\tests\unit\Features\Bracket\BracketResults;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\Notifications\Application\NotificationManager;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Bracket\BracketResults\BracketResultsStorageListener;
use WStrategies\BMB\Includes\Domain\Fakes\PickResultFakeFactory;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\Fakes\UserFake;
use WStrategies\BMB\Includes\Service\WordpressFunctions\Fakes\PermalinkServiceFake;

class BracketResultsStorageListenerTest extends TestCase {
  public function test_notify_should_create_notification_with_correct_content() {
    $notification_manager = $this->createMock(NotificationManager::class);
    $notification_manager
      ->expects($this->once())
      ->method('create_notification')
      ->with(
        1, // user_id
        'Bracket Results Updated',
        $this->isType('string'), // heading
        NotificationType::BRACKET_RESULTS,
        $this->isType('string') // link
      );

    $storage_listener = new BracketResultsStorageListener([
      'notification_manager' => $notification_manager,
      'permalink_service' => new PermalinkServiceFake(),
    ]);

    $play = new Play(['author' => 1, 'id' => 1]);
    $result = PickResultFakeFactory::get_correct_pick_result();
    $user = new UserFake();

    $storage_listener->notify($user, $play, $result);
  }
}
