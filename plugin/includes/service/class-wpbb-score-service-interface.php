<?php

interface Wpbb_Score_Service_Interface
{

	public function score_tournament_plays(Wp_Bracket_Builder_Bracket_Tournament|int|null $tournament);
}
