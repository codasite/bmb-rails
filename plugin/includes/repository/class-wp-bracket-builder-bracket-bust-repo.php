<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-bust.php';

class Wp_Bracket_Builder_Bracket_Bust_Repository {
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function get_many() {
        $query = "SELECT * FROM {$this->wpdb->prefix}bracket_builder_bracket_bust";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $busts = array();

        foreach($results as $result) {
            $bust = new Wp_Bracket_Builder_Bracket_Bust(
                $result['id'],
                $result['busted_play_id'],
                $result['buster_play_id'],
            );
            $busts[] = $bust;
        }
        return $busts;
    }

    public function get_single($bust_id) {
        $query = "SELECT * FROM {$this->wpdb->prefix}bracket_builder_bracket_bust WHERE id = %d";
        $prepared_query = $this->wpdb->prepare($query, $bust_id);
        $results = $this->wpdb->get_results($prepared_query, ARRAY_A);

        if (count($results) === 0) {
            return null;
        }

        $bust = new Wp_Bracket_Builder_Bracket_Bust(
            $results[0]['id'],
            $results[0]['busted_play_id'],
            $results[0]['buster_play_id'],
        );
        return $bust;
    }

    public function get($bust_id = null) {
        if ($bust_id) {
            return $this->get_single($bust_id);
        }
        return $this->get_many();
    }

    public function get_bracket_busts($bracket_id) {
        $sql = "SELECT * FROM {$this->wpdb->prefix}bracket_builder_bracket_bust WHERE bracket_id = %d";
        $sql = $this->wpdb->prepare($sql, $bracket_id);
        $results = $this->wpdb->get_results($sql, ARRAY_A);
        $bracket_busts = array();
        foreach ($results as $result) {
            $bracket_bust = new Wp_Bracket_Builder_Bracket_Bust();
            $bracket_bust->set_id($result['id']);
            $bracket_bust->set_bracket_id($result['bracket_id']);
            $bracket_bust->set_team_id($result['team_id']);
            $bracket_bust->set_round($result['round']);
            $bracket_bust->set_game($result['game']);
            $bracket_bust->set_user_id($result['user_id']);
            $bracket_busts[] = $bracket_bust;
        }
        return $bracket_busts;
    }

    public function add(int $busted_id, int $buster_id) {
        echo 'adding bust';
        $query = "INSERT INTO {$this->wpdb->prefix}bracket_builder_busts (busted_play_id, buster_play_id) VALUES (%d, %d)";
        $prepared_query = $this->wpdb->prepare($query, $busted_id, $buster_id);
        $this->wpdb->query($prepared_query);

        $id = $this->wpdb->insert_id;
        return $this->get_single($id);
    }
}