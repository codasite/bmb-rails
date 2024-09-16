<?php
namespace WStrategies\BMB\tests\unit\Includes\Service\Notifications;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\Notifications\Email\Fakes\EmailServiceInterfaceFake;
use WStrategies\BMB\Includes\Domain\Fakes\PickResultFakeFactory;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\Fakes\UserRepoFake;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsEmailFormatService;
use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
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
    $result = PickResultFakeFactory::get_correct_pick_result();
    $email_format_service->send_email($play, $result);
  }

  public function test_get_pick_result_heading_should_return_won_text_when_pick_is_correct() {
    $email_format_service = new BracketResultsEmailFormatService(
      new EmailServiceInterfaceFake(),
      new UserRepoFake()
    );
    $pick_result = PickResultFakeFactory::get_correct_pick_result();
    $heading = $email_format_service->get_pick_result_heading($pick_result);
    $this->assertEquals('You picked TEAM 1... and they won!', $heading);
  }

  public function test_get_pick_result_heading_should_return_lost_text_when_pick_is_incorrect() {
    $email_format_service = new BracketResultsEmailFormatService(
      new EmailServiceInterfaceFake(),
      new UserRepoFake()
    );
    $pick_result = PickResultFakeFactory::get_incorrect_pick_result();
    $heading = $email_format_service->get_pick_result_heading($pick_result);
    $this->assertEquals(
      'You picked TEAM 2... but TEAM 1 won the round!',
      $heading
    );
  }
}
