<?php

use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsEmailFormatService;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationService;
use WStrategies\BMB\Includes\Service\Notifications\EmailServiceInterface;

class BracketResultsNotificationServiceTest extends WPBB_UnitTestCase {
  public function test_notify_bracket_results_updated() {
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

    $user_picks = [
      [
        'user_id' => $user1->ID,
        'play_id' => $play1->id,
        'pick_id' => $play1->picks[2]->id,
      ],
      [
        'user_id' => $user2->ID,
        'play_id' => $play2->id,
        'pick_id' => $play2->picks[2]->id,
      ],
    ];

    $play_repo_mock = $this->getMockBuilder(PlayRepo::class)
      ->onlyMethods(['get_user_picks_for_result'])
      ->getMock();
    $play_repo_mock
      ->method('get_user_picks_for_result')
      ->willReturn($user_picks);

    $email_mock = $this->getMockBuilder(EmailServiceInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $email_mock
      ->expects($this->exactly(2))
      ->method('send')
      ->withConsecutive(
        [
          $this->equalTo($user1->user_email),
          $this->equalTo($user1->display_name),
          $this->equalTo('Bracket Results Updated'),
          $this->anything(),
          $this->anything(),
        ],
        [
          $this->equalTo($user2->user_email),
          $this->equalTo($user2->display_name),
          $this->equalTo('Bracket Results Updated'),
          $this->anything(),
          $this->anything(),
        ]
      );

    $notification_service = new BracketResultsNotificationService([
      'play_repo' => $play_repo_mock,
      'email_format_service' => new BracketResultsEmailFormatService(
        email_service: $email_mock
      ),
    ]);

    $notification_service->notify_bracket_results_updated($bracket);
  }
}
