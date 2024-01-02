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

  // public function test_anonymous_printed_play_is_linked_to_user() {

  // }

  public function test_anonymous_bracket_is_linked_to_user_on_login() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket([
      'author' => 0,
      'num_teams' => 4,
    ]);
    update_post_meta($bracket->id, 'wpbb_anonymous_bracket_key', 'test_key');

    $utils_mock = $this->createMock(Utils::class);
    $utils_mock
      ->expects($this->exactly(2))
      ->method('pop_cookie')
      ->withConsecutive(
        [$this->equalTo('wpbb_anonymous_bracket_id')],
        [$this->equalTo('wpbb_anonymous_bracket_key')]
      )
      ->willReturnOnConsecutiveCalls($bracket->id, 'test_key');

    $hooks = new PublicHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_bracket_to_user_on_login('test_login', $user);

    $bracket = self::factory()->bracket->get_object_by_id($bracket->id);

    $this->assertEquals($user->ID, $bracket->author);
  }

  public function test_anonymous_bracket_is_linked_to_user_on_register() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket([
      'author' => 0,
      'num_teams' => 4,
    ]);
    update_post_meta($bracket->id, 'wpbb_anonymous_bracket_key', 'test_key');

    $utils_mock = $this->createMock(Utils::class);
    $utils_mock
      ->expects($this->exactly(2))
      ->method('pop_cookie')
      ->withConsecutive(
        [$this->equalTo('wpbb_anonymous_bracket_id')],
        [$this->equalTo('wpbb_anonymous_bracket_key')]
      )
      ->willReturnOnConsecutiveCalls($bracket->id, 'test_key');

    $hooks = new PublicHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_bracket_to_user_on_register($user->ID);

    $bracket = self::factory()->bracket->get_object_by_id($bracket->id);

    $this->assertEquals($user->ID, $bracket->author);
  }

  public function test_anonymous_play_is_linked_to_user_on_login() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket([
      'author' => 0,
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => 0,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);
    update_post_meta($play->id, 'wpbb_anonymous_play_key', 'test_key');

    $utils_mock = $this->createMock(Utils::class);
    $utils_mock
      ->expects($this->exactly(2))
      ->method('pop_cookie')
      ->withConsecutive(
        [$this->equalTo('play_id')],
        [$this->equalTo('wpbb_anonymous_play_key')]
      )
      ->willReturnOnConsecutiveCalls($play->id, 'test_key');

    $hooks = new PublicHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_play_to_user_on_login('test_login', $user);

    $play = $this->get_play($play->id);

    $this->assertEquals($user->ID, $play->author);
  }

  public function test_anonymous_play_is_linked_to_user_on_register() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket([
      'author' => 0,
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => 0,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);
    update_post_meta($play->id, 'wpbb_anonymous_play_key', 'test_key');

    $utils_mock = $this->createMock(Utils::class);
    $utils_mock
      ->expects($this->exactly(2))
      ->method('pop_cookie')
      ->withConsecutive(
        [$this->equalTo('play_id')],
        [$this->equalTo('wpbb_anonymous_play_key')]
      )
      ->willReturnOnConsecutiveCalls($play->id, 'test_key');

    $hooks = new PublicHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_play_to_user_on_register($user->ID);

    $play = $this->get_play($play->id);

    $this->assertEquals($user->ID, $play->author);
  }

  public function test_link_anonymous_post_to_user_from_cookie() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post([
      'post_author' => 0,
    ]);
    update_post_meta($post->ID, 'wpbb_anonymous_key', 'test_key');

    $utils_mock = $this->createMock(Utils::class);
    $utils_mock
      ->expects($this->exactly(2))
      ->method('pop_cookie')
      ->withConsecutive(
        [$this->equalTo('wpbb_anonymous_id')],
        [$this->equalTo('wpbb_anonymous_key')]
      )
      ->willReturnOnConsecutiveCalls($post->ID, 'test_key');

    $hooks = new PublicHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_post_to_user_from_cookie(
      $user->ID,
      'wpbb_anonymous_id',
      'wpbb_anonymous_key'
    );

    $post = self::factory()->post->get_object_by_id($post->ID);

    $this->assertEquals($user->ID, $post->post_author);
  }
  public function test_link_anonymous_post_to_user() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post([
      'post_author' => 0,
    ]);

    $hooks = new PublicHooks();
    $hooks->link_anonymous_post_to_user($post->ID, $user->ID);

    $post = self::factory()->post->get_object_by_id($post->ID);

    $this->assertEquals($user->ID, $post->post_author);
  }

  public function test_post_with_author_is_not_linked() {
    $user = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $post = $this->create_post([
      'post_author' => $user->ID,
    ]);

    $hooks = new PublicHooks();
    $hooks->link_anonymous_post_to_user($post->ID, $user2->ID);

    $post = self::factory()->post->get_object_by_id($post->ID);

    $this->assertEquals($user->ID, $post->post_author);
  }

  public function test_link_post_from_cookie_with_invalid_key() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post([
      'post_author' => 0,
    ]);
    update_post_meta($post->ID, 'wpbb_anonymous_bracket_key', 'test_key');

    $utils_mock = $this->createMock(Utils::class);

    $utils_mock
      ->expects($this->exactly(2))
      ->method('pop_cookie')
      ->withConsecutive(
        [$this->equalTo('wpbb_anonymous_bracket_id')],
        [$this->equalTo('wpbb_anonymous_bracket_key')]
      )
      ->willReturnOnConsecutiveCalls($post->ID, 'invalid_key');

    $hooks = new PublicHooks([
      'utils' => $utils_mock,
    ]);

    $hooks->link_anonymous_post_to_user_from_cookie(
      $user->ID,
      'wpbb_anonymous_bracket_id',
      'wpbb_anonymous_bracket_key'
    );

    $post = get_post($post->ID);

    $this->assertEquals(0, $post->post_author);
  }
}
