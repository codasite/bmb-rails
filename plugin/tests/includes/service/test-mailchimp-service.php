
<?php

require_once WPBB_PLUGIN_DIR . 'includes/service/class-wp-bracket-builder-mailchimp-email-service.php';
require_once WPBB_PLUGIN_DIR . 'vendor/autoload.php';

class MailchimpEmailServiceTest extends WPBB_UnitTestCase {
	public function test_client_send_is_called() {
		$client = $this->createMock(MailchimpTransactional\ApiClient::class);

		$mailchimp = new Wp_Bracket_Builder_Mailchimp_Email_Service([
			'api_client' => $client,
			'api_key' => '123',
			'from_email' => 'test@test.com'
		]);

		// $client->expects($this->once())
		// 	->method('messages')
		// 	->willReturn($client);

		$client->messages->expects($this->once())
			->method('send')
			->willReturn([
				'status' => 'sent',
				'id' => '123',
			]);

		$mailchimp->send('test2@test.com', 'Test', 'Test Subject', 'Test Message', '<p>Test Message</p>');
	}
}
