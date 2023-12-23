<?php

use WStrategies\BMB\Includes\Service\TournamentEntryService;

class TournamentEntryServiceTest extends WPBB_UnitTestCase {
  public function test_clear_tournament_entries_for_author() {
    $bracket = $this->create_bracket();
    $user1 = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
      'author' => $user1->ID,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
      'author' => $user2->ID,
    ]);

    $service = new TournamentEntryService();
    $service->clear_tournament_entries_for_author($bracket->id, $play1->author);

    $play1 = $this->get_play($play1->id);
    $play2 = $this->get_play($play2->id);

    $this->assertFalse($play1->is_tournament_entry);
    $this->assertTrue($play2->is_tournament_entry);
  }

  public function test_mark_play_as_tournament_entry() {
    $bracket = $this->create_bracket();
    $user = self::factory()->user->create_and_get();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
      'author' => $user->ID,
    ]);

    $service = new TournamentEntryService();
    $service->mark_play_as_tournament_entry($play);

    $play = $this->get_play($play->id);

    $this->assertTrue($play->is_tournament_entry);
  }

  public function test_mark_play_as_tournament_entry_sets_other_plays_to_false() {
    $bracket = $this->create_bracket();
    $user = self::factory()->user->create_and_get();
    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
      'author' => $user->ID,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
      'author' => $user->ID,
    ]);

    $service = new TournamentEntryService();
    $service->mark_play_as_tournament_entry($play1);

    $play1 = $this->get_play($play1->id);
    $play2 = $this->get_play($play2->id);

    $this->assertTrue($play1->is_tournament_entry);
    $this->assertFalse($play2->is_tournament_entry);
  }
}
