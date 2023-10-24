<?php

interface Wpbb_ScoreServiceInterface {
  public function score_bracket_plays(Wpbb_Bracket|int|null $bracket): int;
}
