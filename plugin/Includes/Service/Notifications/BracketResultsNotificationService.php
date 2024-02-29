<?php
namespace WStrategies\BMB\Includes\Service\Notifications;

use DateTimeImmutable;
use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Factory\MatchPickResultFactory;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketResultsRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
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
    $results_sent_at = $this->get_results_sent_at($bracket);
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
      $result = $this->get_result_notification_for_play(
        $match_pick_results,
        $play
      );
      if ($result) {
        $this->notify_play_result($play, $result);
      }

      // Send the email update
    }
    update_post_meta(
      $bracket->id,
      BracketResultsRepo::RESULTS_NOTIFICATIONS_SENT_AT_META_KEY,
      (new DateTimeImmutable())->format('Y-m-d H:i:s')
    );
  }

  /**
   * @param array<MatchPickResult> $results
   */
  public function get_result_notification_for_play(
    array $results,
    BracketPlay $play
  ): MatchPickResult|null {
    $result = null;
    $final_winning_team = $play->get_winning_team();
    foreach ($results as $match_pick_result) {
      // if final_winning_team is in the match_pick_result
      if (
        $match_pick_result->winning_team->id === $final_winning_team->id ||
        $match_pick_result->losing_team->id === $final_winning_team->id
      ) {
        $result = $match_pick_result;
        break;
      }
    }
    return $result;
  }

  public function get_pick_result_heading(MatchPickResult $result): string {
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

  private function notify_play_result(
    BracketPlay $play,
    MatchPickResult $result
  ) {
  }

  /**
   * @param Bracket|int|null $bracket
   *
   * @return DateTimeImmutable
   * @throws \Exception
   */
  public function get_results_sent_at(
    Bracket|int|null $bracket
  ): DateTimeImmutable {
    $results_sent_at = get_post_meta(
      $bracket->id,
      BracketResultsRepo::RESULTS_NOTIFICATIONS_SENT_AT_META_KEY,
      true
    );
    if ($results_sent_at) {
      return new DateTimeImmutable($results_sent_at);
    } else {
      return new DateTimeImmutable('1970-01-01');
    }
  }

  public function send_email($user_pick, $winning_pick, BracketPlay $play) {
    // TODO fix this function
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
    $button_url = get_permalink($play->id) . 'view';
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
