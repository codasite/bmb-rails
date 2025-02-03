<?php

namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Features\Bracket\BracketMetaConstants;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationSubscriptionRepo;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Factory\NotificationSubscriptionFactory;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Features\Bracket\UpcomingBracket\UpcomingBracketNotificationService;
use WStrategies\BMB\Includes\Utils;

class UpcomingBracketHooks implements HooksInterface {
  private $utils;
  private UpcomingBracketNotificationService $notification_service;
  private NotificationSubscriptionRepo $notification_sub_repo;
  private $bracket_repo;

  public function __construct($args = []) {
    $this->notification_sub_repo =
      $args['notification_sub_repo'] ?? new NotificationSubscriptionRepo();
    $this->utils = $args['utils'] ?? new Utils();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->notification_service =
      $args['notification_service'] ?? new UpcomingBracketNotificationService();
  }

  public function load(Loader $loader): void {
    $loader->add_action(
      'set_object_terms',
      [$this, 'update_upcoming_status'],
      10,
      6
    );
    $loader->add_action(
      'transition_post_status',
      [$this, 'transition_from_upcoming_status'],
      10,
      3
    );
    $loader->add_action(
      'wp_login',
      [$this, 'create_upcoming_bracket_notification_on_login'],
      10,
      2
    );
    $loader->add_action(
      'user_register',
      [$this, 'create_upcoming_bracket_notification_on_register'],
      10,
      1
    );
    $loader->add_filter(
      'wp_insert_post_data',
      [$this, 'dont_set_status_to_published'],
      10,
      2
    );
  }
  /**
   * This function is a workaround for custom post status not being added to the admin panel
   * It runs on the `set_object_terms` hook and updates the post status based on tag value
   */
  public function update_upcoming_status(
    $object_id,
    $terms,
    $tt_ids,
    $taxonomy,
    $append,
    $old_tt_ids
  ): void {
    $upcoming_term_id = ($upcoming_term = get_term_by(
      'slug',
      'bmb_upcoming',
      'post_tag'
    ))
      ? $upcoming_term->term_id
      : null;

    $post = get_post($object_id);
    if (!$post || $post->post_type !== Bracket::get_post_type()) {
      return;
    }
    $should_update = false;
    // check if post has the tag "bmb_upcoming"
    if (
      //check if the tag is in the terms array
      $upcoming_term_id &&
      in_array($upcoming_term_id, $tt_ids) &&
      $post->post_status !== 'upcoming'
    ) {
      // update post status to "upcoming"
      $post->post_status = 'upcoming';
      // update post meta to indicate that the notification has not been sent
      update_post_meta(
        $post->ID,
        BracketMetaConstants::UPCOMING_NOTIFICATION_SENT,
        false
      );
      $should_update = true;
    } elseif (
      $post->post_status === 'upcoming' &&
      (!$upcoming_term_id || !in_array($upcoming_term_id, $tt_ids))
    ) {
      // update post status to "publish"
      $post->post_status = 'publish';
      $should_update = true;
    }
    if ($should_update) {
      wp_update_post($post);
    }
  }

  // hooks into the transition_post_status action
  public function transition_from_upcoming_status(
    $new_status,
    $old_status,
    $post
  ): void {
    if ($post->post_type !== Bracket::get_post_type()) {
      return;
    }
    if (
      $this->notification_service !== null &&
      $new_status === 'publish' &&
      $old_status === 'upcoming' &&
      !get_post_meta(
        $post->ID,
        BracketMetaConstants::UPCOMING_NOTIFICATION_SENT,
        true
      )
    ) {
      $this->notification_service->notify_upcoming_bracket_live($post->ID);
      // add a flag to the post meta to indicate that the notification has been sent
      update_post_meta(
        $post->ID,
        BracketMetaConstants::UPCOMING_NOTIFICATION_SENT,
        true
      );
    }
  }

  public function create_upcoming_bracket_notification_on_login(
    $user_login,
    \WP_User $user
  ): void {
    $this->create_upcoming_bracket_notification($user->ID);
  }

  public function create_upcoming_bracket_notification_on_register(
    $user_id
  ): void {
    $this->create_upcoming_bracket_notification($user_id);
  }

  public function create_upcoming_bracket_notification($user_id): void {
    $upcoming_bracket_id = $this->utils->pop_cookie('wpbb_upcoming_bracket_id');

    if (!$upcoming_bracket_id) {
      return;
    }

    $bracket = $this->bracket_repo->get($upcoming_bracket_id);

    if (!$bracket) {
      return;
    }

    $this->notification_sub_repo->add(
      NotificationSubscriptionFactory::create([
        'user_id' => $user_id,
        'post_id' => $upcoming_bracket_id,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
  }

  /**
   * Stop status from being set to published when you save the post with the upcoming post status.
   */
  public function dont_set_status_to_published($data, $postarr) {
    if (has_tag('bmb_upcoming', $postarr['ID'])) {
      $data['post_status'] = 'upcoming';
    }
    return $data;
  }
}
