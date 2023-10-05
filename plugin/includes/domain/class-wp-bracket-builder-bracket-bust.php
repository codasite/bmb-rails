<?php

use PHPUnit\Util\Log\TeamCity;

require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-play.php';

class Wp_Bracket_Builder_Bracket_Bust {
    public $id;

    public Wp_Bracket_Builder_Bracket_Play $busted_play;
    public Wp_Bracket_Builder_Bracket_Play $buster_play;
    private Wp_Bracket_Builder_Bracket_Play_Repository $play_repo;

    public function __construct(
        int $id,
        Wp_Bracket_Builder_Bracket_Play $busted_play,
        Wp_Bracket_Builder_Bracket_Play $buster_play,
    ) {
        $this->id = $id;
        $this->play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();
        $this->busted_play = $busted_play;
        $this->buster_play = $buster_play;
    }
}