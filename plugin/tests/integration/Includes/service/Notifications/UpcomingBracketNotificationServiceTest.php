<?php
namespace WStrategies\BMB\tests\integration\Includes\service\Notifications;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
use WStrategies\BMB\Includes\Service\Notifications\UpcomingBracketNotificationService;
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
    $email_mock = $this->createMock(EmailServiceInterface::class);
    $email_mock
      ->expects($this->exactly(2))
      ->method('send')
      ->withConsecutive(
        [
          $user1->user_email,
          $user1->display_name,
          $this->isType('string'),
          $this->isType('string'),
          $this->isType('string'),
        ],
        [
          $user2->user_email,
          $user2->display_name,
          $this->isType('string'),
          $this->isType('string'),
          $this->isType('string'),
        ]
      );
    $notification_service = new UpcomingBracketNotificationService([
      'email_service' => $email_mock,
    ]);
    $notification_service->notify_upcoming_bracket_live($bracket1->id);
  }
}
