<?php

require_once('class-wp-bracket-builder-email-service-interface.php');
require_once( plugin_dir_path( dirname( __FILE__, 1 ) ) . 'repository/class-wp-bracket-builder-bracket-play-repo.php' );

class Wp_Bracket_Builder_Notification_Service {

    protected Wp_Bracket_Builder_Email_Service_Interface $email_service;

    public function __construct(Wp_Bracket_Builder_Email_Service_Interface $email_service) {
        $this->email_service = $email_service;
    }

    private function get_team($team_id) {
        global $wpdb;

        $query = "
            SELECT team.name as name FROM wp_bracket_builder_teams team
            WHERE team.id = %d;
        ";

        $prepared_query = $wpdb->prepare($query, $team_id);
        $result = $wpdb->get_results($prepared_query);
        return $result;
    }

    public function get_picks_for_tournament($tournament_id) {
        global $wpdb;

        /**
         * @var $query string
         * Sorts picks for tournament by round index and
         * returns the author's email, display name, the
         * winning pick and the winning result.
         */
        $query = "
            SELECT author.user_email, author.display_name, tournament.post_id as tournament_id, pick.round_index, pick.winning_team_id as user_pick, result.winning_team_id as winner
            FROM wp_bracket_builder_tournaments tournament
            JOIN wp_bracket_builder_plays play
            ON tournament.id = play.bracket_tournament_id
            JOIN wp_bracket_builder_match_picks pick
            ON pick.bracket_play_id = play.id
            JOIN wp_bracket_builder_tournament_results result
            ON pick.round_index = result.round_index
            AND pick.match_index = result.match_index
            AND result.bracket_tournament_id = tournament.id
            JOIN wp_posts post
            ON post.ID = play.post_id
            JOIN wp_users author
            ON author.ID = post.post_author
            WHERE tournament.post_id = %d
            ORDER BY result.round_index;
        ";

        $prepared_query = $wpdb->prepare($query, $tournament_id);
        $results = $wpdb->get_results($prepared_query);
        return $results;

    }

    public function get_last_round_picks_for_tournament($tournament_id) {
        $picks = $this->get_picks_for_tournament($tournament_id);
        $last_round_index = $picks[count($picks) - 1]->round_index;
        $last_round_picks = array_filter($picks, function($pick) use ($last_round_index) {
            return $pick->round_index === $last_round_index;
        });
        return $last_round_picks;
    }

    public function send_tournament_result_email_update($tournament_id) {
        $play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();

        
        // create mailchimp message template (fails if template already exists)
        $background_image_url = 'https://backmybracket.com/wp-content/uploads/2023/10/bracket_bg.png';
        $logo_url = 'https://backmybracket.com/wp-content/uploads/2023/10/logo_dark.png';
        $heading = 'Back My Bracket Update';
        $message = 'Back My Bracket Update';
        $subtext = $message;
        $button_url = get_permalink($tournament_id) . '/leaderboard';
        $button_text = 'click me';

        ob_start();
        include plugin_dir_path( dirname( __FILE__, 2 ) ) . 'email/templates/play-scored.php';
        $html = ob_get_clean();

        // file_put_contents(plugin_dir_path( dirname( __FILE__, 2 ) ) . 'email/demo.html', $html);

        $template_name = 'tournament-update';
        $from_email = MAILCHIMP_FROM_EMAIL;
        $from_name = 'Back My Bracket';
        $subject = 'Update from Back My Bracket';
        $code = $html;
        $text = 'tournament update';
        $publish = true;
        $labels = ['tournament', 'update'];

        $response1 = $this->email_service->create_template(
            $template_name, // name
            $from_email, // from_email
            $from_name, // from_name
            $subject, // subject
            $code, // code
            $text, // text
            $publish, // publish
            $labels // labels
        );

        // get last round picks for the tournament
        $picks = $this->get_last_round_picks_for_tournament($tournament_id);

        foreach($picks as $pick) {
            $to_email = $pick->user_email;
            $to_email = 'barry@wstrategies.co';
            $to_name = $pick->display_name;
            $from_email = MAILCHIMP_FROM_EMAIL;
            $subject = 'Back My Bracket Notification';
            $user_pick = $this->get_team($pick->user_pick);
            $winner = $this->get_team($pick->winner);
            $tournament_id = $pick->tournament_id;

            $template_content = array(
                array(
                    'header' => 'You picked ' . $user_pick . '. Winner: ' . $winner . '.',
                    'subtext' => 'Click below to see the leaderboard.',
                    'button_text' => 'View Leaderboard',
                ),
            );
            $message = array(
                'to'=>array(
                    array(
                        'email'=>$to_email,
                        'name'=>$to_name,
                    ),
                ),
            );
            // send template
            $response2 = $this->email_service->send_template(
                $template_name, // template_name
                $template_content, // template_content
                $message // message
            );

            print_r($response2);
            
            // $response = $this->email_service->send_message(
            //     $from_email,
            //     $to_email,
            //     $to_name,
            //     $subject,
            //     $message,
            //     $html,
            // );

            // print_r($response);
        }
    }
}

?>