<?php

namespace WStrategies\BMB\Includes\Factory;

use WStrategies\BMB\Includes\Domain\BracketPlay;

class BracketPlayFactory extends PostBaseFactory {
  public function create(array $data): BracketPlay {
    $play = new BracketPlay();
    $play->bracket_id = isset($data['bracket_id'])
      ? (int) $data['bracket_id']
      : null;
    $play->bracket = $data['bracket'] ?? null;
    $play->picks = $data['picks'] ?? [];
    $play->total_score = $data['total_score'] ?? null;
    $play->accuracy_score = $data['accuracy_score'] ?? null;
    $play->busted_id = isset($data['busted_id'])
      ? (int) $data['busted_id']
      : null;
    $play->is_printed = isset($data['is_printed'])
      ? (bool) $data['is_printed']
      : false;
    $play->busted_play = $data['busted_play'] ?? null;
    $play->is_bustable = isset($data['is_bustable'])
      ? (bool) $data['is_bustable']
      : false;
    $play->is_winner = isset($data['is_winner'])
      ? (bool) $data['is_winner']
      : false;
    $play->bmb_official = isset($data['bmb_official'])
      ? (bool) $data['bmb_official']
      : false;
    $play->is_tournament_entry = isset($data['is_tournament_entry'])
      ? (bool) $data['is_tournament_entry']
      : false;
    return $play;
  }
}
