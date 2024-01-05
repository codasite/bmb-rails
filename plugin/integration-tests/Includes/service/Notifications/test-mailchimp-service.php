
<?php
use WStrategies\BMB\Includes\Service\Notifications\MailchimpEmailService;

require_once WPBB_PLUGIN_DIR .
  'integration-tests/mock/MailchimpApiClientMock.php';

class MailchimpEmailServiceTest extends WPBB_UnitTestCase {
  public function test_client_send_is_called() {
    $messagesMock = $this->getMockBuilder(stdClass::class) // Use stdClass just as a generic object.
      ->addMethods(['send']) // Mock the 'send' method.
      ->getMock();

    $messagesMock
      ->expects($this->once())
      ->method('send')
      ->willReturn([
        'status' => 'sent',
        'id' => '123',
      ]);

    $client = $this->createMock(MailchimpApiClientMock::class);

    $client->messages = $messagesMock;

    $mailchimp = new MailchimpEmailService([
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

