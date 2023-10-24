<?php
require_once WPBB_PLUGIN_DIR . 'includes/service/class-wpbb-score-service.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-match-pick.php';

class NotificationServiceTest extends WPBB_UnitTestCase {
  public function test_correct_picked() {
    $email_mock = $this->getMockBuilder('Wpbb_EmailServiceInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $notification_service = new Wpbb_NotificationService([
      'email_service' => $email_mock,
    ]);
    $correct_pick = new Wpbb_MatchPick([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team_id' => 1,
    ]);
    $incorrect_pick = new Wpbb_MatchPick([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team_id' => 2,
    ]);

    $winning_pick = new Wpbb_MatchPick([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team_id' => 1,
    ]);

    $this->assertTrue(
      $notification_service->correct_picked($correct_pick, $winning_pick)
    );
    $this->assertFalse(
      $notification_service->correct_picked($incorrect_pick, $winning_pick)
    );
  }

  public function test_get_pick_result_heading() {
    $email_mock = $this->getMockBuilder('Wpbb_EmailServiceInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $notification_service = new Wpbb_NotificationService([
      'email_service' => $email_mock,
    ]);
    $winning_pick = new Wpbb_MatchPick([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team_id' => 1,
      'winning_team' => new Wpbb_Team(['name' => 'Team 1']),
    ]);
    $correct_pick = new Wpbb_MatchPick([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team_id' => 1,
      'winning_team' => new Wpbb_Team(['name' => 'Team 1']),
    ]);
    $incorrect_pick = new Wpbb_MatchPick([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team_id' => 2,
      'winning_team' => new Wpbb_Team(['name' => 'Team 2']),
    ]);

    $this->assertEquals(
      'You picked Team 1... and they won!',
      $notification_service->get_pick_result_heading(
        $correct_pick,
        $winning_pick
      )
    );
    $this->assertEquals(
      'You picked Team 2, but Team 1 won the round...',
      $notification_service->get_pick_result_heading(
        $incorrect_pick,
        $winning_pick
      )
    );
  }

  public function test_notify_bracket_results_updated() {
    $bracket = self::factory()->bracket->create_object([
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

    $bracket = self::factory()->bracket->update_object($bracket->id, [
      'results' => $results,
    ]);

    $user1 = self::factory()->user->create_and_get([
      'user_email' => 'user1@test.com',
    ]);
    $user2 = self::factory()->user->create_and_get([
      'user_email' => 'user2@test.com',
    ]);

    $play1 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'author' => $user1->ID,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $play2 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'author' => $user2->ID,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team2->id,
        ]),
        new Wpbb_MatchPick([
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

    $play_repo_mock = $this->getMockBuilder(Wpbb_BracketPlayRepo::class)
      ->onlyMethods(['get_user_picks_for_result'])
      ->getMock();
    $play_repo_mock
      ->method('get_user_picks_for_result')
      ->willReturn($user_picks);

    $email_mock = $this->getMockBuilder('Wpbb_EmailServiceInterface')
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

    $notification_service = new Wpbb_NotificationService([
      'email_service' => $email_mock,
      'play_repo' => $play_repo_mock,
    ]);

    $notification_service->notify_bracket_results_updated($bracket);
  }
}
