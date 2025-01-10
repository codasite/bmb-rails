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
  private readonly UserRepo $user_repo;
  private readonly PermalinkService $permalink_service;

  public function __construct($args = []) {
    $this->email_service =
      $args['email_service'] ?? (new MailchimpEmailServiceFactory())->create();
    $this->user_repo = $args['user_repo'] ?? new UserRepo();
    $this->permalink_service =
      $args['permalink_service'] ?? new PermalinkService();
  }

  public function notify(User $user, Play $play, PickResult $result): void {
    $subject = 'Bracket Results Updated';
    $heading = $this->get_pick_result_heading($result);
    $button_url = $this->permalink_service->get_permalink($play->id) . 'view';
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

  public function get_pick_result_heading(PickResult $result): string {
    $picked_team = strtoupper($result->get_picked_team()->name);
    $winning_team = strtoupper($result->match->get_winning_team()->name);

    if ($result->picked_team_won()) {
      return 'You picked ' . $picked_team . '... and they won!';
    } else {
      return 'You picked ' .
        $picked_team .
        '... but ' .
        $winning_team .
        ' won the round!';
    }
  }
}
