<?php
namespace WStrategies\BMB\tests\unit\Includes\Service\Notifications;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationService;
use WStrategies\BMB\Includes\Service\Notifications\EmailServiceInterface;

class BracketResultsNotificationServiceTest extends TestCase {
  public function test_should_send_notification_if_updated_results_contain_winning_team() {
    $notification_service = new BracketResultsNotificationService([
      'email_service' => $this->createMock(EmailServiceInterface::class),
    ]);

    // have a bracket with 1 play
    // have a play with 1 pick, team 1 wins over team 2, winning team is team 1
    // update results for bracket, team 1 wins over team 2
    //
  }
}
