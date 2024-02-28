<?php
namespace WStrategies\BMB\Includes\Service\Notifications;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Factory\MatchPickResultFactory;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Service\BracketMatchService;

class BracketResultsNotificationService implements
  BracketResultsNotificationServiceInterface {
  protected EmailServiceInterface $email_service;
  protected BracketMatchService $match_service;
  protected MatchPickResultFactory $match_pick_result_factory;

  protected BracketRepo $bracket_repo;
  protected PlayRepo $play_repo;

  public function __construct($args = []) {
    $this->email_service =
      $args['email_service'] ?? new MailchimpEmailService();
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->match_service = $args['match_service'] ?? new BracketMatchService();
    $this->match_pick_result_factory =
      $args['match_pick_result_factory'] ?? new MatchPickResultFactory();
  }

  // $ranked_play_teams = [5, 1, 0, 2, 3];
  // foreach result:
  //   if result is in $ranked_play_teams:

  // foreach NEW result:
  //   if my_team plays and my_team wins:
  //     “You picked {my_team} and they won!”
  //   if my_team plays and my_team loses:
  //     “You picked {my_team} but {winning_team} won the round!”

  public function get_result_notification_for_play(
    array $results,
    BracketPlay $play
  ) {
  }

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
    $matches = $this->match_service->matches_from_picks($matches, $results);
    foreach ($plays as $play) {
      $match_pick_results = $this->match_pick_result_factory->create_match_pick_results(
        $matches,
        $play->picks
      );
      // TODO: find the match pick result to notify for
      // Send the email update
    }
  }

  public function get_pick_result_heading(
    MatchPick $pick,
    MatchPick $correct_pick
  ): string {
    $picked_team = strtoupper($pick->winning_team->name);
    $correct_team = strtoupper($correct_pick->winning_team->name);
    if ($this->correct_picked($pick, $correct_pick)) {
      return 'You picked ' . $picked_team . '... and they won!';
    } else {
      return 'You picked ' .
        $picked_team .
        ', but ' .
        $correct_team .
        ' won the round...';
    }
  }

  public function correct_picked(
    MatchPick $pick,
    MatchPick $correct_pick
  ): bool {
    return $pick->winning_team_id === $correct_pick->winning_team_id;
  }
}
