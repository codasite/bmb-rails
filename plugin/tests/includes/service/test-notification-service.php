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
}
