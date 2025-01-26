<?php

namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailServiceFactory;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationSubscriptionRepo;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\UserRepo;

class UpcomingBracketNotificationService {
  private NotificationSubscriptionRepo $notification_sub_repo;
  private BracketRepo $bracket_repo;
  private readonly UserRepo $user_repo;

  /**
   * @var array<UpcomingNotificationListenerInterface>
   */
  private array $listeners = [];

  public function __construct($args = []) {
    $this->notification_sub_repo =
      $args['notification_sub_repo'] ?? new NotificationSubscriptionRepo();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->user_repo = $args['user_repo'] ?? new UserRepo();
    $this->listeners = $args['listeners'] ?? $this->init_listeners($args);
  }

  /**
   * @return array<UpcomingNotificationListenerInterface>
   */
  private function init_listeners($args): array {
    return [
      new UpcomingBracketEmailListener($args),
      new UpcomingBracketPushListener($args),
    ];
  }

  public function notify_upcoming_bracket_live(int $bracket_post_id): void {
    $notifications = $this->notification_sub_repo->get([
      'post_id' => $bracket_post_id,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);

    $bracket = $this->bracket_repo->get($bracket_post_id);
    if (!$bracket) {
      return;
    }

    foreach ($notifications as $notification) {
      $user = $this->user_repo->get_by_id($notification->user_id);
      if (!$user) {
        continue;
      }

      foreach ($this->listeners as $listener) {
        $listener->notify($user, $bracket, $notification);
      }
    }
  }
}
