<?php
namespace WStrategies\BMB\tests\unit\Includes\Service\Notifications;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\Fakes\UserRepoFake;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsEmailFormatService;
use WStrategies\BMB\Includes\Service\Notifications\EmailServiceInterface;
use WStrategies\BMB\Includes\Service\Notifications\Fakes\EmailServiceInterfaceFake;
use WStrategies\BMB\Includes\Service\WordpressFunctions\Fakes\PermalinkServiceFake;

class BracketResultsEmailFormatServiceTest extends TestCase {
  public function test_send_email_should_send_email_with_correct_content() {
    $email_service = $this->createMock(EmailServiceInterface::class);
    $email_service
      ->expects($this->once())
      ->method('send')
      ->with(
        'test@email.com',
        'Test User',
        'Bracket Results Updated',
        $this->isType('string'),
        $this->isType('string')
      );
    $email_format_service = new BracketResultsEmailFormatService(
      $email_service,
      new UserRepoFake(),
      new PermalinkServiceFake()
    );
    $play = new Play(['author' => 1, 'id' => 1]);
    $result = new MatchPickResult([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      'losing_team' => new Team(['name' => 'Team 2', 'id' => 2]),
      'picked_team' => new Team(['name' => 'Team 1', 'id' => 1]),
    ]);
    $email_format_service->send_email($play, $result);
  }

  public function test_get_pick_result_heading_should_return_won_text_when_pick_is_correct() {
    $email_format_service = new BracketResultsEmailFormatService(
      new EmailServiceInterfaceFake(),
      new UserRepoFake()
    );
    $pick_result = new MatchPickResult([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      'losing_team' => new Team(['name' => 'Team 2', 'id' => 2]),
      'picked_team' => new Team(['name' => 'Team 1', 'id' => 1]),
    ]);
    $heading = $email_format_service->get_pick_result_heading($pick_result);
    $this->assertEquals('You picked TEAM 1... and they won!', $heading);
  }

  public function test_get_pick_result_heading_should_return_lost_text_when_pick_is_incorrect() {
    $email_format_service = new BracketResultsEmailFormatService(
      new EmailServiceInterfaceFake(),
      new UserRepoFake()
    );
    $pick_result = new MatchPickResult([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      'losing_team' => new Team(['name' => 'Team 2', 'id' => 2]),
      'picked_team' => new Team(['name' => 'Team 2', 'id' => 2]),
    ]);
    $heading = $email_format_service->get_pick_result_heading($pick_result);
    $this->assertEquals(
      'You picked TEAM 2... but TEAM 1 won the round!',
      $heading
    );
  }
}
