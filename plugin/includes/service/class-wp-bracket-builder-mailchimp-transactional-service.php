<?php

require_once(plugin_dir_path(dirname(__FILE__, 2)) . 'vendor/autoload.php');
require_once('class-wp-bracket-builder-email-service-interface.php');


class Wp_Bracket_Builder_Mailchimp_Transactional_Service  implements Wp_Bracket_Builder_Email_Service_Interface {
    protected MailchimpTransactional\ApiClient $client;

    public $from_email;

    public function __construct() {
        if (!defined('MAILCHIMP_API_KEY') || !defined('MAILCHIMP_FROM_EMAIL')) {
            throw new Exception('Mailchimp API Key and From Email must be defined');
        }
        $mandrill_api_key = MAILCHIMP_API_KEY;
        $this->from_email = MAILCHIMP_FROM_EMAIL;

        $this->client = new \MailchimpTransactional\ApiClient();
        $this->client->setApiKey($mandrill_api_key);
    }

    public function ping_server() {
        $response = $this->client->users->ping();
        return $response;
    }

    public function send_message($to_email, $to_name, $subject, $message, $html) {
        $response = $this->client->messages->send([
            'message' => [
                'text' => $message,
                'html' => $html,
                'subject' => $subject,
                'from_email' => $this->from_email,
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

    // public function send_template(string $template_name, array $template_content, $message) {
    //     $response = $this->client->messages->sendTemplate([
    //         "template_name" => $template_name,
    //         "template_content" => $template_content,
    //         "message" => $message,
    //     ]);

    //     return $response;
    // }

    // public function create_template(string $name, string $from_email, string $from_name, string $subject, string $code, string $text, bool $publish, array $labels) {
    //     $response = $this->client->templates->add([
    //         'name' => $name,
    //         'from_email' => $from_email,
    //         'from_name' => $from_name,
    //         'subject' => $subject,
    //         'code' => $code,
    //         'text' => $text,
    //         'publish' => $publish,
    //         'labels' => $labels,
    //     ]);

    //     return $response;
    // }

    // public function get_or_create_template(string $name, string $from_email, string $from_name, string $subject, string $code, string $text, bool $publish, array $labels) {
    //     $response = $this->client->templates->info([
    //         'name' => $name,
    //     ]);

    //     if ($response->status == 'error' && $response->slug != $template_name) {
    //         $response = $this->create_template($name, $from_email, $from_name, $subject, $code, $text, $publish, $labels);
    //     }

    //     return $response;
    // }

    // public function list_templates() {
    //     $response = $this->client->templates->list();

    //     return $response;
    // }

    // public function delete_template(string $name) {
    //     $response = $this->client->templates->delete([
    //         'name' => $name,
    //     ]);

    //     return $response;
    // }

}
