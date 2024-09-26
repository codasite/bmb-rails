<?php

namespace WStrategies\BMB\tests\integration\Features\VotingBracket\Notifications;
use WStrategies\BMB\Features\Bracket\BracketMetaConstants;

use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
use WStrategies\BMB\Features\VotingBracket\Notifications\SendRoundCompleteNotificationsService;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class SendRoundCompleteNotificationsServiceTest extends WPBB_UnitTestCase {
  public function tear_down() {
    // delete all post meta
    parent::tear_down();
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->postmeta");
  }

  public function test_should_send_notifications() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    update_post_meta(
      $bracket->id,
      BracketMetaConstants::SHOULD_NOTIFY_ROUND_COMPLETE,
      1
    );

    $user1 = self::factory()->user->create_and_get([
      'user_email' => 'user1@test.com',
    ]);
    $user2 = self::factory()->user->create_and_get([
      'user_email' => 'user2@test.com',
    ]);

    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user1->ID,
      'picks' => [],
    ]);

    $play2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user2->ID,
      'picks' => [],
    ]);

    $email_mock = $this->getMockBuilder(EmailServiceInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $matcher = $this->exactly(2);
    $email_mock
      ->expects($matcher)
      ->method('send')
      ->willReturnCallback(function (
        $to,
        $name,
        $subject,
        $message,
        $headers
      ) use ($matcher, $user1, $user2, $bracket) {
        switch ($matcher->getInvocationCount()) {
          case 1:
            $this->assertEquals($user1->user_email, $to);
            $this->assertEquals($user1->display_name, $name);
            $this->assertEquals(
              $bracket->get_title() . ' Voting Round Complete!',
              $subject
            );
            break;
          case 2:
            $this->assertEquals($user2->user_email, $to);
            $this->assertEquals($user2->display_name, $name);
            $this->assertEquals(
              $bracket->get_title() . ' Voting Round Complete!',
              $subject
            );
            break;
        }
      });

    $service = new SendRoundCompleteNotificationsService([
      'email_service' => $email_mock,
    ]);
    $service->send_round_complete_notifications();
    $this->assertEquals(
      '0',
      get_post_meta(
        $bracket->id,
        BracketMetaConstants::SHOULD_NOTIFY_ROUND_COMPLETE,
        true
      )
    );
  }

  public function test_should_send_bracket_complete_notifications_when_bracket_is_complete() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
      'status' => 'complete',
    ]);
    update_post_meta(
      $bracket->id,
      BracketMetaConstants::SHOULD_NOTIFY_ROUND_COMPLETE,
      1
    );

    $user1 = self::factory()->user->create_and_get([
      'user_email' => 'user1@test.com',
    ]);

    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user1->ID,
      'picks' => [],
    ]);

    $email_mock = $this->getMockBuilder(EmailServiceInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $matcher = $this->exactly(1);
    $email_mock
      ->expects($matcher)
      ->method('send')
      ->willReturnCallback(function (
        $to,
        $name,
        $subject,
        $message,
        $headers
      ) use ($matcher, $user1, $bracket) {
        switch ($matcher->getInvocationCount()) {
          case 1:
            $this->assertEquals($user1->user_email, $to);
            $this->assertEquals($user1->display_name, $name);
            $this->assertEquals(
              $bracket->get_title() . ' Voting Complete!',
              $subject
            );
            break;
        }
      });

    $service = new SendRoundCompleteNotificationsService([
      'email_service' => $email_mock,
    ]);
    $service->send_round_complete_notifications();
    $this->assertEquals(
      '0',
      get_post_meta(
        $bracket->id,
        BracketMetaConstants::SHOULD_NOTIFY_ROUND_COMPLETE,
        true
      )
    );
  }
}
