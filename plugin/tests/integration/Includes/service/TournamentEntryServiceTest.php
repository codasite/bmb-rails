<?php

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Repository\PlayRepo;
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

  public function test_should_mark_play_as_tournament_entry() {
    $bracket = $this->create_bracket();
    $user = self::factory()->user->create_and_get();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
      'author' => $user->ID,
    ]);

    $service = new TournamentEntryService();
    $should = $service->should_mark_play_as_tournament_entry($play);

    $this->assertTrue($should);
  }
  public function test_buster_plays_should_not_be_marked_as_tournament_entry() {
    $bracket = $this->create_bracket();
    $user = self::factory()->user->create_and_get();
    $busted_play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
      'busted_id' => $busted_play->id,
    ]);

    $service = new TournamentEntryService();
    $should = $service->should_mark_play_as_tournament_entry($play);

    $this->assertFalse($should);
  }
  // test that exisiting entries should not be marked
  public function test_existing_tournament_entries_should_not_be_marked_as_tournament_entry() {
    $bracket = $this->create_bracket();
    $user = self::factory()->user->create_and_get();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
      'author' => $user->ID,
    ]);

    $service = new TournamentEntryService();
    $should = $service->should_mark_play_as_tournament_entry($play);

    $this->assertFalse($should);
  }
  // test that closed brackets should not be marked
  public function test_closed_brackets_should_not_be_marked_as_tournament_entry() {
    $bracket = $this->create_bracket([
      'status' => 'score',
    ]);
    $user = self::factory()->user->create_and_get();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
      'author' => $user->ID,
    ]);

    $service = new TournamentEntryService();
    $should = $service->should_mark_play_as_tournament_entry($play);

    $this->assertFalse($should);
  }
  // test that open brackets should be marked
  public function test_open_brackets_should_be_marked_as_tournament_entry() {
    $bracket = $this->create_bracket([
      'status' => 'publish',
    ]);
    $user = self::factory()->user->create_and_get();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
      'author' => $user->ID,
    ]);

    $service = new TournamentEntryService();
    $should = $service->should_mark_play_as_tournament_entry($play);

    $this->assertTrue($should);
  }

  public function test_try_mark_play_as_tournament_entry_marks_play() {
    $entry_service_mock = $this->getMockBuilder(TournamentEntryService::class)
      ->onlyMethods([
        'should_mark_play_as_tournament_entry',
        'mark_play_as_tournament_entry',
      ])
      ->getMock();

    $entry_service_mock
      ->expects($this->once())
      ->method('should_mark_play_as_tournament_entry')
      ->willReturn(true);

    $entry_service_mock
      ->expects($this->once())
      ->method('mark_play_as_tournament_entry');

    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);

    $entry_service_mock->try_mark_play_as_tournament_entry($play);
  }

  public function test_try_mark_play_as_tournament_entry_does_not_mark_play() {
    $entry_service_mock = $this->getMockBuilder(TournamentEntryService::class)
      ->onlyMethods([
        'should_mark_play_as_tournament_entry',
        'mark_play_as_tournament_entry',
      ])
      ->getMock();

    $entry_service_mock
      ->expects($this->once())
      ->method('should_mark_play_as_tournament_entry')
      ->willReturn(false);

    $entry_service_mock
      ->expects($this->never())
      ->method('mark_play_as_tournament_entry');

    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);

    $entry_service_mock->try_mark_play_as_tournament_entry($play);
  }

  public function test_unpaid_play_for_paid_bracket_is_not_marked() {
    $bracket = $this->create_bracket([
      'fee' => 10.0,
    ]);
    $user = self::factory()->user->create_and_get();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_paid' => false,
      'author' => $user->ID,
    ]);

    $service = new TournamentEntryService();
    $should = $service->should_mark_play_as_tournament_entry($play);

    $this->assertFalse($should);
  }

  public function test_should_not_clear_tournament_entries_for_paid_bracket() {
    $bracket = $this->create_bracket([
      'fee' => 10.0,
    ]);

    $service = new TournamentEntryService();
    $should = $service->should_clear_tournament_entries($bracket->id);

    $this->assertFalse($should);
  }

  public function test_should_clear_tournament_entries_for_free_bracket() {
    $bracket = $this->create_bracket([
      'fee' => 0.0,
    ]);

    $service = new TournamentEntryService();
    $should = $service->should_clear_tournament_entries($bracket->id);

    $this->assertTrue($should);
  }

  public function test_tournament_entries_are_not_cleared() {
    $play_mock = $this->createMock(BracketPlay::class);
    $play_mock->bracket_id = 1;
    $play_mock->author = 2;
    $play_mock->id = 3;
    $play_repo_mock = $this->createMock(PlayRepo::class);
    $play_repo_mock
      ->expects($this->once())
      ->method('update')
      ->with($play_mock->id, ['is_tournament_entry' => true]);
    $entry_service_mock = $this->getMockBuilder(TournamentEntryService::class)
      ->onlyMethods([
        'should_clear_tournament_entries',
        'clear_tournament_entries_for_author',
      ])
      ->setConstructorArgs([
        [
          'play_repo' => $play_repo_mock,
        ],
      ])
      ->getMock();
    $entry_service_mock
      ->expects($this->once())
      ->method('should_clear_tournament_entries')
      ->willReturn(false);
    $entry_service_mock
      ->expects($this->never())
      ->method('clear_tournament_entries_for_author');

    $entry_service_mock->mark_play_as_tournament_entry($play_mock);
  }
}
