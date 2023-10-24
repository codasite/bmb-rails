<?php

require_once 'class-wpbb-email-service-interface.php';
require_once 'class-wpbb-mailchimp-email-service.php';
require_once 'class-wpbb-notification-service-interface.php';
require_once plugin_dir_path(dirname(__FILE__, 1)) .
  'repository/class-wpbb-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 1)) .
  'repository/class-wpbb-bracket-team-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 1)) .
  'repository/class-wpbb-bracket-repo.php';

class Wpbb_NotificationService implements Wpbb_NotificationService_Interface {
  protected Wpbb_EmailServiceInterface $email_service;

  protected Wpbb_BracketRepo $bracket_repo;
  protected Wpbb_BracketPlayRepo $play_repo;

  public function __construct($args = []) {
    $this->email_service =
      $args['email_service'] ?? new Wpbb_MailchimpEmailService();
    $this->play_repo = $args['play_repo'] ?? new Wpbb_BracketPlayRepo();
    $this->bracket_repo = $args['bracket_repo'] ?? new Wpbb_BracketRepo();
  }

  public function notify_bracket_results_updated(
    Wpbb_Bracket|int|null $bracket
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
      $to_email = $user->user_email;
      $to_name = $user->display_name;
      $subject = 'Bracket Results Updated';
      $pick = $user_pick['pick'];
      $bracket_url = get_permalink($bracket_id) . '/leaderboard';
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
      $button_url = get_permalink($bracket_id) . '/leaderboard';
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
    Wpbb_MatchPick $pick,
    Wpbb_MatchPick $result
  ): string {
    if ($this->correct_picked($pick, $result)) {
      return 'You picked ' . $pick->winning_team->name . '... and they won!';
    } else {
      return 'You picked ' .
        $pick->winning_team->name .
        ', but ' .
        $result->winning_team->name .
        ' won the round...';
    }
  }

  public function correct_picked(Wpbb_MatchPick $pick, Wpbb_MatchPick $result) {
    return $pick->winning_team_id === $result->winning_team_id;
  }
}
