<?php

namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailServiceFactory;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationSubscriptionRepo;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\UserRepo;
use WStrategies\BMB\Includes\Utils;
use Exception;
use WStrategies\BMB\Features\Notifications\Application\NotificationDispatcher;
use WStrategies\BMB\Features\Notifications\Domain\Notification;

class UpcomingBracketNotificationService {
  private NotificationSubscriptionRepo $notification_sub_repo;
  private BracketRepo $bracket_repo;
  private readonly UserRepo $user_repo;
  private readonly NotificationDispatcher $dispatcher;
  /**
   * @var array<UpcomingNotificationListenerInterface>
   */
  private array $listeners = [];

  public function __construct($args = []) {
    $this->notification_sub_repo =
      $args['notification_sub_repo'] ?? new NotificationSubscriptionRepo();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->user_repo = $args['user_repo'] ?? new UserRepo();
    $this->dispatcher = $args['dispatcher'] ?? new NotificationDispatcher();
  }

  public function notify_upcoming_bracket_live(int $bracket_post_id): void {
    $notification_subscriptions = $this->notification_sub_repo->get([
      'post_id' => $bracket_post_id,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);

    $bracket = $this->bracket_repo->get($bracket_post_id);
    if (!$bracket) {
      return;
    }

    foreach ($notification_subscriptions as $notification_subscription) {
      $user = $this->user_repo->get_by_id($notification_subscription->user_id);
      if (!$user) {
        continue;
      }

      try {
        $this->dispatcher->dispatch(
          new Notification([
            'user_id' => $user->id,
            'title' => UpcomingBracketMessageFormatter::get_title(),
            'message' => UpcomingBracketMessageFormatter::get_message($bracket),
            'link' => UpcomingBracketMessageFormatter::get_link($bracket),
            'notification_type' => NotificationType::BRACKET_UPCOMING,
          ])
        );
      } catch (Exception $e) {
        (new Utils())->log_error(
          'Error sending upcoming bracket notification: ' .
            $e->getMessage() .
            "\nStack trace:\n" .
            $e->getTraceAsString()
        );
      }
    }
  }
}
