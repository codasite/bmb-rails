<?php

use PHPUnit\Util\Log\TeamCity;

require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-play-repo.php';

class Wp_Bracket_Builder_Bracket_Bust {
    public $id;

    public Wp_Bracket_Builder_Bracket_Play $busted_play;
    public Wp_Bracket_Builder_Bracket_Play $buster_play;
    private Wp_Bracket_Builder_Bracket_Play_Repository $play_repo;

    public function __construct(
        int $id,
        int $busted_play_id,
        int $buster_play_id,
    ) {
        $this->id = $id;
        $this->play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();
        $this->busted_play = $this->play_repo->get($busted_play_id);
        $this->buster_play = $this->play_repo->get($buster_play_id);
    }
}