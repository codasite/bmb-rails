<?php

namespace WStrategies\BMB\Includes\Hooks;

use Exception;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Service\Logger\SentryLogger;
use WStrategies\BMB\Includes\Service\Notifications\UpcomingBracketNotificationService;

class UpcomingBracketHooks implements HooksInterface {
  private $notification_service;
  public const UPCOMING_NOTIFICATION_SENT_META_KEY = 'bmb_upcoming_notification_sent';
  public function __construct($args = []) {
    try {
      $this->notification_service =
        $args['notification_service'] ??
        new UpcomingBracketNotificationService();
    } catch (Exception $e) {
      error_log(
        'Caught error: ' .
          $e->getMessage() .
          '\nSetting ' .
          __CLASS__ .
          '::$notification_service to null'
      );
      $this->notification_service = null;
    }
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
  ) {
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
        self::UPCOMING_NOTIFICATION_SENT_META_KEY,
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
  ) {
    if ($post->post_type !== Bracket::get_post_type()) {
      return;
    }
    if (
      $this->notification_service !== null &&
      $new_status === 'publish' &&
      $old_status === 'upcoming' &&
      !get_post_meta($post->ID, self::UPCOMING_NOTIFICATION_SENT_META_KEY, true)
    ) {
      $this->notification_service->notify_upcoming_bracket_live($post->ID);
      // add a flag to the post meta to indicate that the notification has been sent
      update_post_meta(
        $post->ID,
        self::UPCOMING_NOTIFICATION_SENT_META_KEY,
        true
      );
    }
  }
}
