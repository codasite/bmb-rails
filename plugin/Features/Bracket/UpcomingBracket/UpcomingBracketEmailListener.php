<?php

namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailServiceFactory;
use WStrategies\BMB\Features\Notifications\Domain\NotificationSubscription;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\User;

class UpcomingBracketEmailListener implements
  UpcomingNotificationListenerInterface {
  private readonly EmailServiceInterface $email_service;

  public function __construct($args = []) {
    $this->email_service =
      $args['email_service'] ?? (new MailchimpEmailServiceFactory())->create();
  }

  public function notify(
    User $user,
    Bracket $bracket,
    NotificationSubscription $notification
  ): void {
    $title = UpcomingBracketMessageFormatter::get_title();
    $message = UpcomingBracketMessageFormatter::get_message($bracket);
    $link = UpcomingBracketMessageFormatter::get_link($bracket);
    $button_text = 'Play Tournament';

    $html = BracketEmailTemplate::render($message, $link, $button_text);

    $this->email_service->send(
      $user->user_email,
      $user->display_name,
      $title,
      $message,
      $html
    );
  }
}
