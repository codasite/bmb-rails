<?php

// import mailchimp marketing api
require_once(plugin_dir_path(dirname(__FILE__, 2)) . 'vendor/autoload.php');

class Wp_Bracket_Builder_Mailchimp_Marketing_Service {

    protected MailchimpMarketing\ApiClient $client;
    
    // Defaults for audience / list creation
    protected $name = 'Tournament alerts';
    protected $permission_reminder = 'You signed up for updates on our website.';
    protected $email_type_option = false;
    protected $campaign_defaults = [
        'from_name' => 'Back My Bracket',
        'from_email' => 'amchi81@gmail.com',
        'subject' => 'Tournament updated',
        'language' => 'EN_US',
    ];
    protected $contact = [
        'company' => 'Back My Bracket',
        'address1' => '2900 Bedford Ave',
        'city' => 'Brooklyn',
        'state' => 'NY',
        'zip' => '11210',
        'country' => 'US'
    ];

    public function __construct() {
        $api_key = 'c178900b763625797a00371a7de439d9-us13';
        $server = 'us13';

        // Instantiate mailchimp client
        $this->client = new \MailchimpMarketing\ApiClient();
        $this->client->setConfig([
            'apiKey' => $api_key,
            'server' => $server
        ]);
    }

    public function create_list($name=null, $permission_reminder=null, $email_type_option=null, $contact=null, $campaign_defaults=null) {
        $response = $this->client->lists->createList([
            'name' => $name? $name: $this->name,
            'contact' => $contact? $contact: $this->contact,
            'permission_reminder' => $permission_reminder? $permission_reminder: $this->permission_reminder,
            'campaign_defaults' => $campaign_defaults? $campaign_defaults: $this->campaign_defaults,
            'email_type_option' => $email_type_option? $email_type_option: $this->email_type_option
        ]);
        return $response;
    }

    public function add_list_member($list_id, $email_address, $status, $first_name, $last_name) {
        $response = $this->client->lists->addListMember($list_id, [
            'email_address' => $email_address,
            'status' => $status,
            'merge_fields' => [
                'FNAME' => $first_name,
                'LNAME' => $last_name
            ]
        ]);
        return $response;
    }

    public function delete_list($list_id) {
        $response = $this->client->lists->deleteList($list_id);
        return $response;
    }

    public function delete_all_lists() {
        $list_ids = $this->client->lists->getAllLists([
            'fields' => 'lists.id'
        ])->lists;

        foreach ($list_ids as $list_id) {
            $this->client->lists->deleteList($list_id->id);
        }
    }

    public function create_campaign($list_id, $subject_line, $from_name, $reply_to) {
        $response = $this->client->campaigns->create([
            'type' => 'regular',
            'recipients' => [
                'list_id' => $list_id
            ],
            'settings' => [
                'subject_line' => $subject_line,
                'from_name' => $from_name,
                'reply_to' => $reply_to
            ]
        ]);
        return $response;
    }

    public function send_campaign($campaign_id) {
        $response = $this->client->campaigns->send($campaign_id);
        return $response;
    }
}

// $mailchimp = new \MailchimpMarketing\ApiClient();
// $mailchimp->setConfig([
//     'apiKey' => $api_key,
//     'server' => 'us13'
// ]);

// // Ping mailchimp
// // $response = $mailchimp->ping->get();
// // print_r($response);

// // Create a new list
// $list = $mailchimp->lists->createList([
//     'name' => 'Test Audience',
//     'contact' => [
//         'company' => 'Test Company',
//         'address1' => '2900 Bedford Ave',
//         'city' => 'Brooklyn',
//         'state' => 'NY',
//         'zip' => '11210',
//         'country' => 'US'
//     ],
//     'permission_reminder' => 'You signed up for updates on our website.',
//     'campaign_defaults' => [
//         'from_name' => 'Test Company',
//         'from_email' => 'amchi81@gmail.com',
//         'subject' => 'Test Subject',
//         'language' => 'EN_US',
//     ],
//     'email_type_option' => false
// ]);

// // Add a member to the list
// $list_id = $list->id;
// $member = $mailchimp->lists->addListMember($list_id, [
//     // 'email_address' => 'mark.maceachen@gmail.com',
//     'email_address' => 'mark@frontrunnerapp.io',
//     // 'email_address' => 'markmaceachen@live.com',
//     'status' => 'subscribed',
//     'merge_fields' => [
//         'FNAME' => 'Mark',
//         'LNAME' => 'FrontRunner'
//     ]
// ]);

// // Create a new campaign
// $campaign = $mailchimp->campaigns->create([
//     'type' => 'regular',
//     'recipients' => [
//         'list_id' => $list_id
//     ],
//     'settings' => [
//         'subject_line' => 'Test Subject',
//         'from_name' => 'Test Company',
//         'reply_to' => 'amchi81@gmail.com'
//     ]
// ]);

// // Add content to the campaign
// $campaign_id = $campaign->id;
// $mailchimp->campaigns->setContent($campaign_id, [
//     'html' => '<h1>Test Content</h1>'
// ]);

// // Send the campaign
// $campaign_id = $campaign->id;
// $response = $mailchimp->campaigns->send($campaign_id);

// // Delete the list
// $mailchimp->lists->deleteList($list_id);


// // // Get the id of each list
// // $list_ids = $mailchimp->lists->getAllLists([
// //     'fields' => 'lists.id'
// // ])->lists;

// // // Delete each list
// // foreach ($list_ids as $list_id) {
// //     $mailchimp->lists->deleteList($list_id->id);
// // }