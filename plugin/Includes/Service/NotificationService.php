<?php
namespace WStrategies\BMB\Includes\Service;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;

class NotificationService implements NotificationServiceInterface {
  protected EmailServiceInterface $email_service;

  protected BracketRepo $bracket_repo;
  protected BracketPlayRepo $play_repo;

  public function __construct($args = []) {
    $this->email_service =
      $args['email_service'] ?? new MailchimpEmailService();
    $this->play_repo = $args['play_repo'] ?? new BracketPlayRepo();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
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
    $bracket_id = $bracket->id;
    $winning_pick = $bracket->get_last_result();
    $user_picks = $this->play_repo->get_user_picks_for_result(
      $bracket,
      $winning_pick
    );

    foreach ($user_picks as $user_pick) {
      $user = get_user_by('id', $user_pick['user_id']);
      $pick = $this->play_repo->get_pick($user_pick['pick_id']);
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
      $background_image_url =
        'https://backmybracket.com/wp-content/uploads/2023/10/bracket_bg.png';
      $logo_url =
        'https://backmybracket.com/wp-content/uploads/2023/10/logo_dark.png';
      $heading = $this->get_pick_result_heading($pick, $winning_pick);
      $button_url = get_permalink($user_pick['play_id']) . 'view';
      $button_text = 'View Bracket';

      ob_start();
      include plugin_dir_path(dirname(__FILE__, 2)) .
        'email/templates/play-scored.php';
      $html = ob_get_clean();

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

  public function correct_picked(MatchPick $pick, MatchPick $correct_pick) {
    return $pick->winning_team_id === $correct_pick->winning_team_id;
  }
}
