<?php

namespace WStrategies\BMB\tests\unit\Features\VotingBracket\Notifications;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\Notifications\Application\NotificationManager;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\VotingBracket\Notifications\RoundCompleteStorageListener;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\Fakes\UserFake;
use WStrategies\BMB\Includes\Service\WordpressFunctions\Fakes\PermalinkServiceFake;

class RoundCompleteStorageListenerTest extends TestCase {
  public function test_notify_should_create_notification_with_correct_content() {
    $notification_manager = $this->createMock(NotificationManager::class);
    $notification_manager
      ->expects($this->once())
      ->method('create_notification')
      ->with(
        1, // user_id
        $this->isType('string'), // heading
        $this->isType('string'), // message
        NotificationType::ROUND_COMPLETE,
        $this->isType('string') // link
      );

    $storage_listener = new RoundCompleteStorageListener([
      'notification_manager' => $notification_manager,
      'permalink_service' => new PermalinkServiceFake(),
    ]);

    $bracket = new Bracket(['id' => 1, 'title' => 'Test Bracket']);
    $play = new Play(['id' => 1]);
    $user = new UserFake();

    $storage_listener->notify($user, $bracket, $play);
  }
}
