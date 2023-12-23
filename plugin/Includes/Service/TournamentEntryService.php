<?php

namespace WStrategies\BMB\Includes\Service;

use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;

class TournamentEntryService {
  private BracketPlayRepo $play_repo;

  public function __construct(array $args = []) {
    $this->play_repo = $args['play_repo'] ?? new BracketPlayRepo();
  }

  public function mark_play_as_tournament_entry(BracketPlay $play): void {
    $author_id = $play->author;
    $bracket_id = $play->bracket_id;
    $play_id = $play->id;
    $this->clear_tournament_entries_for_author($bracket_id, $author_id);
    $this->play_repo->update($play_id, [
      'is_tournament_entry' => true,
    ]);
  }

  public function clear_tournament_entries_for_author(
    int $bracket_id,
    int $author_id
  ) {
    global $wpdb;
    $posts_table = $wpdb->posts;
    $plays_table = BracketPlayRepo::table_name();

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
