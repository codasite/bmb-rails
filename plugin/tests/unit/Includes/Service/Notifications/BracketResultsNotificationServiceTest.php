<?php
namespace WStrategies\BMB\tests\unit\Includes\Service\Notifications;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Factory\PickResultFactory;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\DateTimePostMetaRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\BracketMatchService;
use WStrategies\BMB\Features\Bracket\BracketResults\Fakes\BracketResultsNotificationListenerFake;
use WStrategies\BMB\Features\Bracket\BracketResults\BracketResultsFilterService;
use WStrategies\BMB\Features\Bracket\BracketResults\BracketResultsNotificationService;
use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
use WStrategies\BMB\Includes\Service\PickResultService;
use WStrategies\BMB\Includes\Repository\Fakes\UserRepoFake;

class BracketResultsNotificationServiceTest extends TestCase {
  public function test_should_send_email_when_match_pick_result_is_not_null() {
    $bracket = new Bracket(['id' => 1]);
    $bracket_repo = $this->createStub(BracketRepo::class);
    $plays = [new Play(['picks' => [], 'author' => 1])];
    $play_repo = $this->createMock(PlayRepo::class);
    $play_repo->method('get_all')->willReturn($plays);

    $notification_listener = new BracketResultsNotificationListenerFake();

    $results_filter_service = $this->createMock(
      BracketResultsFilterService::class
    );
    $results_filter_service
      ->method('filter_results_updated_at_time')
      ->willReturn([$this->createStub(Pick::class)]);

    $match_service = $this->createMock(BracketMatchService::class);
    $match_service->method('matches_2d_from_picks')->willReturn([]);
    $result_to_send = $this->createMock(PickResult::class);

    $pick_result_service = $this->createMock(PickResultService::class);
    $pick_result_service
      ->method('get_pick_result_for_play')
      ->willReturn($result_to_send);

    $notification_service = new BracketResultsNotificationService([
      'email_service' => $this->createMock(EmailServiceInterface::class),
      'play_repo' => $play_repo,
      'bracket_repo' => $bracket_repo,
      'results_sent_at_repo' => $this->createMock(DateTimePostMetaRepo::class),
      'results_filter_service' => $results_filter_service,
      'match_service' => $match_service,
      'match_pick_result_factory' => $this->createMock(
        PickResultFactory::class
      ),
      'listeners' => [$notification_listener],
      'pick_result_service' => $pick_result_service,
      'user_repo' => new UserRepoFake(),
    ]);

    $notification_service->send_results_notifications_for_bracket($bracket);
    $this->assertTrue($notification_listener->notify_was_called);
  }

  public function test_should_not_send_email_when_match_pick_result_is_null() {
    $bracket = new Bracket(['id' => 1]);
    $bracket_repo = $this->createStub(BracketRepo::class);
    $plays = [new Play(['picks' => [], 'author' => 1])];
    $play_repo = $this->createMock(PlayRepo::class);
    $play_repo->method('get_all')->willReturn($plays);

    $notification_listener = new BracketResultsNotificationListenerFake();

    $results_filter_service = $this->createMock(
      BracketResultsFilterService::class
    );
    $results_filter_service
      ->method('filter_results_updated_at_time')
      ->willReturn([]);

    $match_service = $this->createMock(BracketMatchService::class);
    $match_service->method('matches_2d_from_picks')->willReturn([]);
    $pick_result_service = $this->createMock(PickResultService::class);
    $pick_result_service->method('get_pick_result_for_play')->willReturn(null);

    $notification_service = new BracketResultsNotificationService([
      'email_service' => $this->createMock(EmailServiceInterface::class),
      'play_repo' => $play_repo,
      'bracket_repo' => $bracket_repo,
      'results_sent_at_repo' => $this->createMock(DateTimePostMetaRepo::class),
      'results_filter_service' => $results_filter_service,
      'match_service' => $match_service,
      'match_pick_result_factory' => $this->createMock(
        PickResultFactory::class
      ),
      'listeners' => [$notification_listener],
      'pick_result_service' => $pick_result_service,
      'user_repo' => new UserRepoFake(),
    ]);

    $notification_service->send_results_notifications_for_bracket($bracket);
    $this->assertFalse($notification_listener->notify_was_called);
  }

  public function test_should_set_results_sent_at_to_now_after_sending_notifications() {
    $bracket = new Bracket(['id' => 1]);
    $bracket_repo = $this->createStub(BracketRepo::class);
    $plays = [new Play(['picks' => [], 'author' => 1])];
    $play_repo = $this->createMock(PlayRepo::class);
    $play_repo->method('get_all')->willReturn($plays);

    $notification_listener = new BracketResultsNotificationListenerFake();

    $results_filter_service = $this->createMock(
      BracketResultsFilterService::class
    );
    $results_filter_service
      ->method('filter_results_updated_at_time')
      ->willReturn([$this->createStub(Pick::class)]);

    $match_service = $this->createMock(BracketMatchService::class);
    $match_service->method('matches_2d_from_picks')->willReturn([]);
    $pick_result_service = $this->createMock(PickResultService::class);
    $pick_result_service->method('get_pick_result_for_play')->willReturn(null);

    $results_sent_at_repo = $this->createMock(DateTimePostMetaRepo::class);
    $results_sent_at_repo->expects($this->once())->method('set_to_now');

    $notification_service = new BracketResultsNotificationService([
      'email_service' => $this->createMock(EmailServiceInterface::class),
      'play_repo' => $play_repo,
      'bracket_repo' => $bracket_repo,
      'results_sent_at_repo' => $results_sent_at_repo,
      'results_filter_service' => $results_filter_service,
      'match_service' => $match_service,
      'match_pick_result_factory' => $this->createMock(
        PickResultFactory::class
      ),
      'listeners' => [$notification_listener],
      'pick_result_service' => $pick_result_service,
      'user_repo' => new UserRepoFake(),
    ]);

    $notification_service->send_results_notifications_for_bracket($bracket);
  }
}
