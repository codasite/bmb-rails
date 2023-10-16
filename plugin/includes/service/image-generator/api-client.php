<?php

require_once plugin_dir_path(dirname(__FILE__)) .
    'object-storage/class-wpbb-object-storage-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) .
    'object-storage/class-wpbb-s3-storage.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) .
    'domain/class-wpbb-bracket-interface.php';
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

$client = new Client([
        'base_url' => 'http://localhost:3000',
]);

$promises = [
        'image' => $client->getAsync('/image'),
        'html' => $client->getAsync('/html'),
];

