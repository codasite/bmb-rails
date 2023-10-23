<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'public/wpbb-public-hooks.php';

class PublicHooksTest extends WPBB_UnitTestCase {
  public function test_role_is_added_when_sub_activated() {
    $user = $this->factory->user->create_and_get();
    $user->set_role('subscriber');
    $this->assertTrue(!$user->has_role('bmb_plus'));

    // check that the role is added when the subscription is activated
  }
}
