<?php

use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\NotificationType;
use WStrategies\BMB\Includes\Hooks\PublicHooks;
use WStrategies\BMB\Includes\Repository\NotificationRepo;
use WStrategies\BMB\Includes\Utils;

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

  public function test_mark_play_printed() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_printed' => false,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $hooks = new PublicHooks();
    $hooks->mark_play_printed($play);

    $play = $this->get_play($play->id);

    $this->assertTrue($play->is_printed);
  }
}
