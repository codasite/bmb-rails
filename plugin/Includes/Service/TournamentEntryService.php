<?php

namespace WStrategies\BMB\Includes\Service;

use WStrategies\BMB\Includes\Controllers\ApiListeners\BracketPlayCreateListenerBase;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class TournamentEntryService extends BracketPlayCreateListenerBase {
  private PlayRepo $play_repo;
  private BracketProductUtils $bracket_product_utils;

  public function __construct(array $args = []) {
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->bracket_product_utils =
      $args['bracket_product_utils'] ?? new BracketProductUtils();
  }

  public function filter_after_play_added(BracketPlay $play): BracketPlay {
    $this->try_mark_play_as_tournament_entry($play);
    return $play;
  }

  public function try_mark_play_as_tournament_entry(BracketPlay $play): void {
    if (!$this->should_mark_play_as_tournament_entry($play)) {
      return;
    }
    $this->mark_play_as_tournament_entry($play);
  }

  public function should_mark_play_as_tournament_entry(
    BracketPlay $play
  ): bool {
    if ($play->is_tournament_entry) {
      return false;
    }
    if ($play->busted_id) {
      return false;
    }
    if (!$play->bracket->is_open()) {
      return false;
    }
    if ($play->bracket->fee > 0 && !$play->is_paid) {
      return false;
    }
    return true;
  }

  public function mark_play_as_tournament_entry(BracketPlay $play): void {
    $author_id = $play->author;
    $bracket_id = $play->bracket_id;
    $play_id = $play->id;
    if ($this->should_clear_tournament_entries($bracket_id)) {
      $this->clear_tournament_entries_for_author($bracket_id, $author_id);
    }
    $this->play_repo->update($play_id, [
      'is_tournament_entry' => true,
    ]);
  }

  public function should_clear_tournament_entries(int $bracket_id): bool {
    return !$this->bracket_product_utils->has_bracket_fee($bracket_id);
  }

  public function clear_tournament_entries_for_author(
    int $bracket_id,
    int $author_id
  ): void {
    global $wpdb;
    $posts_table = $wpdb->posts;
    $plays_table = PlayRepo::table_name();

    $query = "
			UPDATE {$plays_table}
			JOIN {$posts_table} ON {$posts_table}.ID = {$plays_table}.post_id
			SET is_tournament_entry = 0
			WHERE {$posts_table}.post_author = %d
			AND bracket_post_id = %d
			AND is_tournament_entry = 1";

    $wpdb->query($wpdb->prepare($query, $author_id, $bracket_id));
  }
}
