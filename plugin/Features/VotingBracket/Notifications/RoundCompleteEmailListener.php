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
    if ($bracket->status === 'complete') {
      $subject = $bracket->get_title() . ' Voting Complete!';
      $message = 'The voting for ' . $bracket->get_title() . ' is complete!';
      $button_url =
        $this->permalink_service->get_permalink($bracket->id) . 'results';
      $button_text = 'View Results';
    } else {
      $subject = $bracket->get_title() . ' Voting Round Complete!';
      $message = 'Vote now in round ' . ((int) $bracket->live_round_index + 1);
      $button_url =
        $this->permalink_service->get_permalink($bracket->id) . 'play';
      $button_text = 'Vote now';
    }

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
