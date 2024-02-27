<?php

namespace WStrategies\BMB\Includes\Domain;

interface BracketMatchNodeInterface {
  public function get_round_index(): int;
  public function get_match_index(): int;
}
