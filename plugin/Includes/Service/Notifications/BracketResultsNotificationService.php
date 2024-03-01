<?php
namespace WStrategies\BMB\Includes\Service\Notifications;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Factory\MatchPickResultFactory;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketResultsRepo;
use WStrategies\BMB\Includes\Repository\DateTimePostMetaRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\BracketMatchService;
use WStrategies\BMB\Includes\Service\MatchPickResultService;

class BracketResultsNotificationService implements
  BracketResultsNotificationServiceInterface {
  protected EmailServiceInterface $email_service;
  protected BracketMatchService $match_service;
  protected MatchPickResultFactory $match_pick_result_factory;
  protected MatchPickResultService $match_pick_result_service;

  protected BracketRepo $bracket_repo;
  protected PlayRepo $play_repo;
  private BracketResultsEmailFormatService $email_format_service;
  private DateTimePostMetaRepo $results_sent_at_repo;

  public function __construct($args = []) {
    $this->email_service =
      $args['email_service'] ?? new MailchimpEmailService();
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->match_service = $args['match_service'] ?? new BracketMatchService();
    $this->match_pick_result_factory =
      $args['match_pick_result_factory'] ?? new MatchPickResultFactory();
    $this->match_pick_result_service =
      $args['match_pick_result_service'] ?? new MatchPickResultService();
    $this->email_format_service =
      $args['email_format_service'] ??
      new BracketResultsEmailFormatService(
        $this->play_repo,
        $this->email_service
      );
    $this->results_sent_at_repo =
      $args['results_sent_at_repo'] ??
      new DateTimePostMetaRepo(
        BracketResultsRepo::RESULTS_NOTIFICATIONS_SENT_AT_META_KEY
      );
  }

  // $ranked_play_teams = [5, 1, 0, 2, 3];
  // foreach result:
  //   if result is in $ranked_play_teams:

  // foreach NEW result:
  //   if my_team plays and my_team wins:
  //     “You picked {my_team} and they won!”
  //   if my_team plays and my_team loses:
  //     “You picked {my_team} but {winning_team} won the round!”

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
    $results = array_filter($results, function ($result) use (
      $results_sent_at
    ) {
      return $result->get_updated_at() > $results_sent_at;
    });
    $matches = $this->match_service->matches_from_picks($matches, $results);
    foreach ($plays as $play) {
      $match_pick_results = $this->match_pick_result_factory->create_match_pick_results(
        $matches,
        $play->picks
      );
      $result = $this->get_match_pick_result_for_play(
        $match_pick_results,
        $play
      );
      if ($result) {
        $this->email_format_service->send_email($play, $result);
      }

      // Send the email update
    }
    $this->results_sent_at_repo->set_to_now($bracket->id);
  }

  /**
   * @param array<MatchPickResult> $results
   */
  public function get_match_pick_result_for_play(
    array $results,
    BracketPlay $play
  ): MatchPickResult|null {
    $final_winning_team_id = $play->get_winning_team()->id;
    if (!$final_winning_team_id) {
      throw new \Exception('Winning team id is required');
    }
    return $this->get_match_pick_result_for_single_team(
      $results,
      $final_winning_team_id
    );
  }

  /**
   * This function returns the match pick result given a single team id (assumed to be the final winning pick of a play)
   * @param array<MatchPickResult> $results
   * @param int $team_id
   * @return MatchPickResult|null
   */
  public function get_match_pick_result_for_single_team(
    array $results,
    int $team_id
  ): MatchPickResult|null {
    $result = null;
    $winning_team_map = $this->match_pick_result_service->get_winning_team_map(
      $results
    );
    $losing_team_map = $this->match_pick_result_service->get_losing_team_map(
      $results
    );
    if (isset($winning_team_map[$team_id])) {
      $result = $winning_team_map[$team_id];
    } elseif (isset($losing_team_map[$team_id])) {
      $result = $losing_team_map[$team_id];
    }
    return $result;
  }

  /**
   * This function returns the match pick result given an array of team ids.
   * team_ids is assumed to be a play's winning picks in ranked order. For example [5, 1, 0, 2, 3]
   * where team 5 is the final winning team, team 1 is the second place team, and so on.
   */
  public function get_match_pick_result_for_many_teams(
    array $results,
    array $team_ids
  ) {
    $result = null;
    $winning_team_map = $this->match_pick_result_service->get_winning_team_map(
      $results
    );
    $losing_team_map = $this->match_pick_result_service->get_losing_team_map(
      $results
    );
    foreach ($team_ids as $team_id) {
      if (isset($winning_team_map[$team_id])) {
        $result = $winning_team_map[$team_id];
        break;
      } elseif (isset($losing_team_map[$team_id])) {
        $result = $losing_team_map[$team_id];
        break;
      }
    }
    return $result;
  }

  public function get_pick_result_heading(MatchPickResult $result): string {
    $picked_team = strtoupper($result->picked_team->name);
    $winning_team = strtoupper($result->winning_team->name);
    if ($result->correct_picked()) {
      return 'You picked ' . $picked_team . '... and they won!';
    } else {
      return 'You picked ' .
        $picked_team .
        '... but ' .
        $winning_team .
        ' won the round!';
    }
  }
}
