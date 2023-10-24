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
}
