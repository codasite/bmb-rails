<?php

namespace WStrategies\BMB\Includes\Service\Notifications;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\UserRepo;
use WStrategies\BMB\Includes\Service\WordpressFunctions\PermalinkService;

class BracketResultsEmailFormatService {
  public function __construct(
    private readonly EmailServiceInterface $email_service = new MailchimpEmailService(),
    private readonly UserRepo $user_repo = new UserRepo(),
    private readonly PermalinkService $permalink_service = new PermalinkService()
  ) {
  }

  public function send_email(Play $play, PickResult $result): void {
    $user = $this->user_repo->get_by_id($play->author);
    $to_email = $user->user_email;
    $to_name = $user->display_name;
    $subject = 'Bracket Results Updated';

    // Generate html content for email
    $heading = $this->get_pick_result_heading($result);
    $button_url = $this->permalink_service->get_permalink($play->id) . 'view';
    $button_text = 'View Bracket';

    $html = BracketEmailTemplate::render($heading, $button_url, $button_text);

    // send the email
    $response = $this->email_service->send(
      $to_email,
      $to_name,
      $subject,
      $heading,
      $html
    );
  }

  public function get_pick_result_heading(PickResult $result): string {
    $picked_team = strtoupper($result->picked_team->name);
    $winning_team = strtoupper($result->winning_team->name);
    if ($result->correct_picked()) {
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
