<?php

namespace WStrategies\BMB\Includes\Service\Notifications;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailServiceFactory;
use WStrategies\BMB\Features\Notifications\NotificationRepo;
use WStrategies\BMB\Features\Notifications\NotificationType;
use WStrategies\BMB\Includes\Repository\BracketRepo;

class UpcomingBracketNotificationService {
  private NotificationRepo $notification_repo;
  private EmailServiceInterface $email_service;
  private BracketRepo $bracket_repo;

  public function __construct($args = []) {
    $this->notification_repo =
      $args['notification_repo'] ?? new NotificationRepo();
    $this->email_service =
      $args['email_service'] ?? (new MailchimpEmailServiceFactory())->create();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
  }

  public function notify_upcoming_bracket_live(int $bracket_post_id): void {
    $notifications = $this->notification_repo->get([
      'post_id' => $bracket_post_id,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $bracket = $this->bracket_repo->get($bracket_post_id);
    // send email to each user
    foreach ($notifications as $notification) {
      $user = get_user_by('id', $notification->user_id);
      if (!$user) {
        continue;
      }
      $heading = strtoupper($bracket->title) . ' is now live. Make your picks!';
      $button_url = $bracket->url;
      $button_text = 'Play Tournament';

      $html = BracketEmailTemplate::render($heading, $button_url, $button_text);

      $this->email_service->send(
        $user->user_email,
        $user->display_name,
        $heading,
        $heading,
        $html
      );
    }
  }
}
