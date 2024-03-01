<?php

namespace WStrategies\BMB\Includes\Service\Notifications;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Repository\PlayRepo;

class BracketResultsEmailFormatService {
  private EmailServiceInterface $email_service;

  public function __construct(EmailServiceInterface $email_service) {
    $this->email_service = $email_service;
  }

  public function send_email(BracketPlay $play, MatchPickResult $result) {
    $user = get_user_by('id', $play->author);
    $to_email = $user->user_email;
    $to_name = $user->display_name;
    $subject = 'Bracket Results Updated';
    $message = [
      'to' => [
        [
          'email' => $to_email,
          'name' => $to_name,
        ],
      ],
    ];

    // Generate html content for email
    $heading = $this->get_pick_result_heading($result);
    $button_url = get_permalink($play->id) . 'view';
    $button_text = 'View Bracket';

    $html = BracketEmailTemplate::render($heading, $button_url, $button_text);

    // send the email
    $response = $this->email_service->send(
      $to_email,
      $to_name,
      $subject,
      $message,
      $html
    );
  }

  public function get_pick_result_heading(MatchPickResult $result): string {
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
