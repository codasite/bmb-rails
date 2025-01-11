<?php

namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailServiceFactory;
use WStrategies\BMB\Features\Notifications\Notification;
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
    Notification $notification
  ): void {
    $heading = UpcomingBracketMessageFormatter::get_heading($bracket);
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
