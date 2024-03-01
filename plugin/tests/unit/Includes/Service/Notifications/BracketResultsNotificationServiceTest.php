<?php
namespace WStrategies\BMB\tests\unit\Includes\Service\Notifications;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Service\BracketMatchService;
use WStrategies\BMB\Includes\Factory\MatchPickResultFactory;
use WStrategies\BMB\Includes\Repository\DateTimePostMetaRepo;
use WStrategies\BMB\Includes\Service\Notifications\EmailServiceInterface;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsEmailFormatService;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsFilterService;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationService;
use WStrategies\BMB\Includes\Service\Notifications\MatchPickResultNotificationService;

class BracketResultsNotificationServiceTest extends TestCase {
  // public function test_should_send_notification_if_updated_results_contain_winning_team() {
  //   $notification_service = new BracketResultsNotificationService([
  //     'email_service' => $this->createMock(EmailServiceInterface::class),
  //   ]);

  //   // have a bracket with 1 play
  //   // have a play with 1 pick, team 1 wins over team 2, winning team is team 1
  //   // update results for bracket, team 1 wins over team 2
  //   //
  // }

  public function test_should_send_email_when_match_pick_result_is_not_null() {
    $bracket = new Bracket(['id' => 1]);
    $plays = [new BracketPlay(['picks' => []])];
    $play_repo = $this->createMock(PlayRepo::class);
    $play_repo->method('get_all')->willReturn($plays);
    $email_format_service = $this->createMock(
      BracketResultsEmailFormatService::class
    );
    $match_pick_result_factory = $this->createMock(
      MatchPickResultFactory::class
    );
    $results_filter_service = $this->createMock(
      BracketResultsFilterService::class
    );
    $results_filter_service
      ->method('filter_results_updated_at_time')
      ->willReturn([]);
    $match_service = $this->createMock(BracketMatchService::class);
    $match_service->method('matches_from_picks')->willReturn([]);
    $result_to_send = $this->createMock(MatchPickResult::class);
    // Expecting the send_email method to be called once with the result to send
    $email_format_service
      ->expects($this->once())
      ->method('send_email')
      ->with($plays[0], $result_to_send);

    $match_pick_result_notification_service = $this->createMock(
      MatchPickResultNotificationService::class
    );
    $match_pick_result_notification_service
      ->method('get_match_pick_result_for_play')
      ->willReturn($result_to_send);

    $notification_service = new BracketResultsNotificationService([
      'email_service' => $this->createMock(EmailServiceInterface::class),
      'play_repo' => $play_repo,
      'results_sent_at_repo' => $this->createMock(DateTimePostMetaRepo::class),
      'results_filter_service' => $results_filter_service,
      'match_service' => $match_service,
      'match_pick_result_factory' => $this->createMock(
        MatchPickResultFactory::class
      ),
      'email_format_service' => $email_format_service,
      'match_pick_result_notification_service' => $match_pick_result_notification_service,
    ]);

    $notification_service->notify_bracket_results_updated($bracket);
  }
  public function test_should_not_send_email_when_match_pick_result_is_null() {
    $bracket = new Bracket(['id' => 1]);
    $plays = [new BracketPlay(['picks' => []])];
    $play_repo = $this->createMock(PlayRepo::class);
    $play_repo->method('get_all')->willReturn($plays);
    $email_format_service = $this->createMock(
      BracketResultsEmailFormatService::class
    );
    $match_pick_result_factory = $this->createMock(
      MatchPickResultFactory::class
    );
    $results_filter_service = $this->createMock(
      BracketResultsFilterService::class
    );
    $results_filter_service
      ->method('filter_results_updated_at_time')
      ->willReturn([]);
    $match_service = $this->createMock(BracketMatchService::class);
    $match_service->method('matches_from_picks')->willReturn([]);
    $match_pick_result_notification_service = $this->createMock(
      MatchPickResultNotificationService::class
    );
    $match_pick_result_notification_service
      ->method('get_match_pick_result_for_play')
      ->willReturn(null);

    $notification_service = new BracketResultsNotificationService([
      'email_service' => $this->createMock(EmailServiceInterface::class),
      'play_repo' => $play_repo,
      'results_sent_at_repo' => $this->createMock(DateTimePostMetaRepo::class),
      'results_filter_service' => $results_filter_service,
      'match_service' => $match_service,
      'match_pick_result_factory' => $this->createMock(
        MatchPickResultFactory::class
      ),
      'email_format_service' => $email_format_service,
      'match_pick_result_notification_service' => $match_pick_result_notification_service,
    ]);

    $email_format_service->expects($this->never())->method('send_email');

    $notification_service->notify_bracket_results_updated($bracket);
  }

  public function test_should_set_results_sent_at_to_now_after_sending_notifications() {
    $bracket = new Bracket(['id' => 1]);
    $plays = [new BracketPlay(['picks' => []])];
    $play_repo = $this->createMock(PlayRepo::class);
    $play_repo->method('get_all')->willReturn($plays);
    $email_format_service = $this->createMock(
      BracketResultsEmailFormatService::class
    );
    $match_pick_result_factory = $this->createMock(
      MatchPickResultFactory::class
    );
    $results_filter_service = $this->createMock(
      BracketResultsFilterService::class
    );
    $results_filter_service
      ->method('filter_results_updated_at_time')
      ->willReturn([]);
    $match_service = $this->createMock(BracketMatchService::class);
    $match_service->method('matches_from_picks')->willReturn([]);
    $match_pick_result_notification_service = $this->createMock(
      MatchPickResultNotificationService::class
    );
    $match_pick_result_notification_service
      ->method('get_match_pick_result_for_play')
      ->willReturn(null);

    $results_sent_at_repo = $this->createMock(DateTimePostMetaRepo::class);
    $results_sent_at_repo->expects($this->once())->method('set_to_now');

    $notification_service = new BracketResultsNotificationService([
      'email_service' => $this->createMock(EmailServiceInterface::class),
      'play_repo' => $play_repo,
      'results_sent_at_repo' => $results_sent_at_repo,
      'results_filter_service' => $results_filter_service,
      'match_service' => $match_service,
      'match_pick_result_factory' => $this->createMock(
        MatchPickResultFactory::class
      ),
      'email_format_service' => $email_format_service,
      'match_pick_result_notification_service' => $match_pick_result_notification_service,
    ]);

    $notification_service->notify_bracket_results_updated($bracket);
  }
}
