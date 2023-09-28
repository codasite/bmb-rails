<?php

require_once(plugin_dir_path(dirname(__FILE__, 2)) . 'vendor/autoload.php');

class Wp_Bracket_Builder_Mailchimp_Transactional_Service {
    protected MailchimpTransactional\ApiClient $client;

    public function __construct() {
        // $api_key = 'c178900b763625797a00371a7de439d9-us13';
        // $mandrill_api_key = 'md-p0ty53RPtFBaLQ-K1fhGcg';
        $mandrill_api_key = 'md-CcmrsE3fBHZLKcaKRyyIpA';

        $this->client = new \MailchimpTransactional\ApiClient();
        $this->client->setApiKey($mandrill_api_key);
    }

    public function ping_server() {
        $response = $this->client->users->ping();
        return $response;
    }

    public function send_message($from_email, $recipient_email, $recipient_name, $subject, $message) {
        $response = $this->client->messages->send([
            'message' => [
                'text' => $message,
                'subject' => $subject,
                'from_email' => $from_email,
                'to' => [
                    [
                        'email' => $recipient_email,
                        'name' => $recipient_name,
                    ]
                ],
                'preserve_recipients' => false,
            ],
        ]);

        return $response;
    }
}