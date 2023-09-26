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

    public function get_first_list() {
        $response = $this->client->lists->getAllLists([
            'fields' => 'lists.id'
        ])->lists;
        return $response[0];
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
                'LNAME' => $last_name,
                'ARBITRARY_FIELD' => 'Test'
            ]
        ]);
        return $response;
    }

    public function delete_list($list_id) {
        $response = $this->client->lists->deleteList($list_id);
        return $response;
    }

    public function create_list_segment($list_id, $segment_name, $emails, $tag_name, $tag_value) {
        $response = $this->client->lists->createSegment($list_id, [
            'name' => $segment_name,
            'static_segment' => $emails,
            // 'conditions' => [
            //     [
            //         'condition_type' => 'StaticSegment',
            //         'field' => $tag_name,
            //         'op' => 'eq',
            //         'value' => $tag_value
            //     ],
            // ],
        ]);
        return $response;
    }

    public function delete_list_segment($list_id, $segment_id) {
        $response = $this->client->lists->deleteSegment($list_id, $segment_id);
        return $response;
    }

    public function delete_all_list_segments($list_id) {
        $segment_ids = $this->client->lists->listSegments($list_id, [
            'fields' => 'segments.id'
        ])->segments;

        foreach ($segment_ids as $segment_id) {
            $this->client->lists->deleteSegment($list_id, $segment_id->id);
        }
    }

    public function add_list_segment_member($list_id, $segment_id, $email_address) {
        $response = $this->client->lists->createSegmentMember($list_id, $segment_id, [
            'email_address' => $email_address
        ]);
        return $response;
    }

    public function add_list_segment_members($list_id, $segment_it, $email_addresses) {
        $response = $this->client->lists->createSegmentMembers($list_id, $segment_id, [
            'members_to_add' => $email_addresses
        ]);
        return $response;
    }

    /**
     * Delete all lists from the mailchimp account. This is important
     * because the account is limited to only 5 lists, and a list is
     * required to send a campaign.
     * 
     * The list should be deleted after the campaign is sent, but
     * this can be used as a failsafe.
     * 
     * The mailchimp server returns 403 when the limit is reached.
     */
    public function delete_all_lists() {
        $list_ids = $this->client->lists->getAllLists([
            'fields' => 'lists.id'
        ])->lists;

        foreach ($list_ids as $list_id) {
            $this->client->lists->deleteList($list_id->id);
        }
    }

    public function create_campaign($list_id, $segment_id, $subject_line, $from_name, $reply_to) {// $campaign = $mailchimp->campaigns->create([
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

        $response = $this->client->campaigns->create([
            'type' => 'regular',
            'recipients' => [
                'list_id' => $list_id,
                'segment_opts' => [
                    'saved_segment_id' => $segment_id
                ]
            ],
            'settings' => [
                'subject_line' => 'Test subject',
                'from_name' => 'Test Company',
                'reply_to' => 'amchi81@gmail.com',
            ]
        ]);
        return $response;
    }

    public function set_campaign_content($campaign_id, $html) {
        $response = $this->client->campaigns->setContent($campaign_id, [
            'html' => $html
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