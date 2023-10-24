<?php

interface Wpbb_Score_Service_Interface {
  public function score_bracket_plays(Wpbb_Bracket|int|null $bracket, bool $only_score_printed_plays = true): int;
}
