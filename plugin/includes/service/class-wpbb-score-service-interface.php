<?php

interface Wpbb_Score_Service_Interface
{

	public function score_tournament_plays(Wpbb_BracketTournament|int|null $tournament);
}
