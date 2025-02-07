<?php

namespace WStrategies\BMB\Features\Bracket\BracketResults;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailServiceFactory;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\UserRepo;
use WStrategies\BMB\Includes\Service\WordpressFunctions\PermalinkService;
use WStrategies\BMB\Includes\Domain\User;

class BracketResultsEmailListener implements
  BracketResultsNotificationListenerInterface {
  private readonly EmailServiceInterface $email_service;
  private readonly PermalinkService $permalink_service;

  public function __construct($args = []) {
    $this->email_service =
      $args['email_service'] ?? (new MailchimpEmailServiceFactory())->create();
    $this->permalink_service =
      $args['permalink_service'] ?? new PermalinkService();
  }

  public function notify(User $user, Play $play, PickResult $result): void {
    $subject = BracketResultsMessageFormatter::get_title();
    $heading = BracketResultsMessageFormatter::get_message($result);
    $button_url = BracketResultsMessageFormatter::get_link($play);
    $button_text = 'View Bracket';

    $html = BracketEmailTemplate::render($heading, $button_url, $button_text);

    $this->email_service->send(
      $user->user_email,
      $user->display_name,
      $subject,
      $heading,
      $html
    );
  }
}
