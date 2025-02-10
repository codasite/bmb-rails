<?php

namespace WStrategies\BMB\Features\VotingBracket\Notifications;

use WStrategies\BMB\Features\Bracket\BracketMetaConstants;
use WStrategies\BMB\Features\Notifications\Application\NotificationDispatcher;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Repository\UserRepo;
use WStrategies\BMB\Includes\Utils;
use Exception;

class SendRoundCompleteNotificationsService {
  private readonly BracketRepo $bracket_repo;
  private readonly PlayRepo $play_repo;
  private readonly UserRepo $user_repo;
  private readonly NotificationDispatcher $dispatcher;
  private readonly Utils $utils;

  function __construct($args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->user_repo = $args['user_repo'] ?? new UserRepo();
    $this->dispatcher = $args['dispatcher'] ?? new NotificationDispatcher();
    $this->utils = new Utils();
  }

  public function send_round_complete_notifications(): void {
    $brackets = $this->bracket_repo->get_all(
      [
        'meta_query' => [
          [
            'key' => BracketMetaConstants::SHOULD_NOTIFY_ROUND_COMPLETE,
            'value' => 1,
          ],
        ],
      ],
      ['fetch_matches' => false, 'fetch_results' => false]
    );
    foreach ($brackets as $bracket) {
      $this->send_notifications_for_bracket($bracket);
    }
  }

  private function send_notifications_for_bracket(Bracket $bracket): void {
    $plays = $this->play_repo->get_all(
      ['bracket_id' => $bracket->id, 'author__not_in' => [0]],
      ['fetch_bracket' => false]
    );
    foreach ($plays as $play) {
      $this->send_notifications_for_play($bracket, $play);
    }
    update_post_meta(
      $bracket->id,
      BracketMetaConstants::SHOULD_NOTIFY_ROUND_COMPLETE,
      0
    );
  }

  private function send_notifications_for_play(
    Bracket $bracket,
    Play $play
  ): void {
    $user = $this->user_repo->get_by_id($play->author);
    if (!$user) {
      return;
    }

    try {
      $notification = new Notification([
        'user_id' => $user->id,
        'title' => RoundCompleteMessageFormatter::get_title($bracket),
        'message' => RoundCompleteMessageFormatter::get_message($bracket),
        'link' => RoundCompleteMessageFormatter::get_link($bracket),
        'notification_type' => NotificationType::ROUND_COMPLETE,
      ]);

      $this->dispatcher->dispatch($notification);
    } catch (Exception $e) {
      $this->utils->log_error(
        'Error sending round complete notification: ' .
          $e->getMessage() .
          "\nStack trace:\n" .
          $e->getTraceAsString()
      );
    }
  }
}
