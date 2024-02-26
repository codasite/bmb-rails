<?php
namespace WStrategies\BMB\Includes\Service\Notifications;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;

class BracketResultsNotificationService implements
  BracketResultsNotificationServiceInterface {
  protected EmailServiceInterface $email_service;

  protected BracketRepo $bracket_repo;
  protected PlayRepo $play_repo;

  public function __construct($args = []) {
    $this->email_service =
      $args['email_service'] ?? new MailchimpEmailService();
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
  }

  public function get_updated_bracket_results(
    Bracket|int|null $old_bracket,
    Bracket|int|null $new_bracket
  ): void {
    $new_results = $new_bracket->results;
    $old_results = $old_bracket->results;
    $updated_results = [];
    foreach ($new_results as $new_result) {
      foreach ($old_results as $old_result) {
        if (
          $new_result->round_index === $old_result->round_index &&
          $new_result->match_index === $old_result->match_index
        ) {
          if ($new_result->winning_team_id !== $old_result->winning_team_id) {
            $updated_results[] = $new_result;
          }
        }
      }
    }
  }

  // foreach NEW result:
  //   if my_team plays and my_team wins:
  //     “You picked {my_team} and they won!”
  //   if my_team plays and my_team loses:
  //     “You picked {my_team} but {winning_team} won the round!”

  public function notify_bracket_results_updated(
    Bracket|int|null $bracket
  ): void {
    if (!$bracket) {
      return;
    }
    if (is_numeric($bracket)) {
      $bracket = $this->bracket_repo->get($bracket);
    }
    $bracket_id = $bracket->id;
    $winning_pick = $bracket->get_last_result();
    $user_picks = $this->play_repo->get_user_picks_for_result(
      $bracket,
      $winning_pick
    );

    foreach ($user_picks as $user_pick) {
      $user = get_user_by('id', $user_pick['user_id']);
      $pick = $this->play_repo->pick_repo->get_pick($user_pick['pick_id']);
      $to_email = $user->user_email;
      $to_name = $user->display_name;
      $subject = 'Bracket Results Updated';
      $message = [
        'to' => [
          [
            'email' => $to_email,
            'name' => $to_name,
          ],
        ],
      ];

      // Generate html content for email
      $heading = $this->get_pick_result_heading($pick, $winning_pick);
      $button_url = get_permalink($user_pick['play_id']) . 'view';
      $button_text = 'View Bracket';

      $html = BracketEmailTemplate::render($heading, $button_url, $button_text);

      // send the email
      $response = $this->email_service->send(
        $to_email,
        $to_name,
        $subject,
        $message,
        $html
      );
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
