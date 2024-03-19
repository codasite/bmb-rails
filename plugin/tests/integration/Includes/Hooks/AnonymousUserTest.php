<?php
namespace WStrategies\BMB\tests\integration\Includes\Hooks;

use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Hooks\AnonymousUserHooks;
use WStrategies\BMB\Includes\Utils;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class AnonymousUserTest extends WPBB_UnitTestCase {
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

    $hooks = new AnonymousUserHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_bracket_to_user_on_login('test_login', $user);

    $bracket = $this->get_bracket($bracket->id);

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

    $hooks = new AnonymousUserHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_bracket_to_user_on_register($user->ID);

    $bracket = $this->get_bracket($bracket->id);

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
        new Pick([
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

    $hooks = new AnonymousUserHooks([
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
        new Pick([
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

    $hooks = new AnonymousUserHooks([
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

    $hooks = new AnonymousUserHooks([
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

    $hooks = new AnonymousUserHooks();
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

    $hooks = new AnonymousUserHooks();
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

    $hooks = new AnonymousUserHooks([
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

  public function test_should_mark_anonymous_play_as_tournament_entry_on_register() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket([
      'author' => 0,
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => 0,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);
    update_post_meta($play->id, 'wpbb_anonymous_play_key', 'test_key');

    $utils_mock = $this->createStub(Utils::class);
    $utils_mock
      ->method('pop_cookie')
      ->willReturnOnConsecutiveCalls($play->id, 'test_key');

    $hooks = new AnonymousUserHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_play_to_user_on_register($user->ID);

    $play = $this->get_play($play->id);

    $this->assertTrue($play->is_tournament_entry);
  }

  public function test_should_mark_anonymous_play_as_tournament_entry_on_login() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket([
      'author' => 0,
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => 0,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);
    update_post_meta($play->id, 'wpbb_anonymous_play_key', 'test_key');

    $utils_mock = $this->createStub(Utils::class);
    $utils_mock
      ->method('pop_cookie')
      ->willReturnOnConsecutiveCalls($play->id, 'test_key');

    $hooks = new AnonymousUserHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->link_anonymous_play_to_user_on_login('test_login', $user);

    $play = $this->get_play($play->id);

    $this->assertTrue($play->is_tournament_entry);
  }
}
