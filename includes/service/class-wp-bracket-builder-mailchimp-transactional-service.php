<?php

require_once(plugin_dir_path(dirname(__FILE__, 2)) . 'vendor/autoload.php');
require_once('class-wp-bracket-builder-email-service-interface.php');
    

class Wp_Bracket_Builder_Mailchimp_Transactional_Service  implements Wp_Bracket_Builder_Email_Service_Interface{
    protected MailchimpTransactional\ApiClient $client;

    public function __construct($api_key) {
        $mandrill_api_key = $api_key;

        $this->client = new \MailchimpTransactional\ApiClient();
        $this->client->setApiKey($mandrill_api_key);
    }

    public function ping_server() {
        $response = $this->client->users->ping();
        return $response;
    }

    public function send_message($from_email, $to_email, $to_name, $subject, $message) {
        $response = $this->client->messages->send([
            'message' => [
                'text' => $message,
                'subject' => $subject,
                'from_email' => $from_email,
                'to' => [
                    [
                        'email' => $to_email,
                        'name' => $to_name,
                    ]
                ],
                'preserve_recipients' => false,
            ],
        ]);

        return $response;
    }
}