<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-bust.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-play-repo.php';

class Wp_Bracket_Builder_Bracket_Bust_Repository {
    private $wpdb;

    private Wp_Bracket_Builder_Bracket_Play_Repository $play_repo;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();
    }

    public function get_all() {
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

    public function get($bust_id) {
        $query = "SELECT * FROM {$this->wpdb->prefix}bracket_builder_bracket_bust WHERE id = %d";
        $prepared_query = $this->wpdb->prepare($query, $bust_id);
        $results = $this->wpdb->get_results($prepared_query, ARRAY_A);

        if (count($results) === 0) {
            return null;
        }

        $busted_play = $this->play_repo->get($results[0]['busted_play_id']);
        $buster_play = $this->play_repo->get($results[0]['buster_play_id']);

        $bust = new Wp_Bracket_Builder_Bracket_Bust(
            $results[0]['id'],
            $busted_play,
            $buster_play,
        );
        return $bust;
    }

    public function add(int $busted_id, int $buster_id) {
        $query = "INSERT INTO {$this->wpdb->prefix}bracket_builder_busts (busted_play_id, buster_play_id) VALUES (%d, %d)";
        $prepared_query = $this->wpdb->prepare($query, $busted_id, $buster_id);
        $this->wpdb->query($prepared_query);

        $id = $this->wpdb->insert_id;
        return $this->get($id);
    }
}