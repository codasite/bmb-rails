<?php
namespace WStrategies\BMB\Includes\Service\Notifications;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Factory\PickResultFactory;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketResultsRepo;
use WStrategies\BMB\Includes\Repository\DateTimePostMetaRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\BracketMatchService;
use WStrategies\BMB\Includes\Service\PickResultService;

class BracketResultsNotificationService implements
  BracketResultsNotificationServiceInterface {
  protected BracketMatchService $match_service;
  protected PickResultFactory $pick_result_factory;
  protected PickResultService $pick_result_service;
  protected BracketRepo $bracket_repo;
  protected PlayRepo $play_repo;
  private BracketResultsEmailFormatService $email_format_service;
  private DateTimePostMetaRepo $results_sent_at_repo;
  private BracketResultsFilterService $results_filter_service;

  public function __construct($args = []) {
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->match_service = $args['match_service'] ?? new BracketMatchService();
    $this->pick_result_factory =
      $args['pick_result_factory'] ?? new PickResultFactory();
    $this->pick_result_service =
      $args['pick_result_service'] ?? new PickResultService();
    $this->email_format_service =
      $args['email_format_service'] ??
      new BracketResultsEmailFormatService(
        $args['email_service'] ?? new MailchimpEmailService()
      );
    $this->results_sent_at_repo =
      $args['results_sent_at_repo'] ??
      new DateTimePostMetaRepo(
        BracketResultsRepo::RESULTS_NOTIFICATIONS_SENT_AT_META_KEY
      );
    $this->results_filter_service =
      $args['results_filter_service'] ?? new BracketResultsFilterService();
  }

  /**
   * @throws \Exception
   */
  public function notify_bracket_results_updated(
    Bracket|int|null $bracket
  ): void {
    if (!$bracket) {
      return;
    }
    if (is_numeric($bracket)) {
      $bracket = $this->bracket_repo->get($bracket);
    }
    $plays = $this->play_repo->get_all(
      ['bracket_id' => $bracket->id],
      ['fetch_bracket' => false]
    );
    $matches = $bracket->get_matches();
    $results = $bracket->get_picks();
    $results_sent_at = $this->results_sent_at_repo->get($bracket->id);
    $results = $this->results_filter_service->filter_results_updated_at_time(
      $results,
      $results_sent_at
    );
    $matches = $this->match_service->matches_from_picks($matches, $results);
    foreach ($plays as $play) {
      $match_pick_results = $this->pick_result_factory->create_match_pick_results(
        $matches,
        $play->picks
      );
      $result = $this->pick_result_service->get_pick_result_for_play(
        $match_pick_results,
        $play
      );
      if ($result) {
        $this->email_format_service->send_email($play, $result);
      }
    }
    $this->results_sent_at_repo->set_to_now($bracket->id);
  }
}
