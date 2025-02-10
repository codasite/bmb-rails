<?php
namespace WStrategies\BMB\Features\Notifications\Email;

use Exception;
use WStrategies\BMB\Features\Notifications\Email\MailchimpApiClient;

class MailchimpEmailClient implements EmailClientInterface {
  protected MailchimpApiClient $client;

  public $from_email;

  public function __construct($args = []) {
    $api_key =
      $args['api_key'] ??
      (defined('MAILCHIMP_API_KEY') ? MAILCHIMP_API_KEY : null);
    $this->from_email =
      $args['from_email'] ??
      (defined('MAILCHIMP_FROM_EMAIL') ? MAILCHIMP_FROM_EMAIL : null);
    if (!$api_key || !$this->from_email) {
      throw new Exception('API Key and From Email must be defined');
    }
    $this->client = $args['api_client'] ?? new MailchimpApiClient();
    $this->client->setApiKey($api_key);
  }

  public function ping_server() {
    $response = $this->client->users->ping();
    return $response;
  }

  public function send($to_email, $to_name, $subject, $message, $html) {
    // TODO: Log exceptions to sentry
    return $this->client->messages->send([
      'message' => [
        'text' => $message,
        'html' => $html,
        'subject' => $subject,
        'from_email' => $this->from_email,
        'from_name' => 'BackMyBracket',
        'to' => [
          [
            'email' => $to_email,
            'name' => $to_name,
          ],
        ],
        'preserve_recipients' => false,
      ],
    ]);
  }
}
