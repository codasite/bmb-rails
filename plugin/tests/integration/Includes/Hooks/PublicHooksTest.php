<?php
namespace WStrategies\BMB\tests\integration\Includes\Hooks;

use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Hooks\PublicHooks;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class PublicHooksTest extends WPBB_UnitTestCase {
  public function test_role_is_added_when_sub_activated() {
    $user = self::factory()->user->create_and_get();

    // check that the role is added when the subscription is activated
    //standard class mock
    $sub_mock = $this->getMockBuilder(WC_Subscription::class)
      ->setMethods(['get_user_id'])
      ->getMock();

    $sub_mock->method('get_user_id')->willReturn($user->ID);

    $hooks = new PublicHooks();
    $hooks->add_bmb_plus_role($sub_mock);

    $user = get_user_by('id', $user->ID);
    $this->assertTrue(in_array('bmb_plus', $user->roles));
  }

  public function test_role_is_removed_when_sub_canceled() {
    $user = self::factory()->user->create_and_get();
    $user_id = $user->ID;
    $user->set_role('bmb_plus');

    // check that the role is added when the subscription is activated
    //standard class mock
    $sub_mock = $this->getMockBuilder(WC_Subscription::class)
      ->setMethods(['get_user_id'])
      ->getMock();

    $sub_mock->method('get_user_id')->willReturn($user->ID);

    $hooks = new PublicHooks();
    $hooks->remove_bmb_plus_role($sub_mock);

    $user = get_user_by('id', $user_id);

    $this->assertTrue(!in_array('bmb_plus', $user->roles));
  }

  public function test_other_roles_are_not_removed_when_sub_activated() {
    $user = self::factory()->user->create_and_get();
    $user->set_role('subscriber');

    // check that the role is added when the subscription is activated
    //standard class mock
    $sub_mock = $this->getMockBuilder(WC_Subscription::class)
      ->setMethods(['get_user_id'])
      ->getMock();

    $sub_mock->method('get_user_id')->willReturn($user->ID);

    $hooks = new PublicHooks();
    $hooks->add_bmb_plus_role($sub_mock);

    $user = get_user_by('id', $user->ID);
    $this->assertTrue(in_array('bmb_plus', $user->roles));
    $this->assertTrue(in_array('subscriber', $user->roles));
  }
  public function test_other_roles_are_not_removed_when_sub_canceled() {
    $user = self::factory()->user->create_and_get();
    $user->add_role('subscriber');
    $user->add_role('bmb_plus');

    // check that the role is added when the subscription is activated
    //standard class mock
    $sub_mock = $this->getMockBuilder(WC_Subscription::class)
      ->setMethods(['get_user_id'])
      ->getMock();

    $sub_mock->method('get_user_id')->willReturn($user->ID);

    $hooks = new PublicHooks();
    $hooks->remove_bmb_plus_role($sub_mock);

    $user = get_user_by('id', $user->ID);
    $this->assertTrue(!in_array('bmb_plus', $user->roles));
    $this->assertTrue(in_array('subscriber', $user->roles));
  }

  public function test_after_play_printed_sets_printed_true() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_printed' => false,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $hooks = new PublicHooks();
    $hooks->after_play_printed($play);

    $play = $this->get_play($play->id);

    $this->assertTrue($play->is_printed);
  }

  public function test_after_play_printed_does_not_set_is_paid_true_for_paid_bracket() {
    $bracket_utils_mock = $this->createMock(BracketProductUtils::class);
    $bracket_utils_mock->method('has_bracket_fee')->willReturn(true);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_paid' => false,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $hooks = new PublicHooks([
      'bracket_product_utils' => $bracket_utils_mock,
    ]);
    $hooks->after_play_printed($play);

    $play = $this->get_play($play->id);

    $this->assertFalse($play->is_paid);
  }

  public function test_after_play_printed_does_not_sets_is_paid_true_for_free_bracket() {
    $bracket_utils_mock = $this->createMock(BracketProductUtils::class);
    $bracket_utils_mock->method('has_bracket_fee')->willReturn(false);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_paid' => false,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $hooks = new PublicHooks([
      'bracket_product_utils' => $bracket_utils_mock,
    ]);
    $hooks->after_play_printed($play);

    $play = $this->get_play($play->id);

    $this->assertFalse($play->is_paid);
  }

  public function test_after_play_printed_does_not_mark_play_as_tournament_entry_no_fee() {
    $bracket_utils_mock = $this->createMock(BracketProductUtils::class);
    $bracket_utils_mock->method('has_bracket_fee')->willReturn(false);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $hooks = new PublicHooks([
      'bracket_product_utils' => $bracket_utils_mock,
    ]);
    $hooks->after_play_printed($play);

    $play = $this->get_play($play->id);

    $this->assertFalse($play->is_tournament_entry);
  }

  public function test_after_play_printed_does_not_mark_play_as_tournament_entry_with_fee() {
    $bracket_utils_mock = $this->createMock(BracketProductUtils::class);
    $bracket_utils_mock->method('has_bracket_fee')->willReturn(true);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
      'is_paid' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $hooks = new PublicHooks([
      'bracket_product_utils' => $bracket_utils_mock,
    ]);
    $hooks->after_play_printed($play);

    $play = $this->get_play($play->id);

    $this->assertFalse($play->is_tournament_entry);
  }
}
