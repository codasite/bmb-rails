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

  public function test_anonymous_bracket_is_linked_to_logged_in_user() {
    $user = self::factory()->user->create_and_get();
    $bracket = self::factory()->bracket->create_and_get([
      'author' => 0,
      'num_teams' => 4,
    ]);

    $utils_mock = $this->createMock(Wpbb_Utils::class);
    // $utils_mock
    //   ->expects($this->once())
    //   ->method('pop_cookie')
    //   ->with($this->equalTo('bracket_id'))
    //   ->willReturn($bracket->id);

    $utils_mock
      ->expects($this->at(0))
      ->method('pop_cookie')
      ->with($this->equalTo('bracket_id'))
      ->willReturn($bracket->id);

    $utils_mock
      ->expects($this->at(1))
      ->method('pop_cookie')
      ->with($this->equalTo('anonymous_bracket_nonce'));

    $hooks = new Wpbb_PublicHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_bracket_to_user_on_login($user->user_login, $user);

    $bracket = self::factory()->bracket->get_object_by_id($bracket->id);

    $this->assertEquals($user->ID, $bracket->author);
  }

  public function test_anonymous_bracket_is_linked_to_registered_user() {
    $user = self::factory()->user->create_and_get();
    $bracket = self::factory()->bracket->create_and_get([
      'author' => 0,
      'num_teams' => 4,
    ]);

    $utils_mock = $this->createMock(Wpbb_Utils::class);
    // $utils_mock
    //   ->expects($this->once())
    //   ->method('pop_cookie')
    //   ->with($this->equalTo('bracket_id'))
    //   ->willReturn($bracket->id);
  
    $utils_mock
      ->expects($this->at(0))
      ->method('pop_cookie')
      ->with($this->equalTo('bracket_id'))
      ->willReturn($bracket->id);

    $utils_mock
      ->expects($this->at(1))
      ->method('pop_cookie')
      ->with($this->equalTo('anonymous_bracket_nonce'));   

    $hooks = new Wpbb_PublicHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_bracket_to_user_on_register($user->ID);

    $bracket = self::factory()->bracket->get_object_by_id($bracket->id);

    $this->assertEquals($user->ID, $bracket->author);
  }
}
