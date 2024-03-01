<?php
namespace WStrategies\BMB\tests\unit\Includes\Service\Notifications;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationService;
use WStrategies\BMB\Includes\Service\Notifications\EmailServiceInterface;

class BracketResultsNotificationServiceTest extends TestCase {
  public function test_get_pick_result_heading_should_return_won_text_when_pick_is_correct() {
    $notification_service = new BracketResultsNotificationService([
      'email_service' => $this->createMock(EmailServiceInterface::class),
    ]);
    $pick_result = new MatchPickResult([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      'losing_team' => new Team(['name' => 'Team 2', 'id' => 2]),
      'picked_team' => new Team(['name' => 'Team 1', 'id' => 1]),
    ]);
    $heading = $notification_service->get_pick_result_heading($pick_result);
    $this->assertEquals('You picked TEAM 1... and they won!', $heading);
  }

  public function test_get_pick_result_heading_should_return_lost_text_when_pick_is_incorrect() {
    $notification_service = new BracketResultsNotificationService([
      'email_service' => $this->createMock(EmailServiceInterface::class),
    ]);
    $pick_result = new MatchPickResult([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      'losing_team' => new Team(['name' => 'Team 2', 'id' => 2]),
      'picked_team' => new Team(['name' => 'Team 2', 'id' => 2]),
    ]);
    $heading = $notification_service->get_pick_result_heading($pick_result);
    $this->assertEquals(
      'You picked TEAM 2... but TEAM 1 won the round!',
      $heading
    );
  }

  public function test_should_send_notification_if_updated_results_contain_winning_team() {
  }
}
