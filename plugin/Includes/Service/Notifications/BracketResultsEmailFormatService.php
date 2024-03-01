<?php

namespace WStrategies\BMB\Includes\Service\Notifications;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Repository\PlayRepo;

class BracketResultsEmailFormatService {
  private PlayRepo $play_repo;
  private EmailServiceInterface $email_service;

  public function __construct(
    PlayRepo $play_repo,
    EmailServiceInterface $email_service
  ) {
    $this->play_repo = $play_repo;
    $this->email_service = $email_service;
  }

  public function send_email($user_pick, $winning_pick, BracketPlay $play) {
    // TODO fix this function
    $user = get_user_by('id', $user_pick['user_id']);
    $pick = $this->play_repo->pick_repo->get_pick($user_pick['pick_id']);
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
    $heading = $this->get_pick_result_heading($pick, $winning_pick);
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
}
