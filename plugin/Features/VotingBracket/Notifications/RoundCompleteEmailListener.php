<?php
namespace WStrategies\BMB\Features\VotingBracket\Notifications;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
use WStrategies\BMB\Features\VotingBracket\Notifications\RoundCompleteNotificationListenerInterface;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;
use WStrategies\BMB\Includes\Service\WordpressFunctions\PermalinkService;

class RoundCompleteEmailListener implements
  RoundCompleteNotificationListenerInterface {
  private readonly EmailServiceInterface $email_service;
  private readonly PermalinkService $permalink_service;

  public function __construct($args = []) {
    $this->email_service = $args['email_service'];
    $this->permalink_service =
      $args['permalink_service'] ?? new PermalinkService();
  }

  public function notify(User $user, Bracket $bracket, Play $play): void {
    $subject = RoundCompleteMessageFormatter::get_heading($bracket);
    $message = RoundCompleteMessageFormatter::get_message($bracket);
    $button_url =
      $this->permalink_service->get_permalink($bracket->id) .
      RoundCompleteMessageFormatter::get_button_url_suffix($bracket);
    $button_text = RoundCompleteMessageFormatter::get_button_text($bracket);

    $html = BracketEmailTemplate::render($message, $button_url, $button_text);
    $this->email_service->send(
      $user->user_email,
      $user->display_name,
      $subject,
      $message,
      $html
    );
  }
}
