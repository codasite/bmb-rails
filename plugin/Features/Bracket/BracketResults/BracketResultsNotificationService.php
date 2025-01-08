<?php
namespace WStrategies\BMB\Features\Bracket\BracketResults;

use WStrategies\BMB\Features\Bracket\BracketMetaConstants;
use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailService;
use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailServiceFactory;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Factory\PickResultFactory;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketResultsRepo;
use WStrategies\BMB\Includes\Repository\DateTimePostMetaRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\BracketMatchService;
use WStrategies\BMB\Includes\Service\Logger\SentryLogger;
use WStrategies\BMB\Includes\Service\PickResultService;

class BracketResultsNotificationService {
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
        $args['email_service'] ?? (new MailchimpEmailServiceFactory())->create()
      );
    $this->results_sent_at_repo =
      $args['results_sent_at_repo'] ??
      new DateTimePostMetaRepo(
        BracketResultsRepo::RESULTS_NOTIFICATIONS_SENT_AT_META_KEY
      );
    $this->results_filter_service =
      $args['results_filter_service'] ?? new BracketResultsFilterService();
  }

  public function send_bracket_results_notifications() {
    $brackets = $this->get_brackets_to_send_results_notifications_for();
    foreach ($brackets as $bracket) {
      try {
        $this->send_results_notifications_for_bracket($bracket);
      } catch (\Exception $e) {
        SentryLogger::log_error(
          'Error sending results notifications for bracket ' .
            $bracket->id .
            ': ' .
            $e->getMessage()
        );
      }
    }
  }

  public function get_brackets_to_send_results_notifications_for() {
    return $this->bracket_repo->get_all(
      [
        'meta_query' => [
          [
            'key' => BracketMetaConstants::SHOULD_NOTIFY_RESULTS_UPDATED,
            'value' => 1,
          ],
        ],
      ],
      ['fetch_matches' => true, 'fetch_results' => true]
    );
  }

  /**
   * @throws \Exception
   */
  public function send_results_notifications_for_bracket(
    Bracket|int|null $bracket
  ): void {
    $bracket = $this->get_bracket_obj($bracket);
    if (!$bracket) {
      return;
    }
    $matches_2d = $this->get_matches_2d($bracket);
    $filtered_results = $this->get_filtered_results($bracket);
    if (!empty($filtered_results)) {
      // We only want to send notifications for matches that have been updated recently
      $filtered_matches_2d = $this->get_filtered_matches_2d(
        $matches_2d,
        $filtered_results
      );
      $plays = $this->get_plays($bracket);
      foreach ($plays as $play) {
        $this->send_results_notifications_for_play($play, $filtered_matches_2d);
      }
      $this->results_sent_at_repo->set_to_now($bracket->id);
    }
    $this->bracket_repo->update($bracket, [
      BracketMetaConstants::SHOULD_NOTIFY_RESULTS_UPDATED => false,
    ]);
  }

  /**
   * @param Bracket $bracket
   *
   * @return array<array<BracketMatch>>
   */
  private function get_matches_2d(Bracket $bracket): array {
    return $this->match_service->matches_2d_from_picks(
      $bracket->get_matches(),
      $bracket->get_picks()
    );
  }

  /**
   * @param Bracket $bracket
   * @return array<Pick>
   */
  private function get_filtered_results(Bracket $bracket): array {
    $results_sent_at = $this->results_sent_at_repo->get($bracket->id);
    return $this->results_filter_service->filter_results_updated_at_time(
      $bracket->get_picks(),
      $results_sent_at
    );
  }

  /**
   * @param array<array<BracketMatch>> $matches_2d
   * @param array<Pick> $filtered_results
   *
   * @return array<array<BracketMatch>>
   */
  private function get_filtered_matches_2d(
    array $matches_2d,
    array $filtered_results
  ): array {
    return $this->match_service->filter_2d_array(
      $matches_2d,
      $filtered_results
    );
  }

  /**
   * @param Bracket $bracket
   * @return array<Play>
   */
  public function get_plays(Bracket $bracket): array {
    return $this->play_repo->get_all(
      ['bracket_id' => $bracket->id, 'author__not_in' => [0]],
      ['fetch_bracket' => false]
    );
  }

  /**
   * @param Play $play
   * @param array<array<BracketMatch>> $matches_2d
   *
   * @return PickResult
   */
  private function get_pick_result(Play $play, array $matches_2d): ?PickResult {
    $match_pick_results = $this->pick_result_factory->create_match_pick_results(
      $matches_2d,
      $play->picks
    );
    return $this->pick_result_service->get_pick_result_for_play(
      $match_pick_results,
      $play
    );
  }

  /**
   * @param Play $play
   * @param array<array<BracketMatch>> $filtered_matches_2d
   *
   * @return void
   */
  private function send_results_notifications_for_play(
    Play $play,
    array $filtered_matches_2d
  ): void {
    try {
      $result = $this->get_pick_result($play, $filtered_matches_2d);
      if ($result) {
        $this->email_format_service->send_email($play, $result);
      }
    } catch (\Exception $e) {
      SentryLogger::log_error(
        'Error sending results notification for play ' .
          $play->id .
          ' in bracket ' .
          $play->bracket_id .
          ': ' .
          $e->getMessage()
      );
    }
  }

  /**
   * @param Bracket|int|null $bracket
   * @return Bracket|null
   */
  public function get_bracket_obj(Bracket|int|null $bracket): Bracket|null {
    if (is_numeric($bracket)) {
      return $this->bracket_repo->get($bracket);
    }
    return $bracket;
  }
}
