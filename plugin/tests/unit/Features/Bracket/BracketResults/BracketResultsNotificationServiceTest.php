<?php
namespace WStrategies\BMB\tests\unit\Features\Bracket\BracketResults;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\Notifications\Application\NotificationDispatcher;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Factory\PickResultFactory;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\DateTimePostMetaRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\BracketMatchService;
use WStrategies\BMB\Features\Bracket\BracketResults\BracketResultsFilterService;
use WStrategies\BMB\Features\Bracket\BracketResults\BracketResultsNotificationService;
use WStrategies\BMB\Includes\Service\PickResultService;
use WStrategies\BMB\Includes\Repository\Fakes\UserRepoFake;

class BracketResultsNotificationServiceTest extends TestCase {
  public function test_should_send_notification_when_match_pick_result_is_not_null() {
    $bracket = new Bracket(['id' => 1]);
    $bracket_repo = $this->createStub(BracketRepo::class);
    $plays = [new Play(['picks' => [], 'author' => 1])];
    $play_repo = $this->createMock(PlayRepo::class);
    $play_repo->method('get_all')->willReturn($plays);

    $dispatcher = $this->createMock(NotificationDispatcher::class);
    $dispatcher->expects($this->once())->method('dispatch');

    $results_filter_service = $this->createMock(
      BracketResultsFilterService::class
    );
    $results_filter_service
      ->method('filter_results_updated_at_time')
      ->willReturn([$this->createStub(Pick::class)]);

    $match_service = $this->createMock(BracketMatchService::class);
    $match_service->method('matches_2d_from_picks')->willReturn([]);

    // Create a proper mock BracketMatch with teams
    $match = $this->createMock(BracketMatch::class);
    $winning_team = new Team(['id' => 1, 'name' => 'Team 1']);
    $match->method('get_winning_team')->willReturn($winning_team);

    // Create a proper mock PickResult with required properties
    $result_to_send = $this->createMock(PickResult::class);
    $result_to_send->match = $match;
    $result_to_send
      ->method('get_picked_team')
      ->willReturn(new Team(['id' => 2, 'name' => 'Team 2']));
    $result_to_send->method('picked_team_won')->willReturn(false);

    $pick_result_service = $this->createMock(PickResultService::class);
    $pick_result_service
      ->method('get_pick_result_for_play')
      ->willReturn($result_to_send);

    $notification_service = new BracketResultsNotificationService([
      'play_repo' => $play_repo,
      'bracket_repo' => $bracket_repo,
      'results_sent_at_repo' => $this->createMock(DateTimePostMetaRepo::class),
      'results_filter_service' => $results_filter_service,
      'match_service' => $match_service,
      'pick_result_factory' => $this->createMock(PickResultFactory::class),
      'pick_result_service' => $pick_result_service,
      'user_repo' => new UserRepoFake(),
      'dispatcher' => $dispatcher,
    ]);

    $notification_service->send_results_notifications_for_bracket($bracket);
  }

  public function test_should_not_send_notification_when_match_pick_result_is_null() {
    $bracket = new Bracket(['id' => 1]);
    $bracket_repo = $this->createStub(BracketRepo::class);
    $plays = [new Play(['picks' => [], 'author' => 1])];
    $play_repo = $this->createMock(PlayRepo::class);
    $play_repo->method('get_all')->willReturn($plays);

    $dispatcher = $this->createMock(NotificationDispatcher::class);
    $dispatcher->expects($this->never())->method('dispatch');

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
      'play_repo' => $play_repo,
      'bracket_repo' => $bracket_repo,
      'results_sent_at_repo' => $this->createMock(DateTimePostMetaRepo::class),
      'results_filter_service' => $results_filter_service,
      'match_service' => $match_service,
      'pick_result_factory' => $this->createMock(PickResultFactory::class),
      'pick_result_service' => $pick_result_service,
      'user_repo' => new UserRepoFake(),
      'dispatcher' => $dispatcher,
    ]);

    $notification_service->send_results_notifications_for_bracket($bracket);
  }

  public function test_should_set_results_sent_at_to_now_after_sending_notifications() {
    $bracket = new Bracket(['id' => 1]);
    $bracket_repo = $this->createStub(BracketRepo::class);
    $plays = [new Play(['picks' => [], 'author' => 1])];
    $play_repo = $this->createMock(PlayRepo::class);
    $play_repo->method('get_all')->willReturn($plays);

    $dispatcher = $this->createMock(NotificationDispatcher::class);

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
      'play_repo' => $play_repo,
      'bracket_repo' => $bracket_repo,
      'results_sent_at_repo' => $results_sent_at_repo,
      'results_filter_service' => $results_filter_service,
      'match_service' => $match_service,
      'pick_result_factory' => $this->createMock(PickResultFactory::class),
      'pick_result_service' => $pick_result_service,
      'user_repo' => new UserRepoFake(),
      'dispatcher' => $dispatcher,
    ]);

    $notification_service->send_results_notifications_for_bracket($bracket);
  }
}
