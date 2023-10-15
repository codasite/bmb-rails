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

class Wpbb_Notification_Service implements Wpbb_Notification_Service_Interface {
  protected Wpbb_Email_Service_Interface $email_service;

  protected Wpbb_BracketRepo $bracket_repo;

  public function __construct($args = []) {
    $this->email_service =
      $args['email_service'] ?? new Wpbb_Mailchimp_Email_Service();
    $this->bracket_repo = $args['bracket_repo'] ?? new Wpbb_BracketRepo();
  }

  public function get_last_round_picks_for_bracket(
    $bracket_id,
    $final_round_pick
  ) {
    global $wpdb;

    /**
     * @var $query string
     * Sorts picks for bracket by round index and
     * returns the author's email, display name, the
     * winning pick and the winning result.
     */

    $query = "
        SELECT author.user_email as email, author.display_name as name, pick.winning_team_id as winning_team_id
        FROM wp_bracket_builder_plays play
        JOIN wp_bracket_builder_match_picks pick 
        ON pick.bracket_play_id = play.id
        AND pick.round_index = %d
        AND pick.match_index = %d
        JOIN wp_posts post
        ON post.ID = play.post_id
        JOIN wp_users author
        ON author.ID = post.post_author
        WHERE play.bracket_post_id = %d
        GROUP BY post.post_author;
        ";

    $prepared_query = $wpdb->prepare(
      $query,
      $final_round_pick->round_index,
      $final_round_pick->match_index,
      $bracket_id
    );
    $results = $wpdb->get_results($prepared_query);
    return $results;
  }

  public function notify_bracket_results_updated($bracket_id): void {
    $play_repo = new Wpbb_BracketPlayRepo();
    $team_repo = new Wpbb_BracketTeamRepo();

    $bracket = $this->bracket_repo->get($bracket_id);
    $final_round_pick = end($bracket->results);
    $user_picks = $this->get_last_round_picks_for_bracket(
      $bracket_id,
      $final_round_pick
    );

    foreach ($user_picks as $pick) {
      $to_email = $pick->email;
      $to_name = $pick->name;
      $subject = 'Back My Bracket Notification';
      $user_bracket_pick = $team_repo->get($pick->winning_team_id);
      $user_pick = strtoupper($user_bracket_pick->name);
      $winner_bracket_pick = $team_repo->get(
        $final_round_pick->winning_team_id
      );
      $winner = strtoupper($winner_bracket_pick->name);
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
      if ($user_pick == $winner) {
        $heading = 'You picked ' . $user_pick . '... and they won!';
      } else {
        $heading =
          'You picked ' . $user_pick . ', but ' . $winner . ' won the round...';
      }
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
}
