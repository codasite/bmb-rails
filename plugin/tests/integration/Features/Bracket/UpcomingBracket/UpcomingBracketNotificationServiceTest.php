<?php
namespace WStrategies\BMB\tests\integration\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\Application\NotificationDispatcher;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Bracket\UpcomingBracket\UpcomingBracketNotificationService;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class UpcomingBracketNotificationServiceTest extends WPBB_UnitTestCase {
  public function test_notify_upcoming_bracket_live() {
    $bracket1 = $this->create_bracket();
    $bracket2 = $this->create_bracket();
    $user1 = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $notif1 = self::factory()->notification->create_object([
      'user_id' => $user1->ID,
      'post_id' => $bracket1->id,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $notif2 = self::factory()->notification->create_object([
      'user_id' => $user2->ID,
      'post_id' => $bracket1->id,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $notif3 = self::factory()->notification->create_object([
      'user_id' => $user1->ID,
      'post_id' => $bracket2->id,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);

    $dispatcher = $this->getMockBuilder(NotificationDispatcher::class)
      ->disableOriginalConstructor()
      ->getMock();
    $matcher = $this->exactly(2);
    $dispatcher
      ->expects($matcher)
      ->method('dispatch')
      ->willReturnCallback(function (Notification $notification) use (
        $matcher,
        $user1,
        $user2
      ) {
        switch ($matcher->getInvocationCount()) {
          case 1:
            $this->assertEquals($user1->ID, $notification->user_id);
            $this->assertEquals(
              'Tournament is now live!',
              $notification->title
            );
            break;
          case 2:
            $this->assertEquals($user2->ID, $notification->user_id);
            $this->assertEquals(
              'Tournament is now live!',
              $notification->title
            );
            break;
        }
      });

    $notification_service = new UpcomingBracketNotificationService([
      'dispatcher' => $dispatcher,
    ]);
    $notification_service->notify_upcoming_bracket_live($bracket1->id);
  }
}
