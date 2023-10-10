<?php

require_once('class-wp-bracket-builder-email-service-interface.php');
require_once('class-wp-bracket-builder-mailchimp-email-service.php');
require_once('class-wp-bracket-builder-notification-service-interface.php');
require_once(plugin_dir_path(dirname(__FILE__, 1)) . 'repository/class-wp-bracket-builder-bracket-play-repo.php');
require_once(plugin_dir_path(dirname(__FILE__, 1)) . 'repository/class-wp-bracket-builder-bracket-team-repo.php');
require_once(plugin_dir_path(dirname(__FILE__, 1)) . 'repository/class-wp-bracket-builder-bracket-tournament-repo.php');

class Wp_Bracket_Builder_Notification_Service implements Wp_Bracket_Builder_Notification_Service_Interface {

    protected Wp_Bracket_Builder_Email_Service_Interface $email_service;

    protected Wp_Bracket_Builder_Bracket_Tournament_Repository $tournament_repo;

    public function __construct() {
        $this->email_service = new Wp_Bracket_Builder_Mailchimp_Email_Service(
            array(
                'api_key' => MAILCHIMP_API_KEY,
                'from_email' => MAILCHIMP_FROM_EMAIL,
            )
        );
        $this->tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
    }

    public function get_last_round_picks_for_tournament($tournament_id, $final_round_pick) {
        global $wpdb;

        /**
         * @var $query string
         * Sorts picks for tournament by round index and
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
        WHERE play.bracket_tournament_post_id = %d
        GROUP BY post.post_author;
        ";

        $prepared_query = $wpdb->prepare($query, $final_round_pick->round_index, $final_round_pick->match_index, $tournament_id);
        $results = $wpdb->get_results($prepared_query);
        return $results;
    }

    public function notify_tournament_results_updated($tournament_id): void {
        $play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();
        $team_repo = new Wp_Bracket_Builder_Bracket_Team_Repository();

        $tournament = $this->tournament_repo->get($tournament_id);
        $final_round_pick = end($tournament->results);
        $user_picks = $this->get_last_round_picks_for_tournament($tournament_id, $final_round_pick);

        foreach ($user_picks as $pick) {
            $to_email = $pick->email;
            $to_email = 'test@wstrategies.co';
            $to_name = $pick->name;
            $subject = 'Back My Bracket Notification';
            $user_bracket_pick = $team_repo->get_team($pick->winning_team_id);
            $user_pick = strtoupper($user_bracket_pick->name);
            $winner_bracket_pick = $team_repo->get_team($final_round_pick->winning_team_id);
            $winner = strtoupper($winner_bracket_pick->name);
            $tournament_url = get_permalink($tournament_id) . '/leaderboard';
            $message = array(
                'to' => array(
                    array(
                        'email' => $to_email,
                        'name' => $to_name,
                    ),
                ),
            );

            // Generate html content for email
            $background_image_url = 'https://backmybracket.com/wp-content/uploads/2023/10/bracket_bg.png';
            $logo_url = 'https://backmybracket.com/wp-content/uploads/2023/10/logo_dark.png';
            if ($user_pick == $winner) {
                $heading = 'You picked ' . $user_pick . '... and they won!';
            } else {
                $heading = 'You picked ' . $user_pick . ', but ' . $winner . ' won the round...';
            }
            $button_url = get_permalink($tournament_id) . '/leaderboard';
            $button_text = 'View Tournament';

            ob_start();
            include plugin_dir_path(dirname(__FILE__, 2)) . 'email/templates/play-scored.php';
            $html = ob_get_clean();

            // send the email
            $response = $this->email_service->send(
                $to_email,
                $to_name,
                $subject,
                $message,
                $html,
            );
        }
    }
}
