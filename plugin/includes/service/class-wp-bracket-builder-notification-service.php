<?php

require_once('class-wp-bracket-builder-email-service-interface.php');
require_once( plugin_dir_path( dirname( __FILE__, 1 ) ) . 'repository/class-wp-bracket-builder-bracket-play-repo.php' );

class Wp_Bracket_Builder_Notification_Service {

    protected Wp_Bracket_Builder_Email_Service_Interface $email_service;

    public function __construct(Wp_Bracket_Builder_Email_Service_Interface $email_service) {
        $this->email_service = $email_service;
    }

    public function get_all_picks_by_author($user_id) {
        global $wpdb;

        // Get plays for author
        $subquery = "
            SELECT p.*
            FROM {$wpdb->prefix}bracket_builder_plays p
            JOIN wp_posts post ON p.post_id = post.id
            WHERE post.post_author = %d
        ";

        // Get picks for plays
        $query = "
            SELECT *
            FROM {$wpdb->prefix}bracket_builder_match_picks mp
            JOIN ( " . $subquery . " ) AS subquery
            ON mp.bracket_play_id = subquery.id
        ";

        // Get results for picks
        $query2 = "
            SELECT *
            FROM {$wpdb->prefix}bracket_builder_tournament_results tr
            JOIN ( " . $subquery . " ) AS subquery
            ON tr.bracket_tournament_id = subquery.bracket_tournament_id;
        ";

        // Get incorrect picks
        // This is not working
        // $outerquery = "
        //     SELECT result.*, pick.winning_team_id AS pick_winning_team_id
        //     FROM ( " . $query2 . " ) result
        //     JOIN ( " . $query . " ) pick
        //     ON result.round_index = pick.round_index
        //     AND result.match_index = pick.match_index
        //     AND result.bracket_tournament_id = pick.bracket_tournament_id
        //     WHERE result.winning_team_id != pick.winning_team_id;
        // ";

        $prepared_query = $wpdb->prepare($query, $user_id);
        $picks = $wpdb->get_results($prepared_query);

        $prepared_query2 = $wpdb->prepare($query2, $user_id);
        $results = $wpdb->get_results($prepared_query2);


        return array(
            'picks' => $picks,
            'results' => $results
        );
    }

    public function get_wrong_picks_by_author($user_id) {
        $response = $this->get_all_picks_by_author($user_id);
        $picks = $response['picks'];
        $results = $response['results'];

        $wrong_picks = array();

        for ($i = 0; $i < count($picks); $i++) {
            if ($picks[$i]->round_index == $results[$i]->round_index &&
                $picks[$i]->match_index == $results[$i]->match_index &&
                $picks[$i]->winning_team_id != $results[$i]->winning_team_id) {

                echo 'pick winning team id: ' . $picks[$i]->winning_team_id . '<br>';
                echo 'result winning team id: ' . $results[$i]->winning_team_id . '<br>';
                $wrong_picks[] = $picks[$i];
            }
        }

        return $wrong_picks;

    }

    public function send_incorrect_pick_notification($user_id) {
        $play_repo = new Wp_Bracket_Builder_Play_Repo();

        $plays = $play_repo->get_all_by_author($user_id);

        $random_index = rand(0, count($plays) - 1);




        $to_name = get_user_meta($user_id, 'first_name', true);
        $to_email = get_user_meta($user_id, 'email', true);
        $from_email = MAILCHIMP_FROM_EMAIL;
        $subject = 'Back My Bracket Notification';
    }
}

?>