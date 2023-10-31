<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'public/class-wpbb-public-hooks.php';

class PublicHooksTest extends WPBB_UnitTestCase {
  public function test_role_is_added_when_sub_activated() {
    $user = self::factory()->user->create_and_get();

    // check that the role is added when the subscription is activated
    //standard class mock
    $sub_mock = $this->getMockBuilder('WC_Subscription')
      ->setMethods(['get_user_id'])
      ->getMock();

    $sub_mock->method('get_user_id')->willReturn($user->ID);

    $hooks = new Wpbb_PublicHooks();
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
    $sub_mock = $this->getMockBuilder('WC_Subscription')
      ->setMethods(['get_user_id'])
      ->getMock();

    $sub_mock->method('get_user_id')->willReturn($user->ID);

    $hooks = new Wpbb_PublicHooks();
    $hooks->remove_bmb_plus_role($sub_mock);

    $user = get_user_by('id', $user_id);

    $this->assertTrue(!in_array('bmb_plus', $user->roles));
  }

  public function test_other_roles_are_not_removed_when_sub_activated() {
    $user = self::factory()->user->create_and_get();
    $user->set_role('subscriber');

    // check that the role is added when the subscription is activated
    //standard class mock
    $sub_mock = $this->getMockBuilder('WC_Subscription')
      ->setMethods(['get_user_id'])
      ->getMock();

    $sub_mock->method('get_user_id')->willReturn($user->ID);

    $hooks = new Wpbb_PublicHooks();
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
    $sub_mock = $this->getMockBuilder('WC_Subscription')
      ->setMethods(['get_user_id'])
      ->getMock();

    $sub_mock->method('get_user_id')->willReturn($user->ID);

    $hooks = new Wpbb_PublicHooks();
    $hooks->remove_bmb_plus_role($sub_mock);

    $user = get_user_by('id', $user->ID);
    $this->assertTrue(!in_array('bmb_plus', $user->roles));
    $this->assertTrue(in_array('subscriber', $user->roles));
  }

  public function test_mark_play_printed() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $play = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_printed' => false,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $hooks = new Wpbb_PublicHooks();
    $hooks->mark_play_printed($play);

    $play = self::factory()->play->get_object_by_id($play->id);

    $this->assertTrue($play->is_printed);
  }

  public function test_anonymous_bracket_is_linked_to_user() {
    $user = self::factory()->user->create_and_get();
    $bracket = self::factory()->bracket->create_and_get([
      'author' => 0,
      'num_teams' => 4,
    ]);
    update_post_meta($bracket->id, 'wpbb_anonymous_bracket_key', 'test_key');

    $utils_mock = $this->createMock(Wpbb_Utils::class);
    $utils_mock
      ->expects($this->exactly(2))
      ->method('pop_cookie')
      ->withConsecutive(
        [$this->equalTo('wpbb_anonymous_bracket_id')],
        [$this->equalTo('wpbb_anonymous_bracket_key')]
      )
      ->willReturnOnConsecutiveCalls($bracket->id, 'test_key');

    $hooks = new Wpbb_PublicHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_bracket_to_user($user->ID);

    $bracket = self::factory()->bracket->get_object_by_id($bracket->id);

    $this->assertEquals($user->ID, $bracket->author);
  }

  public function test_anonymous_play_is_linked_to_user() {
    $user = self::factory()->user->create_and_get();
    $play = self::factory()->play->create_and_get([
      'author' => 0,
      'num_teams' => 4,
    ]);
    update_post_meta($play->id, 'wpbb_anonymous_play_key', 'test_key');

    $utils_mock = $this->createMock(Wpbb_Utils::class);
    $utils_mock
      ->expects($this->exactly(2))
      ->method('pop_cookie')
      ->withConsecutive(
        [$this->equalTo('wpbb_anonymous_play_id')],
        [$this->equalTo('wpbb_anonymous_play_key')]
      )
      ->willReturnOnConsecutiveCalls($play->id, 'test_key');

    $hooks = new Wpbb_PublicHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_play_to_user($user->ID);

    $play = self::factory()->play->get_object_by_id($play->id);

    $this->assertEquals($user->ID, $play->author);
  }
}
