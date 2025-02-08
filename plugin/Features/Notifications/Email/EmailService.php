<?php

namespace WStrategies\BMB\Features\Notifications\Email;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationChannelInterface;
use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Includes\Utils;

class EmailService implements NotificationChannelInterface {
  private readonly EmailClientInterface $client;
  private readonly Utils $utils;

  public function __construct($args = []) {
    $this->client =
      $args['client'] ?? (new MailchimpEmailClientFactory())->create();
    $this->utils = new Utils();
  }

  /**
   * Handles sending an email notification using the email client
   *
   * @param Notification $notification The notification to send
   * @return mixed The result from the email client
   * @throws \Exception if there's an error sending the email
   */
  public function handle_notification(Notification $notification): mixed {
    try {
      $user = get_user_by('id', $notification->user_id);
      if (!$user) {
        throw new \Exception(
          'User not found for notification: ' . $notification->id
        );
      }

      $html = BracketEmailTemplate::render(
        $notification->message,
        $notification->link,
        $notification->action_text ?? 'View Details'
      );

      return $this->client->send(
        $user->user_email,
        $user->display_name,
        $notification->title,
        $notification->message,
        $html
      );
    } catch (\Exception $e) {
      $this->utils->log_error(
        'Error sending email notification: ' .
          $e->getMessage() .
          "\nStack trace:\n" .
          $e->getTraceAsString()
      );
      throw $e;
    }
  }
}
