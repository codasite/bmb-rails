<?php
namespace WStrategies\BMB\tests\integration\Features\Bracket\BracketResults;

use WStrategies\BMB\Features\Bracket\BracketMetaConstants;
use WStrategies\BMB\Features\Notifications\Application\NotificationDispatcher;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Features\Bracket\BracketResults\BracketResultsNotificationService;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class BracketResultsNotificationServiceTest extends WPBB_UnitTestCase {
  public function tear_down() {
    // delete all post meta
    parent::tear_down();
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->postmeta");
  }
  public function test_should_send_results_notifications_for_bracket() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
      BracketMetaConstants::SHOULD_NOTIFY_RESULTS_UPDATED => true,
    ]);

    $results = [
      [
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => $bracket->matches[0]->team1->id,
      ],
      [
        'round_index' => 0,
        'match_index' => 1,
        'winning_team_id' => $bracket->matches[1]->team1->id,
      ],
      [
        'round_index' => 1,
        'match_index' => 0,
        'winning_team_id' => $bracket->matches[0]->team1->id,
      ],
    ];

    $bracket = $this->update_bracket($bracket->id, [
      'results' => $results,
    ]);

    $user1 = self::factory()->user->create_and_get([
      'user_email' => 'user1@test.com',
    ]);
    $user2 = self::factory()->user->create_and_get([
      'user_email' => 'user2@test.com',
    ]);

    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user1->ID,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $play2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user2->ID,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team2->id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ]),
      ],
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
              'Bracket Results Updated',
              $notification->title
            );
            break;
          case 2:
            $this->assertEquals($user2->ID, $notification->user_id);
            $this->assertEquals(
              'Bracket Results Updated',
              $notification->title
            );
            break;
        }
      });

    $notification_service = new BracketResultsNotificationService([
      'dispatcher' => $dispatcher,
    ]);

    $notification_service->send_results_notifications_for_bracket($bracket);
    $bracket = $this->get_bracket($bracket->id);
    $this->assertFalse($bracket->should_notify_results_updated);
  }

  public function test_should_return_all_brackets_to_send_results_notifications_for() {
    $bracket1 = $this->create_bracket([
      BracketMetaConstants::SHOULD_NOTIFY_RESULTS_UPDATED => true,
    ]);
    $bracket2 = $this->create_bracket();
    $bracket3 = $this->create_bracket([
      BracketMetaConstants::SHOULD_NOTIFY_RESULTS_UPDATED => true,
    ]);
    $service = new BracketResultsNotificationService([
      'dispatcher' => $this->createMock(NotificationDispatcher::class),
    ]);
    $brackets = $service->get_brackets_to_send_results_notifications_for();
    $this->assertEquals(2, count($brackets));
    $this->assertEquals($bracket1->id, $brackets[0]->id);
    $this->assertEquals($bracket3->id, $brackets[1]->id);
  }

  public function test_should_return_empty_array_when_no_brackets_to_send_results_notifications_for() {
    $bracket1 = $this->create_bracket();
    $bracket2 = $this->create_bracket();
    $bracket3 = $this->create_bracket();
    $service = new BracketResultsNotificationService([
      'dispatcher' => $this->createMock(NotificationDispatcher::class),
    ]);
    $brackets = $service->get_brackets_to_send_results_notifications_for();
    $this->assertEquals(0, count($brackets));
  }

  public function test_should_exclude_anonymous_plays() {
    $user1 = $this->create_user();
    $user2 = $this->create_user();
    $bracket = $this->create_bracket();
    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user1->ID,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user2->ID,
    ]);
    $play3 = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => 0,
    ]);
    $service = new BracketResultsNotificationService([
      'dispatcher' => $this->createMock(NotificationDispatcher::class),
    ]);
    $plays = $service->get_plays($bracket);
    $this->assertEquals(2, count($plays));
  }
}
