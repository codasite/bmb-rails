<?php
namespace WStrategies\BMB\tests\integration\Features\Notifications\Email;

use MailchimpTransactional\Api\MessagesApi;
use WStrategies\BMB\Features\Notifications\Email\MailchimpApiClient;
use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailClient;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class MailchimpEmailServiceTest extends WPBB_UnitTestCase {
  public function test_client_send_is_called() {
    $messagesMock = $this->getMockBuilder(MessagesApi::class)
      ->disableOriginalConstructor()
      ->getMock();

    $messagesMock
      ->expects($this->once())
      ->method('send')
      ->willReturn([
        'status' => 'sent',
        'id' => '123',
      ]);

    $client = $this->createMock(MailchimpApiClient::class);

    $client->messages = $messagesMock;

    $mailchimp = new MailchimpEmailClient([
      'api_client' => $client,
      'api_key' => '123',
      'from_email' => 'test@test.com',
    ]);

    // $client->expects($this->once())
    // 	->method('messages')
    // 	->willReturn($client);

    $client->messages
      ->expects($this->once())
      ->method('send')
      ->willReturn([
        'status' => 'sent',
        'id' => '123',
      ]);

    $mailchimp->send(
      'test2@test.com',
      'Test',
      'Test Subject',
      'Test Message',
      '<p>Test Message</p>'
    );
  }
}
