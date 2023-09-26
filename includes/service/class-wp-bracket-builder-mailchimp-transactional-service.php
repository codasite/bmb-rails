<?php

// import mailchimp marketing api
require_once(plugin_dir_path(dirname(__FILE__, 2)) . 'vendor/autoload.php');

$api_key = 'c178900b763625797a00371a7de439d9-us13';

$mailchimp = new MailchimpTransactional\ApiClient();
$mailchimp->setApiKey($api_key);

$response = $mailchimp->users->ping();
print_r($response);