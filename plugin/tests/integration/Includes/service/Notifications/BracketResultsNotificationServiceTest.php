<?php

use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsEmailFormatService;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationService;
use WStrategies\BMB\Includes\Service\Notifications\EmailServiceInterface;

class BracketResultsNotificationServiceTest extends WPBB_UnitTestCase {
  public function test_should_notify_bracket_results_updated() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
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
      ) use ($matcher, $user1, $user2) {
        switch ($matcher->getInvocationCount()) {
          case 1:
            $this->assertEquals($user1->user_email, $to);
            $this->assertEquals($user1->display_name, $name);
            $this->assertEquals('Bracket Results Updated', $subject);
            break;
          case 2:
            $this->assertEquals($user2->user_email, $to);
            $this->assertEquals($user2->display_name, $name);
            $this->assertEquals('Bracket Results Updated', $subject);
            break;
        }
      });

    $notification_service = new BracketResultsNotificationService([
      'email_service' => $email_mock,
    ]);

    $notification_service->notify_bracket_results_updated($bracket);
  }
}
