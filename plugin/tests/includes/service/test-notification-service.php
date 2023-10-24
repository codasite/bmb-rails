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
    $email_mock = $this->getMockBuilder('Wpbb_EmailServiceInterface')
      ->disableOriginalConstructor()
      ->getMock();
    // $email_mock
    //   ->expects($this->once())
    //   ->method('send')
    //   ->with(
    //     $this->equalTo('
  }
}
